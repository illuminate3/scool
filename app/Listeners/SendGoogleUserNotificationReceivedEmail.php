<?php

namespace App\Listeners;

use App\Mail\GoogleUserNotificationReceived;
use Mail;

/**
 * Class SendInvalidGoogleUserNotificationReceivedEmail.
 *
 * @package App\Listeners
 */
class SendInvalidGoogleUserNotificationReceivedEmail
{
    /**
     * Handle the event.
     *
     * @param  object  $event
     * @return void
     */
    public function handle($event)
    {
        if (config('scool.gsuite_notifications_send_email')) {
            Mail::to(config('scool.gsuite_notifications_email'))->send(new GoogleInvalidUserNotificationReceived($event->request));
        }
    }
}
