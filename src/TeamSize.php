<?php

declare(strict_types=1);

namespace Redactus;

/**
 * @psalm-suppress PossiblyUnusedProperty
 * @psalm-suppress PropertyNotSetInConstructor
 */
class TeamSize extends Packet {
	#[FixedString('team-size')]
	public string $type = 'team-size';

	public int $size;
}
