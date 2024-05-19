<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Receipt extends Model
{
    protected $fillable = ['imagelink', 'receiptdata', 'status'];
    use HasFactory;
}
