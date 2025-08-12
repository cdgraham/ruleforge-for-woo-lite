<?php
/**
 * Main plugin class.
 *
 * @package ruleforge-for-woo-lite
 */

namespace RuleForgeLite;

/**
 * Initializes the plugin.
 */
class Plugin {

	/**
	 * Initialize the plugin.
	 *
	 * @param string $main_file The main plugin file path.
	 */
	public static function init( $main_file ): void {
		load_plugin_textdomain( 'ruleforge-lite', false, dirname( plugin_basename( $main_file ) ) . '/languages' );
		Admin\Menu::init();
		Core\Repository::init();
		Woo\Hooks::init();
		// The Rest API is not part of the lite version.
	}
}
