<?php

namespace App\Repositories\Contracts;

interface GuzzieRepositoryInterface
{
    public function getClient($uri);
}