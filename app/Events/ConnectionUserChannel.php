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
    public $name;
    public $role;
    public $connect;
    public $storageInvite;

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
        $this->name = isset($data['name']) ? $data['name'] : '';
        $this->messages = isset($data['messages']) ? $data['messages'] : '';
        $this->role = isset($data['role']) ? $data['role'] : 4;
        $this->connect = isset($data['connect']) ? $data['connect'] : '';
        $this->storageInvite = isset($data['storageInvite']) ? $data['storageInvite'] : 'true';
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
