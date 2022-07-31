<?php

declare(strict_types=1);

namespace Redactus;

use Amp\Http\Server\Request;
use Amp\Http\Server\RequestHandler;
use Amp\Http\Server\Response;
use Amp\Http\Status;
use Amp\Promise;
use Amp\Success;
use Monolog\Logger;

class CreateTeamHandler implements RequestHandler {
	public function __construct(
		private Logger $logger,
		private Teams $teams,
	) {
	}

	public function handleRequest(Request $request): Promise {
		$this->logger->info('{ip} requests new team', [
			'ip' => $request->getClient()->getRemoteAddress(),
		]);
		$teamId = $this->teams->createTeam();
		$response = new Response(
			Status::OK,
			[
				'Content-type' => 'application/json',
				'Access-Control-Allow-Origin' => '*',
			],
			"{\"team-id\": \"{$teamId}\"}"
		);
		return new Success($response);
	}
}
