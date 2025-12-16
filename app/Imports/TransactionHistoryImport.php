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

class TransactionHistoryImport implements ToModel, WithHeadingRow, WithValidation
{
    public function model(array $rows)
    {
        $timeConverted = Date::excelToDateTimeObject($rows['transaction_time']);
        $timeString = Carbon::parse($timeConverted)->toTimeString();
        return new TransactionHistory([
            'transaction_date' => Carbon::parse($rows['transaction_date'])->toDateString(),
            'transaction_time' => Carbon::createFromFormat('H:i:s', $timeString),
            'amount' => $rows['amount'],
            'remarks' => $rows['remarks']
        ]);
    }

    public function startRow(): int
    {
        return 2;
    }
    
    public function rules(): array
    {
        return [
            'transaction_date' => ['required', 'date'],
            'transaction_time' => ['required', 'date_format:H:i:s'],
            'amount' => ['required', 'numeric'],
            'remarks' => ['nullable', 'string', 'max:1000'],
        ];
    }
}