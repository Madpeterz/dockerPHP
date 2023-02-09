<?php

namespace App;

include "../../vendor/autoload.php";

function getEnvNamed(string $env, string $default): string
{
    $v = getenv($env);
    if ($v  !== false) {
        return $v;
    }
    return $default;
}
