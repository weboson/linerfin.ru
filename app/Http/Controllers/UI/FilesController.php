<?php

namespace App\Http\Controllers\UI;

use App\Exceptions\AccountAuthorizeException;
use App\Http\Abstraction\AccountAbstract;
use App\Http\Controllers\Utils\ImageMagickUtils;
use App\Models\Attachment;
use App\Models\Document;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class FilesController extends AccountAbstract
{
    protected $middlewareAuthorize = false;

    // Поддерживаемые расширения для обработки изображений
    protected $imageExtensions = ['png', 'jpg', 'jpeg', 'bmp', 'gif', 'webp'];

    public function saveAttachment(Request $request){
        try{
            $authorized = $this->authorize_account();
        }
        catch(AccountAuthorizeException $e){
            $authorized = false;
        }

        if(!$authorized){
            if(!Auth::check())
                return $this->error([], 'Ошибка авторизации');

            $this->user = Auth::user();
            $this->subdomain = null;
        }

        // Get file
        if(!$request->hasFile('attachment'))
            return $this->error($_REQUEST,"Не удалось загрузить файл");

        $attachment = $request->attachment;
        $originalExtension = strtolower($attachment->extension());
        $originalName = $attachment->getClientOriginalName();

        // Check extension
        $allowedExtensions = array_merge(
            $this->imageExtensions,
            ['doc', 'docx', 'xml', 'xls', 'xlsx', 'ppt', 'pptx', 'pdf']
        );
        
        if(false === array_search($originalExtension, $allowedExtensions))
            return $this->error("Файл не поддерживается");

        // Save file
        $path = "attachments" . ($this->subdomain ? "/$this->subdomain" : '/common');
        $path = Storage::putFile($path, $attachment);
        
        if(!$path)
            return $this->error("Не удалось загрузить файл");

        // Удаление фона для изображений
        $shouldRemoveBackground = $request->has('remove-background') && 
                                 $request->get('remove-background') === 'true';
        
        $finalExtension = $originalExtension;
        $backgroundRemoved = false;
        
        if($shouldRemoveBackground && in_array($originalExtension, $this->imageExtensions)){
            try{
                $utils = ImageMagickUtils::image(Storage::path($path));
                
                // Можно получить цвет фона из запроса если нужно
                $backgroundColor = null;
                if($request->has('background_color')){
                    $color = $request->get('background_color');
                    if(preg_match('/^(\d{1,3}),(\d{1,3}),(\d{1,3})$/', $color, $matches)){
                        $backgroundColor = [(int)$matches[1], (int)$matches[2], (int)$matches[3]];
                    }
                }
                
                // Создаем временный путь для результата
                $tempOutputPath = Storage::path($path) . '.processed.png';
                
                // Удаляем фон
                $utils->removeBackground(
                    $tempOutputPath,
                    $backgroundColor
                );
                
                // Проверяем результат
                if (!file_exists($tempOutputPath)) {
                    throw new \Exception('Processed file was not created');
                }
                
                // Определяем новый путь с правильным расширением
                $newPath = preg_replace('/\.[^\.]+$/', '.png', $path);
                
                // Удаляем оригинальный файл
                if (Storage::exists($path)) {
                    Storage::delete($path);
                }
                
                // Копируем обработанный файл на место оригинального
                rename($tempOutputPath, Storage::path($newPath));
                
                // Обновляем путь и расширение
                $path = $newPath;
                $finalExtension = 'png';
                $backgroundRemoved = true;
                
            }
            catch(\Exception $e){
                // В случае ошибки просто продолжаем с оригинальным файлом
                $backgroundRemoved = false;
            }
        }

        // Create entry to DB
        $attachment = new Attachment([
            'path' => $path,
            'name' => $originalName,
            'extension' => $finalExtension,
            'public' => 1,
            'meta' => [
                'original_extension' => $originalExtension,
                'background_removed' => $backgroundRemoved,
                'processed_at' => $backgroundRemoved ? now()->toISOString() : null
            ]
        ]);

        $attachment->user()->associate($this->user);

        if(!empty($this->account_id))
            $attachment->account_id = $this->account_id;

        if(!$attachment->save())
            return $this->error('Не удалось загрузить файл');

        return $this->success([
            'attachment_id' => $attachment->id,
            'uuid' => $attachment->uuid,
            'attachment' => $attachment,
            'background_removed' => $backgroundRemoved
        ]);
    }

    public function removeAttachment(Request $request){
        $uuid = $request->route('uuid');
        if(!$uuid)
            return $this->error('Файл не найден');

        $this->authorize_account();
        $file = $this->getBuilder(Attachment::class, [
            'uuid' => $uuid
        ])->first();

        if(!$file || !$file->path)
            return $this->error("Файл не найден");

        if(Storage::exists($file->path)){
            Storage::delete($file->path);
        }
        $file->delete();

        return $this->success();
    }

    public function removeMoreAttachment(Request $request){
        $ids = $request->input('ids');
        if(!$ids) return $this->error([], 'Файлы не найдены');
        $ids = explode(',', $ids);

        $this->authorize_account();

        foreach($ids as $id){
            /** @var Attachment $file */
            $file = $this->getBuilder(Attachment::class, compact('id'))->first();
            if(!$file || !$file->path) continue;

            if(Storage::exists($file->path))
                Storage::delete($file->path);

            $file->documents()->delete();
            $file->delete();
        }

        return $this->success();
    }

    public function getAttachmentList(Request $request){
        $this->authorize_account();
        $user = $request->user();
        $account = $this->account;

        if(!$user || !$account)
            return $this->error('no data');

        // build query
        $builder = Attachment::where('account_id', $account->id)
            ->where('public', true)
            ->orWhere('user_id', $user->id);

        // get
        $attachments = $builder->orderByDesc('created_at')->get();

        return $this->success(compact('attachments'));
    }

    public function getAttachment(Request $request){
        $uuid = $request->route('uuid');
        if(!$uuid)
            return $this->error("Файл не найден");

        $attachment = Attachment::where('uuid', $uuid)->first();
        
        if(!$attachment) {
            return $this->error("Файл не найден");
        }
        
        if($attachment->public) {
            return Storage::download($attachment->path, $attachment->name);
        }

        return '';
    }
}