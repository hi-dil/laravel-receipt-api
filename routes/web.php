<?php

use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

Route::get('/receipt', function() {
    return view('receipt');
});

Route::get('/receiptdetails', function() {
    return view('receiptdetails');
});
