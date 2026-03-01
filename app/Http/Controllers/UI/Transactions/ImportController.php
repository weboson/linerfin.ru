<?php
// для "выгурзка с банка" 

namespace App\Http\Controllers\UI\Transactions;

use App\Http\Abstraction\AccountAbstract;
use App\Imports\BankStatementImport;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;
use Illuminate\Validation\ValidationException;
use Illuminate\Support\Facades\Log;

class ImportController extends AccountAbstract
{
    public function import(Request $request)
    {
        // Логируем для отладки
        Log::info('=== НАЧАЛО ИМПОРТА ===');

        try {
            // Базовая проверка наличия файла
            if (!$request->hasFile('file')) {
                return $this->error('Файл не загружен', 'Ошибка');
            }

            $file = $request->file('file');

            // Проверяем расширение файла
            $extension = strtolower($file->getClientOriginalExtension());
            $allowedExtensions = ['xlsx', 'xls', 'csv'];

            if (!in_array($extension, $allowedExtensions)) {
                Log::warning('Недопустимое расширение файла', ['extension' => $extension]);
                return $this->error(
                    'Файл должен быть формата xlsx, xls или csv. Получен: ' . $extension,
                    'Ошибка валидации'
                );
            }

            // Проверяем размер файла (10MB)
            if ($file->getSize() > 10 * 1024 * 1024) {
                return $this->error('Файл слишком большой. Максимальный размер 10MB', 'Ошибка валидации');
            }

            // Проверяем наличие ID счета
            if (!$request->filled('checking_account_id')) {
                return $this->error('Не указан счет для импорта', 'Ошибка валидации');
            }

            // Проверяем существование счета
            $checkingAccount = $this->account->checkingAccounts()
                ->find($request->checking_account_id);

            if (!$checkingAccount) {
                Log::warning('Счет не найден', ['account_id' => $request->checking_account_id]);
                return $this->error('Счет не найден в вашем аккаунте', 'Ошибка');
            }

            Log::info('Валидация пройдена', [
                'account_id' => $this->account->id,
                'checking_account_id' => $checkingAccount->id,
                'file' => $file->getClientOriginalName(),
                'extension' => $extension,
                'size' => $file->getSize()
            ]);

            // Импортируем
            $import = new BankStatementImport($this->account, $checkingAccount->id);
            Excel::import($import, $file);

            $importedCount = $import->getImportedCount();
            $errors = $import->getErrors();

            Log::info('Импорт завершен', [
                'imported' => $importedCount,
                'errors' => count($errors)
            ]);

            $message = "Импорт завершен. Добавлено записей: {$importedCount}";

            $responseData = [
                'message' => $message,
                'imported_count' => $importedCount
            ];

            if (!empty($errors)) {
                $responseData['warnings'] = $errors;
            }

            return $this->success($responseData);
        } catch (\Maatwebsite\Excel\Validators\ValidationException $e) {
            $failures = $e->failures();
            $errors = [];
            foreach ($failures as $failure) {
                $errors[] = "Строка {$failure->row()}: " . implode(', ', $failure->errors());
            }

            Log::error('Ошибка валидации Excel', ['errors' => $errors]);
            return $this->error(['excel_errors' => $errors], 'Ошибка в данных Excel');
        } catch (\Exception $e) {
            Log::error('Ошибка импорта', [
                'message' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString()
            ]);

            return $this->error(
                'Ошибка при импорте: ' . $e->getMessage(),
                'Ошибка импорта'
            );
        }
    }
}
