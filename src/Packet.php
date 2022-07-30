<?php

declare(strict_types=1);

namespace Redactus;

use function Safe\json_encode;

use Spatie\DataTransferObject\DataTransferObject;

/**
 * @psalm-suppress PossiblyUnusedProperty
 * @psalm-suppress PropertyNotSetInConstructor
 */
class Packet extends DataTransferObject {
	public string $type;

	public function toJSON(): string {
		return json_encode($this->toArray());
	}
}
