<?php

namespace App\Notifications;

use Illuminate\Auth\Notifications\VerifyEmail;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\URL;

class AdminVerifyEmail extends VerifyEmail
{
    /**
     * Generate URL verifikasi yang menautkan ke route admin.verification.verify
     */
    protected function verificationUrl($notifiable)
    {
        $expiration = Carbon::now()->addMinutes(
            Config::get('auth.verification.expire', 60)
        );

        return URL::temporarySignedRoute(
            'admin.verification.verify',  // <-- nama route admin
            $expiration,
            [
                'id'   => $notifiable->getKey(),
                'hash' => sha1($notifiable->getEmailForVerification()),
            ]
        );
    }

    /**
     * Build the mail message.
     */
    public function toMail($notifiable)
    {
        $verifyUrl = $this->verificationUrl($notifiable);

        return (new MailMessage)
            ->subject('Verifikasi Email Admin')
            ->line('Silakan klik tombol di bawah untuk memverifikasi email Anda sebagai Admin.')
            ->action('Verifikasi Email', $verifyUrl)
            ->line('Jika Anda tidak melakukan permintaan ini, abaikan saja.');
    }
}
