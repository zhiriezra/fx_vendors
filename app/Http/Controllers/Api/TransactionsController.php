<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Exports\TransactionsExport;
use App\Models\Transaction;
use Maatwebsite\Excel\Facades\Excel;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;

class TransactionsController extends Controller
{
    public function exportTransactions()
    {
        // Get authenticated user
        $user = Auth::user();

        // Fetch only the user's transactions
        $transactions = Transaction::where('payable_id', $user->id)->get();
    
        if ($transactions->isEmpty()) {
            return response()->json(['status' => false, 'message' => 'No transactions found'], 404);
        }
    
        // Generate file name with timestamp
        $timestamp = Carbon::now()->format('Ymd_His');
        $fileName = "transactions_{$timestamp}.xlsx";
    
        return Excel::download(new TransactionsExport($transactions), $fileName);
    }
}
