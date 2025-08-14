<?php
/**
 * Plugin main file.
 *
 * @package ruleforge-lite
 */

namespace RuleForgeLite;

/**
 * Plugin class.
 */
class Plugin {

	/**
	 * Init.
	 *
	 * @param string $main_file The main plugin file.
	 */
	public static function init( $main_file ): void {
		load_plugin_textdomain( 'ruleforge-lite', false, dirname( plugin_basename( $main_file ) ) . '/languages' );
		Admin\Menu::init();
		Core\Repository::init();
		Woo\Hooks::init();
	}
}
