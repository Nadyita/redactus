<?php

declare(strict_types=1);

namespace Redactus;

use function Amp\asyncCall;
use function Amp\call;
use function Safe\json_decode;

use Amp\Http\Server\Request;
use Amp\Http\Server\Response;
use Amp\Http\Server\Router;
use Amp\Promise;
use Amp\Success;
use Amp\Websocket\Client;
use Amp\Websocket\Message;
use Amp\Websocket\Server\ClientHandler;
use Amp\Websocket\Server\Gateway;
use Generator;
use Monolog\Logger;
use Safe\Exceptions\JsonException;
use Throwable;

class Server implements ClientHandler {
	private const ALLOWED_ORIGINS = [
		'http://localhost:1337',
		'http://127.0.0.1:1337',
		'http://[::1]:1337',
	];

	public function __construct(
		private Logger $logger,
		private Teams $teams,
	) {
	}

	public function handleHandshake(Gateway $gateway, Request $request, Response $response): Promise {
		/*
		$sentOrigin = $request->getHeader('origin') ?? '<unset>';
		if (!\in_array($sentOrigin, self::ALLOWED_ORIGINS, true)) {
			$this->logger->warning('Client from {ip} used wrong origin {origin}', [
				'ip' => $request->getClient()->getRemoteAddress(),
				'origin' => $sentOrigin,
			]);
			return $gateway->getErrorHandler()->handleError(403);
		}
		*/
		$args = $request->getAttribute(Router::class);
		if ($this->teams->hasTeam($args['team']) === false) {
			$this->logger->warning('Client from {ip} wants to join non-existing team {team}', [
				'ip' => $request->getClient()->getRemoteAddress(),
				'team' => $args['team'] ?? '<unset>',
			]);
			return $gateway->getErrorHandler()->handleError(404);
		}

		return new Success($response);
	}

	public function handleClient(Gateway $gateway, Client $client, Request $request, Response $response): Promise {
		return call(function() use ($client, $request): Generator {
			$args = $request->getAttribute(Router::class);
			$team = $this->teams->getTeam($args['team']);
			$this->logger->info('Client {client} joined team {team}', [
				'client' => $client->getRemoteAddress()->getHost(),
				'team' => $team->getId(),
			]);
			yield $team->join($client);

			$client->onClose(function() use ($team, $client): void {
				asyncCall(function() use ($team, $client): Generator {
					yield $team->leave($client);
					$this->logger->info('Client {client} left team {team}', [
						'client' => $client->getRemoteAddress()->getHost(),
						'team' => $team->getId(),
					]);
				});
			});
			yield $team->sendGuesses($client);
			while ($message = yield $client->receive()) {
				yield $this->handleMessage($client, $message, $team);
			}
		});
	}

	/** @return Promise<void> */
	private function handleMessage(Client $client, Message $message, Team $team): Promise {
		return call(function() use ($client, $message, $team): Generator {
			$packet = yield $message->buffer();
			try {
				$guess = new Guess(json_decode($packet, true));
			} catch (JsonException $e) {
				$error = new Error(error: 'Invalid JSON');
				yield $client->send($error->toJSON());
				return;
			} catch (Throwable $e) {
				$error = new Error(error: 'Invalid data');
				yield $client->send($error->toJSON());
				return;
			}
			$this->logger->info('User {name} in team {team} guessed {word} for #{nr}', [
				'name' => $guess->sender,
				'team' => $team->getId(),
				'word' => $guess->word,
				'nr' => $guess->number,
			]);
			yield $team->guessWord($client, $guess);
		});
	}
}
