<?php

declare(strict_types=1);

namespace Redactus;

use Monolog\Logger;

class Teams {
	/** @var array<string,Team> */
	private array $teams = [];

	public function __construct(
		private Logger $logger
	) {
	}

	public function createTeam(): string {
		$team = new Team();
		$this->teams[$team->getId()] = $team;
		$this->logger->info('New team created: {team}', ['team' => $team->getId()]);
		return $team->getId();
	}

	public function hasTeam(string $team): bool {
		return \array_key_exists($team, $this->teams);
	}

	public function getTeam(string $team): Team {
		return $this->teams[$team];
	}

	public function cleanup(): void {
		$now = time();
		$this->logger->debug('Team cleanup started');
		foreach ($this->teams as $teamId => $team) {
			if ($team->getSize() > 0) {
				continue;
			}
			if ($now - $team->getLastActivity() > 3600 * 24) {
				$this->logger->info('Team {team} deleted', ['team' => $teamId]);
				unset($this->teams[$teamId]);
			}
		}
		$this->logger->debug('Team cleanup done');
	}
}
