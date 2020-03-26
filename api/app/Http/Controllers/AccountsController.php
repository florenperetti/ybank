<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Account;

class AccountsController extends Controller
{
    /**
     * Display the specified resource.
     *
     * @param  Account  $account
     * @return \Illuminate\Http\Response
     */
    public function show(Account $account)
    {
        return response()->json([$account]);
    }
}
