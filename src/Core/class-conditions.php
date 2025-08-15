<?php
/**
 * Conditions.
 *
 * @package ruleforge-lite
 */

namespace RuleForgeLite\Core;

/**
 * Conditions class.
 */
class Conditions {

	/**
	 * Match all conditions.
	 *
	 * @param array $conds The conditions.
	 * @param array $ctx The context.
	 *
	 * @return boolean
	 */
	public static function match( array $conds, array $ctx ): bool {
		foreach ( $conds as $c ) {
			if ( ! self::one( $c, $ctx ) ) {
				return false;
			}
		}
		return true;
	}

	/**
	 * Match one condition.
	 *
	 * @param array $c The condition.
	 * @param array $ctx The context.
	 *
	 * @return boolean
	 */
	protected static function one( array $c, array $ctx ): bool {
		$type = $c['type'] ?? '';
		switch ( $type ) {
			case 'subtotal':
				return self::cmp( $ctx['subtotal'], $c['op'] ?? '>=', (float) ( $c['value'] ?? 0 ) );
			case 'product_in_cart':
				return self::in_cart( $ctx, (array) ( $c['ids'] ?? array() ) );
			case 'user_role':
				return count( array_intersect( (array) ( $c['in'] ?? array() ), $ctx['user_roles'] ) ) > 0;
			case 'geo_country':
				return in_array( $ctx['country'], (array) ( $c['in'] ?? array() ), true );
			case 'payment_gateway':
				return in_array( $ctx['gateway'], (array) ( $c['in'] ?? array() ), true );
			case 'day_of_week':
				return in_array( (int) $ctx['dow'], array_map( 'intval', (array) ( $c['in'] ?? array() ) ), true );
			case 'first_order':
				return (bool) ( $c['value'] ?? true ) === (bool) $ctx['first_order'];
			default:
				return false;
		}
	}

	/**
	 * Compare two values.
	 *
	 * @param mixed  $a The first value.
	 * @param string $op The operator.
	 * @param mixed  $b The second value.
	 *
	 * @return boolean
	 */
	protected static function cmp( $a, $op, $b ): bool {
		if ( '>=' === $op ) {
			return $a >= $b;
		}
		if ( '>' === $op ) {
			return $a > $b;
		}
		if ( '<=' === $op ) {
			return $a <= $b;
		}
		if ( '<' === $op ) {
			return $a < $b;
		}
		if ( '==' === $op ) {
			return $a === $b;
		}
		if ( '!=' === $op ) {
			return $a !== $b;
		}
		return false;
	}

	/**
	 * Check if product is in cart.
	 *
	 * @param array $ctx The context.
	 * @param array $ids The product ids.
	 *
	 * @return boolean
	 */
	protected static function in_cart( array $ctx, array $ids ): bool {
		$ids = array_map( 'intval', $ids );
		return count( array_intersect( $ids, $ctx['product_ids'] ) ) > 0;
	}
}
