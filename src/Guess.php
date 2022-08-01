<?php

declare(strict_types=1);

namespace Redactus;

/**
 * @psalm-suppress PropertyNotSetInConstructor
 */
class Guess extends Packet {
	#[FixedString('guess')]
	public string $type;

	public string $sender;

	public string $word;
}
