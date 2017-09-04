<?php

namespace App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Queue\SerializesModels;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;

class ConnectUserChannel implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $channel = NULL;
    public $userId = NULL;
    public $role = NULL;

    /**
     * Create a new event instance.
     *
     * @return void
     */
    public function __construct($channel, $data)
    {
        $this->channel = $channel;
        $this->userId = $data['userId'];
        $this->role = $data['role'];
    }

    /**
     * Get the channels the event should broadcast on.
     *
     * @return \Illuminate\Broadcasting\Channel|array
     */
    public function broadcastOn()
    {
        //return new PrivateChannel('name-channel');
        return [$this->channel];
    }

    public function broadcastAs()
    {
        return 'birdchat';
    }
}
