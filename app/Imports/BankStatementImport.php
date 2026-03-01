<?php
// app/Imports/BankStatementImport.php

namespace App\Imports;

// импорт (парсинг) данных с таблицы с помощью библиотеки "maatwebsite"
use App\Models\Transaction;
use App\Models\Counterparty;
use Carbon\Carbon;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithStartRow;
use Maatwebsite\Excel\Concerns\SkipsOnError;
use Maatwebsite\Excel\Concerns\Importable;
use Maatwebsite\Excel\Concerns\SkipsErrors;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Http\Controllers\UI\Transactions\Calculator;

class BankStatementImport implements ToModel, WithStartRow, SkipsOnError
{
    use Importable, SkipsErrors;

    protected $account;
    protected $checkingAccountId;
    protected $calculator;
    protected $importedCount = 0;
    protected $errors = [];
    protected $rowNumber = 0;

    public function __construct($account, $checkingAccountId)
    {
        $this->account = $account;
        $this->checkingAccountId = $checkingAccountId;
        $this->calculator = new Calculator($account);
    }

    public function startRow(): int
    {
        return 13; // Начинаем с 13 строки, где данные
    }

    public function model(array $row)
    {
        $this->rowNumber++;
        $currentExcelRow = $this->rowNumber + 12;

        // Проверяем наличие даты (колонка C - индекс 2)
        if (!isset($row[2]) || empty($row[2])) {
            return null;
        }

        try {
            // 1. ДАТА - колонка C (индекс 2)
            $date = $this->parseExcelDate($row[2]);
            if (!$date) {
                return null;
            }

            // 2. ДЕБЕТ (расход) - колонка J (индекс 9)
            $debit = isset($row[9]) && is_numeric($row[9]) ? floatval($row[9]) : 0;

            // 3. КРЕДИТ (приход) - колонка K (индекс 10)
            $credit = isset($row[10]) && is_numeric($row[10]) ? floatval($row[10]) : 0;

            // Определяем тип и сумму
            $amount = 0;
            $type = null;

            if ($credit > 0) {
                $amount = $credit;
                $type = 'income'; // КРЕДИТ = ПРИХОД
            } elseif ($debit > 0) {
                $amount = $debit;
                $type = 'expense'; // ДЕБЕТ = РАСХОД
            } else {
                return null;
            }

            // 4. КОНТРАГЕНТ - колонка G (индекс 6) - "Реквизиты корреспондента /Counter party details"
            $counterpartyDetails = isset($row[6]) ? trim($row[6]) : '';

            // 5. НАЗНАЧЕНИЕ ПЛАТЕЖА - колонка L (индекс 11)
            $purpose = isset($row[11]) ? trim($row[11]) : '';

            // Ищем или создаем контрагента по реквизитам из колонки G
            $counterparty = null;
            if (!empty($counterpartyDetails) && !is_numeric($counterpartyDetails)) {
                $counterparty = $this->findOrCreateCounterparty($counterpartyDetails);
            }

            // Создаем транзакцию
            return DB::transaction(function () use ($date, $amount, $type, $counterparty, $purpose) {

                $transactionData = [
                    'account_id' => $this->account->id,
                    'type' => $type,
                    'amount' => $amount,
                    'date' => $date,
                    // 'description' => $purpose,
                    'purpose' => $purpose, // назначение платежа
                    'counterparty_id' => $counterparty ? $counterparty->id : null, // ID контрагента
                ];

                // Счет из Popup окна
                if ($type === 'income') {
                    $transactionData['to_ca_id'] = $this->checkingAccountId; // Приход - на счет
                } else {
                    $transactionData['from_ca_id'] = $this->checkingAccountId; // Расход - со счета
                }

                $transaction = new Transaction($transactionData);
                $transaction->save();

                $this->calculator->calculateTotalBalance($transaction);
                $this->calculator->calculateAccountBalances($transaction);

                if ($date->lte(Carbon::now())) {
                    $this->calculator->Core->madePayment($transaction);
                }

                $this->importedCount++;

                return $transaction;
            });
        } catch (\Exception $e) {
            Log::error("Ошибка в строке {$currentExcelRow}: " . $e->getMessage());
            $this->errors[] = "Строка {$currentExcelRow}: " . $e->getMessage();
            return null;
        }
    }

    protected function parseExcelDate($value)
    {
        if (empty($value)) {
            return null;
        }

        try {
            if (is_string($value) && strpos($value, '-') !== false) {
                return Carbon::parse($value);
            }

            if (is_numeric($value)) {
                return Carbon::createFromFormat('Y-m-d', '1900-01-01')
                    ->addDays(intval($value) - 2);
            }
        } catch (\Exception $e) {
            return null;
        }

        return null;
    }

    protected function findOrCreateCounterparty($details)
    {
        if (empty($details)) {
            return null;
        }

        // Извлекаем ИНН из реквизитов
        $inn = $this->extractInn($details);

        // Извлекаем название (очищаем от ИНН и лишних символов)
        $name = $this->extractName($details);

        // Ищем по ИНН или названию
        $counterparty = $this->account->counterparties()
            ->where(function ($query) use ($name, $inn) {
                if ($inn) {
                    $query->where('inn', $inn);
                } else {
                    $query->where('name', 'LIKE', '%' . $name . '%');
                }
            })
            ->first();

        // Если не нашли - создаем нового контрагента
        if (!$counterparty) {
            $counterparty = $this->account->counterparties()->create([
                'name' => $name,
                'inn' => $inn
            ]);
            Log::info("Создан новый контрагент: {$name}, ИНН: {$inn}");
        }

        return $counterparty;
    }

    protected function extractInn($text)
    {
        // Ищем ИНН в формате "ИНН: 5902202276"
        preg_match('/ИНН:\s*(\d{10}|\d{12})/', $text, $matches);

        if (empty($matches)) {
            // Ищем просто последовательность из 10 или 12 цифр
            preg_match('/(\d{10}|\d{12})/', $text, $matches);
        }

        return $matches[1] ?? null;
    }

    protected function extractName($text)
    {
        // Удаляем ИНН из текста
        $name = preg_replace('/,\s*ИНН:\s*\d+/', '', $text);
        $name = preg_replace('/\s+ИНН:\s*\d+/', '', $name);
        $name = trim($name);

        // Если после удаления ИНН осталась пустая строка, возвращаем исходный текст
        if (empty($name)) {
            $name = $text;
        }

        return $name;
    }

    public function getImportedCount()
    {
        return $this->importedCount;
    }

    public function getErrors()
    {
        return $this->errors;
    }
}
