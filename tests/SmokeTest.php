<?php

declare(strict_types=1);

namespace SnippetAdminProForPerfmatters\Tests;

use PHPUnit\Framework\TestCase;

/**
 * Smoke test for autoload and scaffold.
 */
final class SmokeTest extends TestCase {

	public function test_plugin_class_is_autoloaded(): void {
		$this->assertTrue( class_exists( \SnippetAdminProForPerfmatters\Plugin::class ) );
	}

	public function test_integration_classes_are_autoloaded(): void {
		$this->assertTrue( class_exists( \SnippetAdminProForPerfmatters\Integration\PerfmattersSnippetsIntegration::class ) );
		$this->assertTrue( class_exists( \SnippetAdminProForPerfmatters\Integration\BulkLocationHandler::class ) );
	}
}
