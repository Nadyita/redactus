<?php

declare(strict_types=1);

namespace Redactus;

use Attribute;
use Spatie\DataTransferObject\Validation\ValidationResult;
use Spatie\DataTransferObject\Validator;

#[Attribute(Attribute::TARGET_PROPERTY | Attribute::IS_REPEATABLE)]

class FixedString implements Validator {
	public function __construct(
		private string $string,
	) {
	}

	public function validate(mixed $value): ValidationResult {
		if ($value !== $this->string) {
			return ValidationResult::invalid("Value must be equal to {$this->string}");
		}

		return ValidationResult::valid();
	}
}
