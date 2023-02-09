<?php

namespace madpeterz\dockerphp;

use madpeterz\dockerphp\Reply\BasicReply;
use YAPF\Core\ErrorControl\ErrorLogging;

class Docker extends ErrorLogging
{
    public Containers $containers;
    public Images $images;
    public Auth $auth;

    protected SocketControl $interface;
    public function __construct(string $socket, int $timeoutMS = 3000)
    {
        $this->interface = new SocketControl($socket, $timeoutMS);
        $this->containers = new Containers($this->interface);
        $this->images = new Images($this->interface);
        $this->auth = new Auth($this->interface);
    }
    public function connected(): bool
    {
        if ($this->interface->dockerLink == null) {
            $this->interface->attachDockerObject($this);
        }
        return $this->interface->connected();
    }
    public function apiVersion(): BasicReply
    {
        $reply = $this->interface->get("version");
        if ($reply->status == false) {
            return new BasicReply(errorMessage: $reply->errorMessage);
        }
        return new BasicReply(status: true, body: json_decode($reply->body, true)["ApiVersion"]);
    }
}
