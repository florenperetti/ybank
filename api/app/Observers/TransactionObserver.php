<?php

namespace App\Observers;

use App\Transaction;
use App\Account;

class TransactionObserver
{
    /**
     * Handle the transaction "created" event.
     * Update accounts balance given transaction ammount
     *
     * @param  \App\Transaction  $transaction
     * @return void
     */
    public function created(Transaction $transaction)
    {
        $accountTo = Account::find($transaction->to);
        $accountTo->balance += $transaction->amount;
        $accountTo->save();

        $accountFrom = Account::find($transaction->from);
        $accountFrom->balance -= $transaction->amount;
        $accountFrom->save();
    }
}
