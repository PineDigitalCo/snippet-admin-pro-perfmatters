<?php
/**
 * ConditionsSaveHandler tests.
 *
 * @package SnippetAdminProForPerfmatters
 */

declare(strict_types=1);

namespace SnippetAdminProForPerfmatters\Tests;

use PHPUnit\Framework\TestCase;
use SnippetAdminProForPerfmatters\Integration\ConditionsSaveHandler;
use SnippetAdminProForPerfmatters\Perfmatters\ConditionLogic;

final class ConditionsSaveHandlerTest extends TestCase {

	public function test_default_config_strips_sentinel_without_adding_new_row(): void {
		$conditions = [
			'include' => [
				[ 'rule' => 'general:blog' ],
			],
			'exclude' => [
				[
					'rule'   => ConditionLogic::SENTINEL_RULE,
					'object' => '{"section_logic":"or"}',
				],
			],
		];

		$result = ConditionsSaveHandler::apply_config_to_conditions(
			$conditions,
			ConditionLogic::default_config()
		);

		$this->assertSame( [], $result['exclude'] ?? [] );
	}

	public function test_non_default_config_appends_sentinel_row(): void {
		$conditions = [
			'include' => [
				[ 'rule' => 'general:blog' ],
			],
		];

		$result = ConditionsSaveHandler::apply_config_to_conditions(
			$conditions,
			[
				'include_logic' => 'and',
				'section_logic' => 'or',
			]
		);

		$this->assertCount( 1, $result['exclude'] );
		$this->assertSame( ConditionLogic::SENTINEL_RULE, $result['exclude'][0]['rule'] );
		$this->assertStringContainsString( '"section_logic":"or"', $result['exclude'][0]['object'] );
	}
}
