<?php

namespace App\Http\Controllers;

use App\Http\Abstraction\AccountAbstract;
use App\Models\Bill;
use Illuminate\Http\Request;
use mikehaertl\wkhtmlto\Pdf;
use Illuminate\Support\Facades\Storage;

class OutsideController extends AccountAbstract
{
    protected $middlewareAuthorize = false;

    /**
     * Вспомогательный метод для получения Base64
     */
    private function getBase64Image($attachment)
    {
        if (!$attachment) {
            return null;
        }

        // Пробуем разные варианты путей
        $paths = [
            storage_path("app/public/attachments/{$attachment->uuid}"), // Стандартный storage
            storage_path("app/attachments/{$attachment->uuid}"),        // Если нет public
            public_path("ui/attachments/{$attachment->uuid}"),          // Если они в public/ui
        ];

        foreach ($paths as $fullPath) {
            if (file_exists($fullPath)) {
                $file = file_get_contents($fullPath);
                $type = mime_content_type($fullPath);
                return 'data:' . $type . ';base64,' . base64_encode($file);
            }
        }

        // Если ничего не нашли, попробуем через Storage (на случай S3 или других драйверов)
        if (isset($attachment->path) && Storage::exists($attachment->path)) {
            $file = Storage::get($attachment->path);
            $type = Storage::mimeType($attachment->path);
            return 'data:' . $type . ';base64,' . base64_encode($file);
        }

        return null;
    }

    /**
     * МЕТОД ДЛЯ ПРОСМОТРА (Кнопка "Печать")
     */
    public function billView(Request $request)
    {
        $link = $request->route('link');
        $bill = Bill::with([
            'account',
            'counterparty',
            'positions.nds_type',
            'stamp_attachment',
            'signature_list_with_attachments'
        ])->whereLink($link)->first();

        if (!$bill) return response('', 404);

        // Проверка доступа (если нужно)
        if ($bill->access === 'account') {
            $private_key = $request->input('private-key');
            if (!$private_key || $bill->private_key !== $private_key) {
                $this->authorize_account();
                if ($bill->account_id !== $this->account_id)
                    return response('', 403);
            }
        }

        // Подготавливаем картинки в Base64 для отображения
        $bill->stamp_base64 = $this->getBase64Image($bill->stamp_attachment);

        if ($bill->signature_list_with_attachments) {
            foreach ($bill->signature_list_with_attachments as $sig) {
                if (isset($sig->signature_attachment)) {
                    $sig->signature_base64 = $this->getBase64Image($sig->signature_attachment);
                }
            }
        }

        $subdomain = $bill->account->subdomain;

        // Возвращаем просто вьюху в браузер
        return view('account.bill-pdf', compact('bill', 'request', 'subdomain'));
    }

    /**
     * МЕТОД ДЛЯ СКАЧИВАНИЯ (Кнопка "Скачать")
     */
    public function billDownload(Request $request)
    {
        $link = $request->route('link');
        $bill = Bill::with([
            'account',
            'counterparty',
            'positions.nds_type',
            'stamp_attachment',
            'signature_list_with_attachments'
        ])->whereLink($link)->first();

        if (!$bill) return response('', 404);

        if ($bill->access === 'account') {
            $this->authorize_account();
            if ($bill->account_id !== $this->account_id)
                return response('', 403);
        }

        // Подготовка Base64
        $bill->stamp_base64 = $this->getBase64Image($bill->stamp_attachment);

        if ($bill->signature_list_with_attachments) {
            foreach ($bill->signature_list_with_attachments as $sig) {
                if (isset($sig->signature_attachment)) {
                    $sig->signature_base64 = $this->getBase64Image($sig->signature_attachment);
                }
            }
        }

        $subdomain = $bill->account->subdomain;

        // Рендерим HTML для PDF
        $html = view('account.bill-pdf', [
            'bill' => $bill,
            'subdomain' => $subdomain,
            'isPdf' => true,
        ])->render();

        $pdf = new Pdf($html);

        $filename = "Счет №" . ($bill->num ?? date('d-m-Y'));
        if ($bill->counterparty) $filename .= " " . $bill->counterparty->name;
        if (mb_strlen($filename) > 70) $filename = "Счет " . date('d-m-Y');

        if (!$pdf->send($filename . '.pdf')) {
            return response($pdf->getError(), 500);
        }

        return '';
    }
}
