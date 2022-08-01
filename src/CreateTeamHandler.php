<?php

declare(strict_types=1);

namespace Redactus;

use function Amp\call;
use function Safe\json_decode;
use function Safe\json_encode;

use Amp\Http\Server\Request;
use Amp\Http\Server\RequestHandler;
use Amp\Http\Server\Response;
use Amp\Http\Status;
use Amp\Promise;
use Generator;

use Monolog\Logger;
use Spatie\DataTransferObject\Exceptions\ValidationException;
use Throwable;

class CreateTeamHandler implements RequestHandler {
	public function __construct(
		private Logger $logger,
		private Teams $teams,
	) {
	}

	public function handleRequest(Request $request): Promise {
		return call(function() use ($request): Generator {
			if ($request->getHeader('content-type') !== 'application/json') {
				return new Response(Status::UNSUPPORTED_MEDIA_TYPE);
			}
			try {
				$body = yield $request->getBody()->buffer();
				$riddle = new RiddleNr(json_decode($body, true));
			} catch (ValidationException $e) {
				$messages = [];
				foreach ($e->validationErrors as $fieldName => $errorsForField) {
					foreach ($errorsForField as $errorMsg) {
						$messages []= "{$fieldName}: " . $errorMsg->message;
					}
				}
				return new Response(
					Status::BAD_REQUEST,
					['Content-type' => 'application/json'],
					(new Error(error: join("\n", $messages)))->toJSON()
				);
			} catch (Throwable) {
				return new Response(Status::BAD_REQUEST);
			}
			$this->logger->info('{ip} requests new team', [
				'ip' => $request->getClient()->getRemoteAddress(),
			]);
			$teamId = $this->teams->createTeam($riddle->nr);
			$response = new Response(
				Status::OK,
				[
					'Content-type' => 'application/json',
					'Access-Control-Allow-Origin' => '*',
				],
				json_encode(['team-id' => $teamId, 'nr' => $riddle->nr])
			);
			return $response;
		});
	}
}
