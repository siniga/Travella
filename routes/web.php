<?php

use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

Route::get('/selcom-payment-test', function () {
    return view('selcom-test');
});
