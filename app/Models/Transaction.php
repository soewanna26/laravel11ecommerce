<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Transaction extends Model
{
    use HasFactory;


    public function user()
    {
        $this->belongsTo(User::class);
    }


    public function order()
    {
        $this->belongsTo(Order::class);
    }
}
