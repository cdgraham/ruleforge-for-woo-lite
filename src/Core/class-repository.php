<?php
/**
 * Handles the custom post type and rule storage.
 *
 * @package ruleforge-for-woo-lite
 */

namespace RuleForgeLite\Core;

/**
 * Manages the 'fee_rule' custom post type and retrieves rules.
 */
class Repository {

	/**
	 * Initialize hooks.
	 */
	public static function init(): void {
		add_action( 'init', array( __CLASS__, 'register_cpt' ) );
		add_action( 'admin_init', array( __CLASS__, 'enforce_lite_limit' ) );
	}

	/**
	 * Register the 'fee_rule' custom post type.
	 */
	public static function register_cpt(): void {
		register_post_type(
			'fee_rule',
			array(
				'label'        => 'Fee Rules',
				'public'       => false,
				'show_ui'      => true,
				'show_in_menu' => false,
				'supports'     => array( 'title' ),
			)
		);
	}

	/**
	 * Enforce the lite limit of 3 active rules.
	 */
	public static function enforce_lite_limit(): void {
		$q = get_posts(
			array(
				'post_type'   => 'fee_rule',
				'post_status' => 'publish',
				'numberposts' => -1,
			)
		);

		$enabled = array();
		foreach ( $q as $p ) {
			if ( get_post_meta( $p->ID, '_rf_enabled', true ) ) {
				$enabled[] = $p->ID;
			}
		}

		if ( count( $enabled ) > 3 ) {
			$to_disable = array_slice( $enabled, 3 );
			foreach ( $to_disable as $pid ) {
				update_post_meta( $pid, '_rf_enabled', 0 );
			}
			add_action(
				'admin_notices',
				function () {
					echo '<div class="notice notice-warning"><p>' .
						wp_kses_post( __( 'RuleForge Lite allows up to 3 active rules. Extra rules were disabled. <a class="button button-primary" href="https://example.com/ruleforge" target="_blank">Upgrade to Pro</a>', 'ruleforge-lite' ) ) .
						'</p></div>';
				}
			);
		}
	}

	/**
	 * Get active rules.
	 *
	 * @return array The active rules.
	 */
	public static function active_rules(): array {
		$q = get_posts(
			array(
				'post_type'   => 'fee_rule',
				'post_status' => 'publish',
				'numberposts' => -1,
				'meta_key'    => '_rf_priority',
				'orderby'     => 'meta_value_num',
				'order'       => 'ASC',
			)
		);

		$out = array();
		foreach ( $q as $p ) {
			if ( ! get_post_meta( $p->ID, '_rf_enabled', true ) ) {
				continue;
			}

			$priority   = get_post_meta( $p->ID, '_rf_priority', true );
			$conditions = get_post_meta( $p->ID, '_rf_conditions', true );
			$actions    = get_post_meta( $p->ID, '_rf_actions', true );

			$out[] = array(
				'id'         => $p->ID,
				'priority'   => (int) ( ! empty( $priority ) ? $priority : 10 ),
				'conditions' => json_decode( ! empty( $conditions ) ? (string) $conditions : '[]', true ),
				'actions'    => json_decode( ! empty( $actions ) ? (string) $actions : '[]', true ),
			);
		}
		return $out;
	}
}
