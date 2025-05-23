<?php

namespace App\Exports;

use App\Models\Transaction;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\FromCollection;
use Carbon\Carbon;

class TransactionsExport implements FromQuery, WithHeadings, WithMapping
{
    protected $transactions;

    public function __construct($transactions)
    {
        $this->transactions = $transactions;
    }

    public function collection()
    {
        return $this->transactions;
    }
    
    public function map($transaction): array
    {
        return [
            $transaction->type,
            number_format($transaction->amount, 2, '.', ','), // Format amount
            $transaction->confirmed,
            $this->formatMeta($transaction->meta),
            Carbon::parse($transaction->created_at)->format('M j, Y, g:ia'),
            Carbon::parse($transaction->updated_at)->format('M j, Y, g:ia'),
        ];
    }

    public function headings(): array
    {
        return [
            'Type',
            'Amount',
            'Meta',
            'Confirmed',
            'Created At',
            'Updated At',
        ];
    }

    protected function formatMeta($meta)
    {
        if (is_array($meta) || is_object($meta)) {
            return json_encode($meta);
        }

        return $meta ?? '-';
    }
}
