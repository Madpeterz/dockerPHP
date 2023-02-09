<?php

namespace madpeterz\dockerphp\Builders;

use madpeterz\dockerphp\Payloads\HostConfig;
use madpeterz\dockerphp\Payloads\NetworkingConfig;
use stdClass;

class ContainersCreateConfig
{
    public HostConfig $HostConfig;
    //public NetworkingConfig $NetworkingConfig;
    public string $Image;
    public function __construct(
        string $SetImage,
    ) {
        $this->Image = $SetImage;
        $this->HostConfig = new HostConfig();
        //$this->NetworkingConfig = new NetworkingConfig();
        $this->ExposedPorts = new stdClass();
    }

    public stdClass $Labels;
    public stdClass $Volumes;
    public stdClass $ExposedPorts;

    public function addMapPort(int $host, int $container, bool $tcp = true): void
    {
        if ($tcp == false) {
            return;
        }
        $tag = $container . "/tcp";
        $this->ExposedPorts->$tag = new stdClass();
        $this->HostConfig->addMapPort($host, $container, $tcp);
    }
}
