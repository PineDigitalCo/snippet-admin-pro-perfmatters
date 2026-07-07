<?php

declare(strict_types=1);

namespace SnippetAdminProForPerfmatters\Tests;

use PHPUnit\Framework\TestCase;
use SnippetAdminProForPerfmatters\Perfmatters\SnippetDuplicate;

/**
 * Snippet duplicate naming helpers.
 */
final class SnippetDuplicateTest extends TestCase {

	public function test_display_name_for_empty_source(): void {
		$this->assertSame( 'Snippet Copy', SnippetDuplicate::display_name_for_copy( '' ) );
	}

	public function test_display_name_appends_copy(): void {
		$this->assertSame( 'Header Script Copy', SnippetDuplicate::display_name_for_copy( 'Header Script' ) );
	}

	public function test_display_name_increments_existing_copy(): void {
		$this->assertSame( 'Header Script Copy 2', SnippetDuplicate::display_name_for_copy( 'Header Script Copy' ) );
		$this->assertSame( 'Header Script Copy 3', SnippetDuplicate::display_name_for_copy( 'Header Script Copy 2' ) );
	}

	public function test_file_title_limits_words_and_sanitizes(): void {
		$this->assertSame( 'header-script-copy', SnippetDuplicate::file_title_from_display_name( 'Header Script Copy' ) );
		$this->assertSame(
			'one-two-three-four',
			SnippetDuplicate::file_title_from_display_name( 'One Two Three Four Five Six' )
		);
	}
}
