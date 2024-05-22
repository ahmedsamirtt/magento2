<?php

namespace TwentyToo\TextSearch\Logger;

use Magento\Framework\Logger\Handler\Base as BaseHandler;
use Monolog\Logger;

class HandlerFactory extends BaseHandler
{
    protected $loggerType = Logger::DEBUG;
    protected $fileName = '/var/log/twentytoo_textsearch.log';
}
