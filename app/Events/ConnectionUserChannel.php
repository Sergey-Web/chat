<?php

namespace App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Queue\SerializesModels;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;

class ConnectionUserChannel implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $channel;
    public $userId;
    public $agentId;
    public $messages;
    public $role;

    /**
     * Create a new event instance.
     *
     * @return void
     */
    public function __construct($data)
    {
        $this->channel = $data['channel'];
        $this->userId = $data['userId'];
        $this->agentId = $data['agentId'];
        $this->messages = $data['messages'];
        $this->role = isset($data['role']) ? $data['role'] : 4;
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
