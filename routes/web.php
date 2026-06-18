<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\AiAgentController;

Route::get('/', function () {
    return redirect()->route('dashboard.index');
});

Route::middleware('guest')->group(function () {
    Route::get('/login', [AuthController::class, 'login'])->name('login');
    Route::post('/login', [AuthController::class, 'authenticate'])->name('login.post');
});

Route::middleware('auth')->group(function () {
    Route::post('/logout', [AuthController::class, 'logout'])->name('logout');
    
    Route::get('/dashboard', function () {
        return view('dashboard.index');
    })->name('dashboard.index');

    Route::get('/nodes', function () {
        return view('nodes.index');
    })->name('nodes.index');

    Route::get('/vms', function () {
        return view('vms.index');
    })->name('vms.index');

    Route::get('/lxc', function () {
        return view('lxc.index');
    })->name('lxc.index');

    Route::get('/reports', function () {
        return view('reports.index');
    })->name('reports.index');
});

Route::post('/api/chat', [AiAgentController::class, 'chat'])->name('api.chat');
