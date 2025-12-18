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
        $dateConverted = Date::excelToDateTimeObject($rows['transaction_date']);
        $dateString = Carbon::parse($dateConverted)->toDateString();
        $timeConverted = Date::excelToDateTimeObject($rows['transaction_time']);
        $timeString = Carbon::parse($timeConverted)->toTimeString();
        return new TransactionHistory([
            'transaction_date' => Carbon::createFromFormat('Y-m-d', $dateString),
            // 'transaction_date' => Carbon::createFromFormat('Y-m-d', $rows['transaction_date']),
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