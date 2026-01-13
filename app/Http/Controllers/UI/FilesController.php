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
        $extension = $attachment->extension();


        // Check extension
        if(false === array_search(strtolower($extension), ['png', 'jpg', 'bmp', 'jpeg', 'doc', 'docx', 'xls', 'xlsx', 'pdf', 'ppt', 'pptx']))
            return $this->error("Файл не поддерживается");

        // Save file
        $path = "attachments" . ($this->subdomain ? "/$this->subdomain" : '/common');
        $path = Storage::putFile($path, $attachment);
        if(!$path)
            return $this->error("Не удалось загрузить файл");


        // Remove white background
        if($extension == 'png' && isset($_GET['remove-background'])) try{
            ImageMagickUtils::image(Storage::path($path))
                ->addTransparency(255, 255, 255, Storage::path($path));
        }
        catch(\Exception $e){
            return $this->error(['errors' => [$e->getMessage()]]);
        }

        // Create entry to DB
        $attachment = new Attachment([
            'path' => $path,
            'name' => $attachment->getClientOriginalName(),
            'extension' => $extension,
            'public' => 1
        ]);

        $attachment->user()->associate($this->user);

        if(!empty($this->account_id))
            $attachment->account_id = $this->account_id;

        if(!$attachment->save())
            return $this->error('Не удалось загрузить файл');

        return $this->success([
            'attachment_id' => $attachment->id,
            'uuid' => $attachment->uuid,
            'attachment' => $attachment
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
        if($attachment->public)
            return Storage::download($attachment->path, $attachment->name);

        /*if($attachment->account_public){
            if($attachment->account()->id === $this->account_id)
                return Storage::download($attachment->path, $attachment->name);
        }

        if($attachment->user()->id === $this->user->id)
            return Storage::download($attachment->path, $attachment->name);*/

        return '';
    }
}
