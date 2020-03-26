<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Transaction extends Model
{
    protected $fillable = ['from', 'to', 'details', 'amount'];

    public $timestamps = false;

    protected $with = ['accountTo', 'accountFrom'];

    public function accountTo() {
        return $this->belongsTo(Account::class, 'to');
    }
    public function accountFrom() {
        return $this->belongsTo(Account::class, 'from');
    }
}
