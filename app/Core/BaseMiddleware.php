<?php

declare(strict_types=1);

namespace App\Core;

abstract class BaseMiddleware
{
    abstract public function handle(): bool;
}
