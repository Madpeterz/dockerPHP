<?php

namespace madpeterz\dockerphp\Payloads;

use stdClass;

class NetworkingConfig
{
    public stdClass $EndpointsConfig;
    public function __construct()
    {
        $this->EndpointsConfig = new stdClass();
        $this->EndpointsConfig->bridge = new stdClass();
        $this->EndpointsConfig->bridge->IPAMConfig = [
            "IPv4Address" => "",
            "IPv6Address" => "",
        ];
    }
}
