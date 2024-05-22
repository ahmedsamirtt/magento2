<?php

namespace TwentyToo\TextSearch\Logger;

use Monolog\Handler\StreamHandler;
use Monolog\Logger;

class Handler extends StreamHandler
{
    public function __construct()
    {
        parent::__construct(BP . '/var/log/twentytoo_textsearch.log', Logger::DEBUG);
    }
}
