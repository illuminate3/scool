<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Contracts\Queue\ShouldQueue;

/**
 * Class TeacherWelcome.
 *
 * @package App\Mail
 */
class TeacherWelcome extends Mailable
{
    use Queueable, SerializesModels;

    public $body = "# Introduction

The body of your message.";

    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct()
    {
        //
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        return $this->markdown('tenants.emails.teachers.welcome')
            ->attach(public_path('img/app-bg.png'));
    }
}
