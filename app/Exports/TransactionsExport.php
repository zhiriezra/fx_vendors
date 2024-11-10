<?php

namespace App\Exports;

use App\Models\Transaction;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\WithHeadings;

class TransactionsExport implements FromQuery, WithHeadings
{
    /**
    * @return \Illuminate\Support\Collection
    */
    public function query()
    {
        return Transaction::query()
            ->join('users', 'transactions.payable_id', '=', 'users.id')
            ->where('transactions.payable_id', Auth::id())
            ->select(
                'transactions.uuid',
                'transactions.type',
                'transactions.amount',
                'transactions.confirmed',
                DB::raw("CONCAT(users.firstname, ' ', users.lastname) as user_fullname"),
                'transactions.created_at'
            );
    }

    public function headings(): array
    {
        return [
            'Transaction ID',
            'Type',
            'Amount',
            'Confirmed',
            'Wallet Holder',
            'Date',
        ];
    }
}
