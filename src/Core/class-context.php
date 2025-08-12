<?php
/**
 * Builds the context for rule evaluation.
 *
 * @package ruleforge-for-woo-lite
 */

namespace RuleForgeLite\Core;

/**
 * Creates a context object from the current cart.
 */
class Context {

	/**
	 * Create a context from a cart.
	 *
	 * @param \WC_Cart $cart The cart object.
	 * @return array The context.
	 */
	public static function from_cart( \WC_Cart $cart ): array {
		$items       = $cart->get_cart();
		$product_ids = array();
		foreach ( $items as $it ) {
			$product_ids[] = (int) ( $it['product_id'] ?? 0 );
			if ( ! empty( $it['variation_id'] ) ) {
				$product_ids[] = (int) $it['variation_id'];
			}
		}

		$user  = wp_get_current_user();
		$roles = array();
		if ( $user ) {
			$roles = (array) $user->roles;
		}

		$country = '';
		if ( function_exists( 'WC' ) ) {
			$country = WC()->customer->get_billing_country();
		}

		$gateway = '';
		if ( function_exists( 'WC' ) && WC()->session ) {
			$gateway = WC()->session->get( 'chosen_payment_method' );
			if ( empty( $gateway ) ) {
				$gateway = '';
			}
		}

		return array(
			'subtotal'    => (float) $cart->get_subtotal(),
			'product_ids' => array_values( array_unique( array_filter( $product_ids ) ) ),
			'user_roles'  => $roles,
			'country'     => $country,
			'gateway'     => $gateway,
			'dow'         => (int) current_time( 'w' ),
			'first_order' => self::is_first_order(),
		);
	}

	/**
	 * Check if this is the user's first order.
	 *
	 * @return bool Whether this is the first order.
	 */
	protected static function is_first_order(): bool {
		$uid = get_current_user_id();
		if ( ! $uid ) {
			return false;
		}
		$orders = wc_get_orders(
			array(
				'customer_id' => $uid,
				'limit'       => 1,
				'status'      => array( 'wc-completed', 'wc-processing', 'wc-on-hold' ),
			)
		);
		return empty( $orders );
	}
}
