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

Route::get('/forgot-password', [AuthController::class, 'showForgotPassword'])->name('password.request')->middleware('guest');
Route::post('/forgot-password', [AuthController::class, 'sendResetLinkEmail'])->name('password.email')->middleware('guest');
Route::get('/reset-password/{token}', [AuthController::class, 'showResetPassword'])->name('password.reset')->middleware('guest');
Route::post('/reset-password', [AuthController::class, 'resetPassword'])->name('password.update')->middleware('guest');

Route::middleware(['auth'])->group(function () {
    Route::get('/', function () {
        return redirect()->route('dashboard.index');
    });

    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard.index');
    Route::get('/api/dashboard/insights', [DashboardController::class, 'aiInsights'])->name('api.dashboard.insights');
    Route::get('/api/nodes/{node?}/network-traffic', [DashboardController::class, 'nodeNetworkTraffic'])->name('api.nodes.network-traffic');
    
    Route::resource('nodes', ServerFisikController::class)->names('nodes');
    Route::resource('vps', VirtualMachineController::class)->names('vps');
    Route::resource('users', UserController::class)->names('users');
    
    // Other routes
    Route::get('/reports', [\App\Http\Controllers\ReportController::class, 'index'])->name('reports.index');
    Route::post('/reports/export', [\App\Http\Controllers\ReportController::class, 'exportPdf'])->name('reports.export');

    // AI Agent Full Page & Endpoints
    Route::get('/ai-agent', [AiAgentController::class, 'index'])->name('ai.index');

    // Global Navbar Realtime Search
    Route::get('/api/search', [\App\Http\Controllers\SearchController::class, 'liveSearch'])->name('api.search');
});

Route::get('/api/agent/status', [AiAgentController::class, 'status'])->name('api.agent.status');
Route::post('/api/agent/reset', [AiAgentController::class, 'reset'])->name('api.agent.reset');
Route::post('/api/chat', [AiAgentController::class, 'chat'])->name('api.chat');
Route::post('/api/ai/export-pdf', [AiAgentController::class, 'exportPdf'])->name('api.ai.export_pdf');
Route::match(['get', 'post'], '/api/ai/download-file', [AiAgentController::class, 'downloadFile'])->name('api.ai.download_file');

