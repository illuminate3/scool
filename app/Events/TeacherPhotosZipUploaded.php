<?php

namespace App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Queue\SerializesModels;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;

/**
 * Class TeacherPhotosZipUploaded.
 *
 * @package App\Events
 */
class TeacherPhotosZipUploaded
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $path;

    public $tenant;

    /**
     * TeacherPhotosZipUploaded constructor.
     *
     * @param $path
     * @param $tenant
     */
    public function __construct($path, $tenant)
    {
        $this->path = $path;
        $this->tenant = $tenant;
    }


    /**
     * Get the channels the event should broadcast on.
     *
     * @return \Illuminate\Broadcasting\Channel|array
     */
    public function broadcastOn()
    {
        return new PrivateChannel('channel-name');
    }
}
