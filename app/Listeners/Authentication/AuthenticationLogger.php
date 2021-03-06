<?php

namespace App\Listeners\Authentication;

use App\Models\Log;
use App\Models\User;
use Carbon\Carbon;

/**
 * Class AuthenticationLogger.
 *
 * @package App\Listeners\Authentication
 */
class AuthenticationLogger
{
    /**
     * Incorrect attempt.
     *
     * @param $event
     */
    public static function incorrectAttempt($event)
    {
        Log::create([
            'text' => "Intent de login incorrecte amb l'usuari <strong>" . $event->credentials['email'] . "</strong>",
            'time' => Carbon::now(),
            'action_type' => 'error',
            'module_type' => 'UsersManagment',
            'icon' => 'error',
            'color' => 'error',
        ]);
    }

    /**
     * Logout.
     *
     * @param $event
     */
    public static function logout($event)
    {
        Log::create([
            'text' => "L'usuari/a " . $event->user->link() . ' ha sortit del sistema',
            'time' => Carbon::now(),
            'action_type' => 'exit',
            'user_id' => $event->user->id,
            'module_type' => 'UsersManagment',
            'loggable_id' => $event->user->id,
            'loggable_type' => User::class,
            'icon' => 'exit_to_app',
            'color' => 'purple',
        ]);
    }

    /**
     * leaveImpersonation.
     *
     * @param $event
     */
    public static function leaveImpersonation($event)
    {
        Log::create([
            'text' => "L'usuari/a admin " . $event->impersonator->link() . ' - ' . $event->impersonator->email . " ha deixat de fer-se passar per " . $event->impersonated->link() . ' - ' . $event->impersonated->email,
            'time' => Carbon::now(),
            'action_type' => 'update',
            'user_id' => $event->impersonator->id,
            'module_type' => 'UsersManagment',
            'loggable_id' => $event->impersonator->id,
            'loggable_type' => User::class,
            'icon' => 'edit',
            'color' => 'teal',
        ]);
    }

    /**
     * Take impersonation.
     *
     * @param $event
     */
    public static function takeImpersonation($event)
    {
        Log::create([
            'text' => "L'usuari/a admin " . $event->impersonator->link() . '  - ' . $event->impersonator->email . " s'esta fent passar per " . $event->impersonated->link() . ' - ' . $event->impersonated->email,
            'time' => Carbon::now(),
            'action_type' => 'update',
            'user_id' => $event->impersonator->id,
            'module_type' => 'UsersManagment',
            'loggable_id' => $event->impersonator->id,
            'loggable_type' => User::class,
            'icon' => 'edit',
            'color' => 'teal',
        ]);
    }

    /**
     * Login.
     *
     * @param $event
     */
    public static function login($event)
    {
        Log::create([
            'text' => "L'usuari/a " . $event->user->link() . ' (' . $event->user->email . ') ha entrat al sistema des de la IP: ' . $event->user->last_login_ip,
            'time' => Carbon::now(),
            'action_type' => 'enter',
            'user_id' => $event->user->id,
            'module_type' => 'UsersManagment',
            'loggable_id' => $event->user->id,
            'loggable_type' => User::class,
            'icon' => 'input',
            'color' => 'teal',
        ]);
    }

    /**
     * Password reset.
     *
     * @param $event
     */
    public static function passwordReset($event)
    {
        Log::create([
            'text' => "L'usuari/a " . $event->user->link() . ' ha modificat la paraula de pas',
            'time' => Carbon::now(),
            'action_type' => 'update',
            'user_id' => $event->user->id,
            'module_type' => 'UsersManagment',
            'loggable_id' => $event->user->id,
            'loggable_type' => User::class,
            'icon' => 'edit',
            'color' => 'teal',
        ]);
    }

    /**
     * Registered.
     *
     * @param $event
     */
    public static function registered($event)
    {
        Log::create([
            'text' => 'Usuari/a ' . $event->user->link() . " registrat amb l'email <strong> " . $event->user->email . '</strong>',
            'time' => $event->user->created_at,
            'user_id' => $event->user->id,
            'action_type' => 'store',
            'module_type' => 'UsersManagment',
            'loggable_id' => $event->user->id,
            'loggable_type' => User::class,
            'old_loggable' => null,
            'new_loggable' => method_exists($event->user,'map') ? json_encode($event->user->map()) : $event->user->toJson(),
            'icon' => 'input',
            'color' => 'success',
        ]);
    }

    /**
     * Verified user.
     *
     * @param $event
     */
    public static function verifiedUser($event)
    {
        Log::create([
            'text' => "L'usuari/a " . $event->user->link() . " ha verificat l'email <strong>" . $event->user->email . '</strong>',
            'time' => Carbon::now(),
            'action_type' => 'update',
            'user_id' => $event->user->id,
            'module_type' => 'UsersManagment',
            'loggable_id' => $event->user->id,
            'loggable_type' => User::class,
            'icon' => 'edit',
            'color' => 'teal',
        ]);
    }
}
