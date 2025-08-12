<?php
namespace RuleForgeLite\Core;

class Conditions {
    public static function match(array $conds, array $ctx): bool {
        foreach ($conds as $c){ if (!self::one($c,$ctx)) return false; }
        return true;
    }
    protected static function one(array $c, array $ctx): bool {
        $type = $c['type'] ?? '';
        switch ($type){
            case 'subtotal': return self::cmp($ctx['subtotal'], $c['op'] ?? '>=', (float)($c['value'] ?? 0));
            case 'product_in_cart': return self::inCart($ctx, (array)($c['ids'] ?? []));
            case 'user_role': return count(array_intersect((array)($c['in'] ?? []), $ctx['user_roles'])) > 0;
            case 'geo_country': return in_array($ctx['country'], (array)($c['in'] ?? []), true);
            case 'payment_gateway': return in_array($ctx['gateway'], (array)($c['in'] ?? []), true);
            case 'day_of_week': return in_array((int)$ctx['dow'], array_map('intval', (array)($c['in'] ?? [])), true);
            case 'first_order': return (bool)$ctx['first_order'] === (bool)($c['value'] ?? true);
            default: return false;
        }
    }
    protected static function cmp($a,$op,$b): bool {
        return $op === '>=' ? $a >= $b : ($op === '>' ? $a > $b : ($op === '<=' ? $a <= $b : ($op === '<' ? $a < $b : ($op === '==' ? $a == $b : ($op === '!=' ? $a != $b : false)))));
    }
    protected static function inCart(array $ctx, array $ids): bool {
        $ids = array_map('intval',$ids);
        return count(array_intersect($ids, $ctx['product_ids'])) > 0;
    }
}
