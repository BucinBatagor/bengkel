<?php

namespace App\Models;

use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Auth\MustVerifyEmail as MustVerifyEmailTrait;
use App\Notifications\ResetPasswordNotificationForAdmin;
use App\Notifications\AdminVerifyEmail;  // import custom notification

class Admin extends Authenticatable implements MustVerifyEmail
{
    use Notifiable, MustVerifyEmailTrait;

    protected $table = 'admin';
    protected $fillable = ['name', 'email', 'password'];
    protected $hidden   = ['password', 'remember_token'];

    public function sendPasswordResetNotification($token)
    {
        $this->notify(new ResetPasswordNotificationForAdmin($token));
    }

    /**
     * Kirim notification verifikasi email khusus Admin
     */
    public function sendEmailVerificationNotification()
    {
        $this->notify(new AdminVerifyEmail());
    }
}
