<?php

namespace App\Providers;

use Illuminate\Auth\Notifications\ResetPassword;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        ResetPassword::toMailUsing(function (object $notifiable, string $token) {
            $url = route('password.reset', [
                'token' => $token,
                'email' => $notifiable->getEmailForPasswordReset(),
            ]);

            return (new MailMessage)
                ->subject('Pemulihan Akses / Reset Password - SIMOX AI')
                ->greeting('Halo, ' . ($notifiable->name ?? 'Admin'))
                ->line('Kami menerima permintaan untuk mereset password akun SIMOX AI Infrastructure Anda.')
                ->action('Pulihkan Akses / Reset Password', $url)
                ->line('Tautan pemulihan ini berlaku selama 60 menit.')
                ->line('Jika Anda tidak merasa melakukan permintaan ini, tidak ada tindakan lebih lanjut yang diperlukan.')
                ->salutation('Salam hormat, Tim SIMOX AI');
        });
    }
}
