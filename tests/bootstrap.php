<?php

/*
 * This file is part of the Disco package.
 *
 * (c) bitExpert AG
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
declare(strict_types=1);

// include and configure Composer autoloader
include __DIR__ . '/../vendor/autoload.php';

// configure the Simple Logging Facade for PSR-3 loggers with a Monolog backend
\bitExpert\Slf4PsrLog\LoggerFactory::registerFactoryCallback(function ($channel) {
    if (!\Monolog\Registry::hasLogger($channel)) {
        \Monolog\Registry::addLogger(new \Monolog\Logger($channel));
    }

    return \Monolog\Registry::getInstance($channel);
});
