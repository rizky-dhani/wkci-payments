<?php

namespace App\Imports;

use Carbon\Carbon;
use App\Models\TransactionHistory;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\ToModel;
use PhpOffice\PhpSpreadsheet\Shared\Date;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithValidation;

class TransactionHistoryImport implements ToModel, WithHeadingRow
{
    public function model(array $rows)
    {
        $timeConverted = Date::excelToDateTimeObject($rows['transaction_time']);
        $timeString = Carbon::parse($timeConverted)->toTimeString();
        
        // Parse transaction date from Excel - it could be a numeric Excel date or a string
        $transactionDateValue = $rows['transaction_date'];
        $dateObject = null;
        
        if (is_numeric($transactionDateValue)) {
            // If it's a numeric Excel timestamp, convert it to DateTime object
            $dateObject = Date::excelToDateTimeObject($transactionDateValue);
        } else {
            // If it's a string, try to parse it directly
            $dateObject = Carbon::parse($transactionDateValue);
        }

        return new TransactionHistory([
            'transaction_date' => $dateObject,
            'transaction_time' => Carbon::createFromFormat('H:i:s', $timeString),
            'amount' => $rows['amount'],
            'remarks' => $rows['remarks']
        ]);
    }

    public function startRow(): int
    {
        return 2;
    }
}