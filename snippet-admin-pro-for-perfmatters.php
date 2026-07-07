<?php
/**
 * Plugin Name:       Snippet Admin Pro for Perfmatters
 * Plugin URI:        https://github.com/PineDigitalCo/snippet-admin-pro-perfmatters
 * Description:       Enhanced admin tools for managing Perfmatters code snippets.
 * Version:           0.4.0
 * Author:            Pine Digital Co
 * License:           GPL-2.0-or-later
 * License URI:       https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain:       snippet-admin-pro-for-perfmatters
 * Requires at least: 5.8
 * Requires PHP:      8.1
 *
 * @package SnippetAdminProForPerfmatters
 */

declare(strict_types=1);

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! defined( 'SAPFP_VERSION' ) ) {
	define( 'SAPFP_VERSION', '0.4.0' );
}
if ( ! defined( 'SAPFP_FILE' ) ) {
	define( 'SAPFP_FILE', __FILE__ );
}
if ( ! defined( 'SAPFP_DIR' ) ) {
	define( 'SAPFP_DIR', plugin_dir_path( __FILE__ ) );
}
if ( ! defined( 'SAPFP_URL' ) ) {
	define( 'SAPFP_URL', plugin_dir_url( __FILE__ ) );
}

$autoload = SAPFP_DIR . 'vendor/autoload.php';
if ( is_readable( $autoload ) ) {
	require_once $autoload;
} else {
	spl_autoload_register(
		static function ( string $class_name ): void {
			if ( strpos( $class_name, 'SnippetAdminProForPerfmatters\\' ) !== 0 ) {
				return;
			}

			$relative = substr( $class_name, strlen( 'SnippetAdminProForPerfmatters\\' ) );
			$file     = SAPFP_DIR . 'src/' . str_replace( '\\', '/', $relative ) . '.php';
			if ( is_readable( $file ) ) {
				require_once $file;
			}
		}
	);
}

add_action(
	'init',
	static function (): void {
		load_plugin_textdomain(
			'snippet-admin-pro-for-perfmatters',
			false,
			dirname( plugin_basename( SAPFP_FILE ) ) . '/languages'
		);
	},
	0
);

if ( class_exists( \SnippetAdminProForPerfmatters\Support\ConditionsAutoload::class ) ) {
	\SnippetAdminProForPerfmatters\Support\ConditionsAutoload::register();
}

if ( class_exists( \SnippetAdminProForPerfmatters\Plugin::class ) ) {
	\SnippetAdminProForPerfmatters\Plugin::init();
}
