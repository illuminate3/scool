<?php

namespace App\Listeners\Incidents;

use App\Mail\Incidents\IncidentCreated;
use App\Models\Setting;
use Mail;

/**
 * Class SendIncidentCreatedEmail.
 *
 * @package App\Listeners\Incidents
 */
class SendIncidentCreatedEmail
{
    /**
     * Create the event listener.
     *
     * @return void
     */
    public function __construct()
    {
        //
    }

    /**
     * Handle the event.
     *
     * @param  object  $event
     * @return void
     */
    public function handle($event)
    {
        Mail::to($event->incident->user)->cc(Setting::get('incidents_manager_email'))->queue(new IncidentCreated($event->incident));
    }
}
