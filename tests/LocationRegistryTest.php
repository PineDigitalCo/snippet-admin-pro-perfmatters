<?php

declare(strict_types=1);

namespace SnippetAdminProForPerfmatters\Tests;

use PHPUnit\Framework\TestCase;
use SnippetAdminProForPerfmatters\Integration\BulkLocationHandler;
use SnippetAdminProForPerfmatters\Perfmatters\LocationRegistry;
use SnippetAdminProForPerfmatters\Support\BulkLocationRequest;

/**
 * Location registry tests.
 */
final class LocationRegistryTest extends TestCase {

	public function test_php_snippet_supports_everywhere_and_scoped_locations(): void {
		$options = LocationRegistry::get_options_for_type( 'php' );

		$this->assertArrayHasKey( '', $options );
		$this->assertArrayHasKey( 'frontend', $options );
		$this->assertArrayHasKey( 'admin', $options );
		$this->assertArrayNotHasKey( 'wp_head', $options );
	}

	public function test_js_snippet_supports_header_footer_locations(): void {
		$options = LocationRegistry::get_options_for_type( 'js' );

		$this->assertArrayHasKey( 'wp_head', $options );
		$this->assertArrayHasKey( 'wp_footer', $options );
		$this->assertArrayHasKey( 'admin_head', $options );
		$this->assertArrayHasKey( 'admin_footer', $options );
		$this->assertSame( 'Frontend Header', LocationRegistry::get_label( 'wp_head' ) );
	}

	public function test_html_snippet_includes_content_locations(): void {
		$options = LocationRegistry::get_options_for_type( 'html' );

		$this->assertArrayHasKey( 'shortcode', $options );
		$this->assertArrayHasKey( 'before_content', $options );
	}

	public function test_invalid_location_is_rejected_for_type(): void {
		$this->assertFalse( LocationRegistry::is_valid_for_type( 'wp_head', 'php' ) );
		$this->assertTrue( LocationRegistry::is_valid_for_type( 'wp_footer', 'js' ) );
	}

	public function test_options_for_types_use_intersection_not_union(): void {
		$this->assertSame( [], LocationRegistry::get_options_for_types( [ 'php', 'css' ] ) );
		$this->assertArrayHasKey( 'wp_head', LocationRegistry::get_options_for_types( [ 'js', 'css' ] ) );
		$this->assertArrayNotHasKey( 'wp_body_open', LocationRegistry::get_options_for_types( [ 'js', 'css' ] ) );
	}

	public function test_catalog_matches_perfmatters_type_rules(): void {
		$catalog = LocationRegistry::get_catalog_for_js();
		$admin_head = null;

		foreach ( $catalog as $entry ) {
			if ( $entry['value'] === 'admin_head' ) {
				$admin_head = $entry;
				break;
			}
		}

		$this->assertNotNull( $admin_head );
		$this->assertSame( [ 'js', 'css' ], $admin_head['codeTypes'] );
	}

	public function test_display_label_for_empty_location(): void {
		$this->assertSame( 'Everywhere', LocationRegistry::get_display_label( '', 'php' ) );
		$this->assertSame( '--', LocationRegistry::get_display_label( '', 'css' ) );
		$this->assertSame( 'Frontend Footer', LocationRegistry::get_display_label( 'wp_footer', 'js' ) );
	}
}

/**
 * Bulk location request helpers.
 */
final class BulkLocationRequestTest extends TestCase {

	public function test_everywhere_option_round_trip(): void {
		$this->assertSame( '__everywhere__', BulkLocationRequest::location_option_value( '' ) );
		$this->assertSame( '', BulkLocationRequest::normalize_submitted_location( '__everywhere__' ) );
	}

	public function test_named_location_round_trip(): void {
		$this->assertSame( 'wp_footer', BulkLocationRequest::location_option_value( 'wp_footer' ) );
		$this->assertSame( 'wp_footer', BulkLocationRequest::normalize_submitted_location( 'wp_footer' ) );
	}

	public function test_sanitize_snippet_files_filters_invalid_entries(): void {
		$this->assertSame(
			[ '1-global.php', '2-test.php' ],
			BulkLocationRequest::sanitize_snippet_files( [ '1-global.php', '', '../evil.php', '2-test.php' ] )
		);
	}
}

/**
 * Bulk location handler helpers.
 */
final class BulkLocationHandlerTest extends TestCase {

	public function test_sanitize_location_sort(): void {
		$this->assertSame( 'asc', BulkLocationHandler::sanitize_location_sort( 'asc' ) );
		$this->assertSame( 'desc', BulkLocationHandler::sanitize_location_sort( 'desc' ) );
		$this->assertSame( '', BulkLocationHandler::sanitize_location_sort( 'invalid' ) );
	}
}
