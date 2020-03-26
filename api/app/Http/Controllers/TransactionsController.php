<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

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
        $validator = Validator::make($request->all(), [
            'from' => 'required|exists:accounts,id',
            'to' => 'required|exists:accounts,id',
            'amount' => 'required|min:1',
            'details' => 'required'
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 422);
        }
        $newTransaction = Transaction::create($validator->validated());

        return response()->json($newTransaction->load(['accountFrom', 'accountTo']));
    }
}
