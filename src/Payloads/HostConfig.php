<?php

namespace madpeterz\dockerphp\Payloads;

use stdClass;

class HostConfig
{
    public function __construct()
    {
        $this->PortBindings = new stdClass();
    }

    public stdClass $PortBindings;

    public function addMapPort(int $host, int $container, bool $tcp = true): void
    {
        if ($tcp == false) {
            return; // udp support soon (TM)
        }
        $adding = new stdClass();
        $adding->HostPort = (string)$host;
        $tag = $container . "/tcp";
        $this->PortBindings->$tag = [$adding];
    }
}
