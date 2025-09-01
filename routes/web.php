<?php

use Illuminate\Support\Facades\Route;


Route::get('/', function () {
    return view('welcome');
});

Route::get('/admin/dashboard', function(){
    return view ('admin.dashboard');
})->name('admin.dashboard');

Route::get('/user/register', function(){
    return view ('users.register');
})->name('user.register');

Route::get('/user/login', function(){
    return view ('users.login');
})->name('user.login');

Route::get('/user/dashboard', function(){
    return view ('users.dashboard');
})->name('user.dahboard');

