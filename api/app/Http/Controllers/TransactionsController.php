<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Account;
use App\Transaction;

class TransactionsController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index($id)
    {
        $transactions = Account::find($id)->transactions();
        return response()->json($transactions);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $account = Account::find($request->id);
        if ($request->id != $request->input('from') || $request->id == $request->input('to')) {
            return response()->json([ 'status' => 'failed', 'message' => 'Unauthorized' ], 401);
        }
        if ($account->balance < $request->input('amount')) {
            return response()->json([ 'status' => 'failed', 'message' => 'Enter a valid amount' ], 400);
        }
        $validatedData = $request->validate([
            'from' => 'required|exists:accounts,id',
            'to' => 'required|exists:accounts,id',
            'amount' => 'required|min:1',
            'details' => 'required'
        ]);

        $newTransaction = Transaction::create($validatedData);

        return response()->json($newTransaction);
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        //
    }
}
