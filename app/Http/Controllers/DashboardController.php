<?php

namespace App\Http\Controllers;

use App\Models\Transaction;
use App\Services\TransactionService;

class DashboardController extends Controller
{
    protected $transaction;
    public function __construct(
        TransactionService $transactionService
    ) {
        $this->transaction = $transactionService;
    }

    public function subscriptions()
    {
        $transactions = $this->transaction->getUserTransactions();
        return view('front.subscriptions', compact('transactions'));
    }

    public function subscriptionDetail(Transaction $transaction)
    {
        return view('front.subscriptions-detail', compact('transaction'));
    }
}
