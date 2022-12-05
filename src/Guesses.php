<?php

declare(strict_types=1);

namespace Redactus;

/**
 * @psalm-suppress PropertyNotSetInConstructor
 */
class Guesses extends Packet {
	#[FixedString('guesses')]
	public string $type = 'guesses';

	/** @var Guess[] */
	public array $guesses = [];
}
