<?php

namespace App\Notifications;
use App\Mail\DefaultMailer;
use \Illuminate\Auth\Notifications\VerifyEmail as VerifyEmailBase;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Support\Facades\Lang;

class VerifyEmail extends VerifyEmailBase
{


    protected function buildMailMessage($url)
    {

        return (new MailMessage)
            /*->subject(Lang::get('Verify Email Address'))
            ->line(Lang::get('Please click the button below to verify your email address.'))
            ->action(Lang::get('Verify Email Address'), $url)
            ->line(Lang::get('If you did not create an account, no further action is required.'));*/
            ->subject('Подтверждение E-Mail')
            ->view('emails/verify-email', [
                'subject' => 'Подтверждение E-Mail',
                'url' => $url
            ]);
    }

}
