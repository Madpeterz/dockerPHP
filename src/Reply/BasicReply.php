<?php

namespace madpeterz\dockerphp\Reply;

use Psr\Http\Message\ResponseInterface;

class BasicReply
{
    public function __construct(
        public readonly bool $status = false,
        public readonly ?string $body = null,
        public readonly string $errorMessage = "none"
    ) {
    }
}
