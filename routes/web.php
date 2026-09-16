<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\AiAgentController;
use App\Http\Controllers\DashboardController;
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

    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard.index');
    Route::get('/api/dashboard/insights', [DashboardController::class, 'aiInsights'])->name('api.dashboard.insights');
    
    Route::resource('nodes', ServerFisikController::class)->names('nodes');
    Route::resource('vps', VirtualMachineController::class)->names('vps');
    Route::resource('users', UserController::class)->names('users');
    
    // Other routes
    Route::get('/reports', [\App\Http\Controllers\ReportController::class, 'index'])->name('reports.index');
    Route::post('/reports/export', [\App\Http\Controllers\ReportController::class, 'exportPdf'])->name('reports.export');

    // AI Agent Full Page & Endpoints
    Route::get('/ai-agent', [AiAgentController::class, 'index'])->name('ai.index');
});

Route::get('/api/agent/status', [AiAgentController::class, 'status'])->name('api.agent.status');
Route::post('/api/agent/reset', [AiAgentController::class, 'reset'])->name('api.agent.reset');
Route::post('/api/chat', [AiAgentController::class, 'chat'])->name('api.chat');
