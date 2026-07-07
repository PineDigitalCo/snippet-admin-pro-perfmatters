<?php

declare(strict_types=1);

namespace Perfmatters\PMCS;

class PMCS {
	/**
	 * @return array<string, mixed>
	 */
	public static function get_snippet_config( bool $cached = true ): array {
		return [];
	}
}

class Snippet {
	/**
	 * @return array{meta: array<string, mixed>, code: string}|null
	 */
	public static function get( string $file_name ) {
		return null;
	}

	/**
	 * @param array<string, mixed> $meta
	 */
	public static function update( string $file_name, string $code, array $meta ) {
		return false;
	}
}

class Conditions {
	/**
	 * @param array<string, mixed>|mixed $conditions
	 */
	public static function evaluate( $conditions ): bool {
		return true;
	}
}
