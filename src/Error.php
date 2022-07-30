<?php

declare(strict_types=1);

namespace Redactus;

/**
 * @psalm-suppress PossiblyUnusedProperty
 * @psalm-suppress PropertyNotSetInConstructor
 */
class Error extends Packet {
	#[FixedString('error')]
	public string $type = 'error';

	public string $error;
}
