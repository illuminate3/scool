<?php

namespace App\Listeners\Authentication;

use App\Models\Log;
use App\Models\User;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;

/**
 * Class LogRegisteredUser.
 * @package App\Listeners\Authentication
 */
class LogRegisteredUser
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
        Log::create([
            'text' => 'Usuari/a <strong>' . $event->user->name . "</strong> registrat amb l'email <strong> " . $event->user->email . '</strong>',
            'time' => $event->user->created_at,
            'user_id' => $event->user->id,
            'action_type' => 'store',
            'module_type' => 'UsersManagment',
            'loggable_id' => $event->user->id,
            'loggable_type' => User::class,
            'persistedLoggable' => json_encode($event->user->map()),
            'icon' => 'input',
            'color' => 'success',
        ]);
    }
}
