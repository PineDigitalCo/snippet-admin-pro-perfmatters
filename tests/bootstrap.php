<?php

declare(strict_types=1);

if ( ! function_exists( '__' ) ) {
	function __( $text, $domain = 'default' ) {
		return $text;
	}
}

if ( ! function_exists( 'esc_html__' ) ) {
	function esc_html__( $text, $domain = 'default' ) {
		return $text;
	}
}

if ( ! function_exists( 'sanitize_title' ) ) {
	function sanitize_title( $title, $fallback_title = '', $context = 'save' ) {
		$title = strtolower( trim( preg_replace( '/[^a-z0-9]+/i', '-', (string) $title ), '-' ) );

		if ( $title === '' ) {
			return (string) $fallback_title;
		}

		return $title;
	}
}

require_once dirname( __DIR__ ) . '/vendor/autoload.php';
