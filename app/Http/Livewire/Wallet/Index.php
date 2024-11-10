<?php

namespace App\Http\Livewire\Wallet;

use Livewire\Component;
use App\Models\Transaction;
use App\Exports\TransactionsExport;
use Maatwebsite\Excel\Facades\Excel;

class Index extends Component
{
    public $balance;

    public function render()
    {
        return view('livewire.wallet.index', [
            'transactions' => $this->transactions
        ]);
    }

    public function exportTransactions()
    {
        $date = date('Y-m-d');
        $fileName = "transactions_{$date}.xlsx";

        return Excel::download(new TransactionsExport, $fileName);
    }

    public function mount(){
        $user = auth()->user();
        $this->balance = $user->balance;

        $this->transactions = Transaction::with('payable')
            ->orderBy('updated_at', 'DESC')
            ->where('payable_id', $user->id)
            ->get();
    }
}
