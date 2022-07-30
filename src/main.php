<?php

declare(strict_types=1);

namespace Redactus;

require_once dirname(__DIR__) . '/vendor/autoload.php';

use Amp\Loop;
use Generator;
use Monolog\Formatter\LineFormatter;
use Monolog\Handler\StreamHandler;
use Monolog\Logger;
use Monolog\Processor\PsrLogMessageProcessor;
use Psr\Log\LogLevel;
use Redactus\Loop as MainLoop;

Loop::run(static function(): Generator {
	$logHandler = new StreamHandler(STDOUT, LogLevel::INFO);
	$logHandler->setFormatter(new LineFormatter("[%datetime%] %channel%.%level_name%: %message%\n", 'c', true, true));
	$logger = new Logger('server');
	$logger->pushProcessor(new PsrLogMessageProcessor('c', true));
	$logger->pushHandler($logHandler);

	$mainLoop = new MainLoop($logger);
	yield $mainLoop->run();
});
