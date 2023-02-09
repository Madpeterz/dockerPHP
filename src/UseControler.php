<?php

namespace madpeterz\dockerphp;

abstract class UseControler
{
    public function __construct(protected SocketControl $interface)
    {
    }
}
