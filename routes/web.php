<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\AiAgentController;
use App\Http\Controllers\ServerFisikController;
use App\Http\Controllers\VirtualMachineController;
use App\Http\Controllers\UserController;

Route::get('/login', [AuthController::class, 'login'])->name('login')->middleware('guest');
Route::post('/login', [AuthController::class, 'authenticate'])->name('login.post');
Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

Route::middleware(['auth'])->group(function () {
    Route::get('/', function () {
        return redirect()->route('dashboard.index');
    });

    Route::get('/dashboard', function () {
        return view('dashboard.index');
    })->name('dashboard.index');
    
    Route::resource('nodes', ServerFisikController::class)->names('nodes');
    Route::resource('vms', VirtualMachineController::class)->names('vms');
    Route::resource('users', UserController::class)->names('users');
    
    // Other routes
    Route::get('/lxc', function () {
        return view('lxc.index');
    })->name('lxc.index');
    
    Route::get('/reports', function () {
        return view('reports.index');
    })->name('reports.index');
});

Route::post('/api/chat', [AiAgentController::class, 'chat'])->name('api.chat');
