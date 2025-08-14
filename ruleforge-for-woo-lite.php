<?php
/**
 * Plugin Name: RuleForge for Woo Lite – Fees & Discounts
 * Description: Lite version: up to 3 rules. Build simple fees/discounts by subtotal, product, role, country, gateway, day-of-week, or first order. Upgrade for unlimited rules, import/export, and more.
 * Version: 0.9.0
 * Requires PHP: 7.4
 * Requires at least: 6.3
 * WC tested up to: 9.0
 * Author: Your Company
 * License: GPLv2 or later
 * Text Domain: ruleforge-lite
 *
 * @package ruleforge-lite
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

// Autoloader.
spl_autoload_register(
	function ( $class ) {
		if ( strpos( $class, 'RuleForgeLite\\' ) !== 0 ) {
			return;
		}

		$path = str_replace( 'RuleForgeLite\\', '', $class );
		$path = str_replace( '\\', '/', $path );

		$parts = explode( '/', $path );
		$file  = 'class-' . strtolower( array_pop( $parts ) ) . '.php';
		$path  = __DIR__ . '/src/' . implode( '/', $parts ) . ( $parts ? '/' : '' ) . $file;

		if ( file_exists( $path ) ) {
			require_once $path;
		}
	}
);

add_action(
	'plugins_loaded',
	function () {
		if ( ! class_exists( 'WooCommerce' ) ) {
			return;
		}
		\RuleForgeLite\Plugin::init( __FILE__ );
	}
);
