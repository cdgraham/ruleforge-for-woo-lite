<?php
namespace RuleForgeLite\Core;

class Actions {
    public static function normalize(array $actions, array $ctx): array {
        $out=[];
        foreach ($actions as $a){
            $type = $a['type'] ?? '';
            switch ($type){
                case 'discount_percent':
                    $out.append(['kind'=>'discount','amount'=> - round($ctx['subtotal'] * ((float)$a['value']/100), 2), 'label'=>$a['label'] ?? 'Discount']);
                    break;
                case 'fee_percent':
                    $val = round($ctx['subtotal'] * ((float)$a['value']/100), 2);
                    if (isset($a['cap'])) $val = min($val, (float)$a['cap']);
                    $out.append(['kind'=>'fee','amount'=>$val,'label'=>$a['label'] ?? 'Fee']);
                    break;
                case 'fee_fixed':
                    $out.append(['kind'=>'fee','amount'=> (float)($a['value'] ?? 0),'label'=>$a['label'] ?? 'Fee']);
                    break;
            }
        }
        return $out;
    }
    public static function toFees(array $applied, array $ctx): array {
        $fees=[];
        foreach ($applied as $a){
            $amount = (float)$a['amount']; if ($amount == 0.0) continue;
            $fees[] = ['label'=>$a['label'], 'amount'=>$amount, 'taxable'=>false];
        }
        return $fees;
    }
}
