<?php

declare(strict_types=1);

namespace Redactus;

use function Amp\call;
use function Safe\json_encode;

use Amp\Promise;
use Amp\Websocket\Client;
use Generator;

class Team {
	/** @var Guess[] */
	public array $guesses = [];

	/** @var Client[] */
	private array $clients = [];

	private string $id;

	private int $lastActivity;

	public function __construct() {
		$this->lastActivity = time();
		$this->id = gmp_strval(
			gmp_init(
				(string)mt_rand(100, 999)
				. (string)(int)floor(microtime(true) * 1000000),
				10
			),
			62
		);
	}

	public function getId(): string {
		return $this->id;
	}

	public function getLastActivity(): int {
		return $this->lastActivity;
	}

	/** @return Promise<void> */
	public function join(Client $client): Promise {
		$this->lastActivity = time();
		return call(function() use ($client): Generator {
			$this->clients []= $client;
			foreach ($this->clients as $teamMember) {
				yield $teamMember->send((new TeamSize(size: count($this->clients)))->toJSON());
			}
		});
	}

	/** @return Promise<void> */
	public function leave(Client $client): Promise {
		$this->lastActivity = time();
		return call(function() use ($client): Generator {
			$clients = [];
			foreach ($this->clients as $test) {
				if ($test !== $client) {
					$clients []= $test;
				}
			}
			foreach ($clients as $teamMember) {
				yield $teamMember->send((new TeamSize(size: count($clients)))->toJSON());
			}
			$this->clients = $clients;
		});
	}

	public function hasWord(string $word, int $nr): bool {
		foreach ($this->guesses as $guess) {
			if ($guess->number === $nr && strtolower($guess->word) === strtolower($word)) {
				return true;
			}
		}
		return false;
	}

	/** @return Promise<void> */
	public function guessWord(Client $client, Guess $guess): Promise {
		$this->lastActivity = time();
		return call(function() use ($client, $guess): Generator {
			if ($this->hasWord($guess->word, $guess->number)) {
				return;
			}
			$this->guesses []= $guess;
			foreach ($this->clients as $teamMember) {
				if ($client === $teamMember) {
					continue;
				}
				yield $teamMember->send($guess->toJSON());
			}
		});
	}

	/** @return Promise<void> */
	public function sendGuesses(client $client): Promise {
		return call(function() use ($client): Generator {
			foreach ($this->guesses as $guess) {
				yield $client->send(json_encode($guess));
			}
		});
	}

	public function getSize(): int {
		return count($this->clients);
	}
}
