<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Account extends Model
{
    protected $fillable = [ 'name', 'balance' ];

    public $timestamps = false;

    public function transactions() {
        return Transaction::where(function($q) {
            $q->where('from', $this->id)
            ->orWhere('to', $this->id);
        })
        ->get();
    }
}
