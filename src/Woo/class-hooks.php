<?php
/**
 * WooCommerce hooks.
 *
 * @package ruleforge-for-woo-lite
 */

namespace RuleForgeLite\Woo;

use RuleForgeLite\Core\Actions;
use RuleForgeLite\Core\Conditions;
use RuleForgeLite\Core\Context;
use RuleForgeLite\Core\Repository;

/**
 * Integrates with WooCommerce hooks.
 */
class Hooks {

	/**
	 * Initialize hooks.
	 */
	public static function init(): void {
		add_action( 'woocommerce_cart_calculate_fees', array( __CLASS__, 'apply_rules' ), 900, 1 );
	}

	/**
	 * Apply rules to the cart.
	 *
	 * @param \WC_Cart $cart The cart object.
	 */
	public static function apply_rules( \WC_Cart $cart ): void {
		$ctx     = Context::from_cart( $cart );
		$applied = array();
		foreach ( Repository::active_rules() as $rule ) {
			if ( ! Conditions::match( $rule['conditions'], $ctx ) ) {
				continue;
			}
			$acts    = Actions::normalize( $rule['actions'], $ctx );
			$applied = array_merge( $applied, $acts );
		}
		$fees = Actions::to_fees( $applied, $ctx );
		foreach ( $fees as $fee ) {
			$cart->add_fee( $fee['label'], $fee['amount'], $fee['taxable'] );
		}
	}
}
