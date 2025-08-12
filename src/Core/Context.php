<?php
namespace RuleForgeLite\Core;

class Context {
    public static function fromCart(\WC_Cart $cart): array {
        $items = $cart->get_cart();
        $product_ids = [];
        foreach ($items as $it){
            $product_ids[] = (int) ($it['product_id'] ?? 0);
            if (!empty($it['variation_id'])) $product_ids[] = (int)$it['variation_id'];
        }
        $user = wp_get_current_user();
        $roles = (array) ($user ? $user->roles : []);
        $country = function_exists('WC') ? WC()->customer->get_billing_country() : '';
        $gateway = function_exists('WC') && WC()->session ? (WC()->session->get('chosen_payment_method') ?: '') : '';
        return [
            'subtotal'=> (float) $cart->get_subtotal(),
            'product_ids'=> array_values(array_unique(array_filter($product_ids))),
            'user_roles'=> $roles,
            'country'=> $country,
            'gateway'=> $gateway,
            'dow'=> (int) current_time('w'),
            'first_order'=> self::isFirstOrder(),
        ];
    }
    protected static function isFirstOrder(): bool {
        $uid = get_current_user_id(); if (!$uid) return false;
        $orders = wc_get_orders(['customer_id'=>$uid, 'limit'=>1, 'status'=>['wc-completed','wc-processing','wc-on-hold']]);
        return empty($orders);
    }
}
