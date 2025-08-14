<?php
/**
 * Actions.
 *
 * @package ruleforge-lite
 */

namespace RuleForgeLite\Core;

/**
 * Actions class.
 */
class Actions {

	/**
	 * Normalize actions.
	 *
	 * @param array $actions The actions.
	 * @param array $ctx The context.
	 *
	 * @return array
	 */
	public static function normalize( array $actions, array $ctx ): array {
		$out = array();
		foreach ( $actions as $a ) {
			$type = $a['type'] ?? '';
			switch ( $type ) {
				case 'discount_percent':
					$out[] = array(
						'kind'   => 'discount',
						'amount' => - round( $ctx['subtotal'] * ( (float) $a['value'] / 100 ), 2 ),
						'label'  => $a['label'] ?? 'Discount',
					);
					break;
				case 'fee_percent':
					$val = round( $ctx['subtotal'] * ( (float) $a['value'] / 100 ), 2 );
					if ( isset( $a['cap'] ) ) {
						$val = min( $val, (float) $a['cap'] );
					}
					$out[] = array(
						'kind'   => 'fee',
						'amount' => $val,
						'label'  => $a['label'] ?? 'Fee',
					);
					break;
				case 'fee_fixed':
					$out[] = array(
						'kind'   => 'fee',
						'amount' => (float) ( $a['value'] ?? 0 ),
						'label'  => $a['label'] ?? 'Fee',
					);
					break;
			}
		}
		return $out;
	}

	/**
	 * Convert to fees.
	 *
	 * @param array $applied The applied actions.
	 * @param array $ctx The context.
	 *
	 * @return array
	 */
	public static function to_fees( array $applied, array $ctx ): array {
		$fees = array();
		foreach ( $applied as $a ) {
			$amount = (float) $a['amount'];
			if ( 0.0 === $amount ) {
				continue;
			}
			$fees[] = array(
				'label'   => $a['label'],
				'amount'  => $amount,
				'taxable' => false,
			);
		}
		return $fees;
	}
}
