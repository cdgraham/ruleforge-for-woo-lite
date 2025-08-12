<?php
namespace RuleForgeLite\Woo;

use RuleForgeLite\Core\Repository;
use RuleForgeLite\Core\Context;
use RuleForgeLite\Core\Conditions;
use RuleForgeLite\Core\Actions;

class Hooks {
    public static function init(): void {
        add_action('woocommerce_cart_calculate_fees', [__CLASS__,'applyRules'], 900, 1);
    }
    public static function applyRules(\WC_Cart $cart): void {
        $ctx = Context::fromCart($cart);
        $applied = [];
        foreach (Repository::activeRules() as $rule){
            if (!Conditions::match($rule['conditions'],$ctx)) continue;
            $acts = Actions::normalize($rule['actions'],$ctx);
            $applied = array_merge($applied, $acts);
        }
        $fees = Actions::toFees($applied,$ctx);
        foreach ($fees as $fee){ $cart->add_fee($fee['label'], $fee['amount'], $fee['taxable']); }
    }
}
