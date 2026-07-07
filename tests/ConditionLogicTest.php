<?php
/**
 * ConditionLogic tests.
 *
 * @package SnippetAdminProForPerfmatters
 */

declare(strict_types=1);

namespace SnippetAdminProForPerfmatters\Tests;

use PHPUnit\Framework\TestCase;
use SnippetAdminProForPerfmatters\Perfmatters\ConditionLogic;

final class ConditionLogicTest extends TestCase {

	public function test_default_config_matches_perfmatters_native(): void {
		$config = ConditionLogic::default_config();

		$this->assertSame( 'or', $config['include_logic'] );
		$this->assertSame( 'or', $config['exclude_logic'] );
		$this->assertSame( 'and', $config['section_logic'] );
	}

	public function test_extract_config_from_sentinel_row(): void {
		$conditions = [
			'include' => [
				[ 'rule' => 'general:front_page' ],
			],
			'exclude' => [
				[
					'rule'   => ConditionLogic::SENTINEL_RULE,
					'object' => (string) json_encode(
						[
							'include_logic' => 'and',
							'section_logic' => 'or',
						],
						JSON_THROW_ON_ERROR
					),
				],
			],
		];

		$config = ConditionLogic::extract_config( $conditions );

		$this->assertIsArray( $config );
		$this->assertSame( 'and', $config['include_logic'] );
		$this->assertSame( 'or', $config['section_logic'] );
	}

	public function test_strip_sentinel_removes_logic_row(): void {
		$conditions = [
			'exclude' => [
				[ 'rule' => 'general:404' ],
				[
					'rule'   => ConditionLogic::SENTINEL_RULE,
					'object' => '{}',
				],
			],
		];

		$stripped = ConditionLogic::strip_sentinel( $conditions );

		$this->assertCount( 1, $stripped['exclude'] );
		$this->assertSame( 'general:404', $stripped['exclude'][0]['rule'] );
	}

	public function test_build_sentinel_row_encodes_json(): void {
		$row = ConditionLogic::build_sentinel_row(
			[
				'include_logic' => 'and',
				'section_logic' => 'or',
			]
		);

		$this->assertSame( ConditionLogic::SENTINEL_RULE, $row['rule'] );
		$this->assertStringContainsString( '"include_logic":"and"', $row['object'] );
	}
}
