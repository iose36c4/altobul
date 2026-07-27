<?php

namespace App\Exceptions;

use RuntimeException;

class PhotoLimitReachedException extends RuntimeException
{
    public function __construct(int $maxPhotos)
    {
        parent::__construct("Photo limit reached. Maximum {$maxPhotos} photos allowed.");
    }
}
