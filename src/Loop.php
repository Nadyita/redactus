<?php

declare(strict_types=1);

namespace Redactus;

use Amp\Http\Server\HttpServer;
use Amp\Http\Server\Router;
use Amp\Loop as AmpLoop;
use Amp\Promise;
use Amp\Socket\Server as SocketServer;
use Amp\Websocket\Server\Websocket;
use Monolog\Logger;

class Loop {
	public function __construct(
		private Logger $logger
	) {
	}

	/** @return Promise<null> */
	public function run(): Promise {
		$teams = new Teams($this->logger->withName('teams'));
		AmpLoop::repeat(60_000, fn() => $teams->cleanup());
		$websocket = new Websocket(new Server($this->logger->withName('websocket'), $teams));
		$teamHandler = new CreateTeamHandler($this->logger->withName('http'), $teams);

		$sockets = [
			SocketServer::listen('0.0.0.0:1337'),
			SocketServer::listen('[::0]:1337'),
		];

		$router = new Router();
		$router->addRoute('POST', '/team', $teamHandler);
		$router->addRoute('GET', '/team/{team}', $websocket);

		$server = new HttpServer($sockets, $router, $this->logger);

		return $server->start();
	}
}
