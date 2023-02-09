<?php

namespace madpeterz\dockerphp\Reply;

class JsonReply
{
    public function __construct(
        public readonly bool $status = false,
        public readonly ?array $data = null,
        public readonly string $errorMessage = "none"
    ) {
    }
}
