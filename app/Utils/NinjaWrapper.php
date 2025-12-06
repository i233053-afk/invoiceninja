<?php

namespace App\Utils;

class NinjaWrapper
{
    public function isHosted(): bool
    {
        return Ninja::isHosted();
    }

    public function isSelfHost(): bool
    {
        return Ninja::isSelfHost();
    }
}

