<?php

declare(strict_types=1);

namespace Redactus;

/**
 * @psalm-suppress PossiblyUnusedProperty
 * @psalm-suppress PropertyNotSetInConstructor
 */
class RiddleNr extends Packet {
	#[FixedString('riddle-nr')]
	public string $type = 'riddle-nr';

	public int $nr;
}
