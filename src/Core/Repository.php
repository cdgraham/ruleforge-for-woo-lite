<?php
namespace RuleForgeLite\Core;

class Repository {
    public static function init(): void {
        add_action('init',[__CLASS__,'registerCPT']);
        add_action('admin_init',[__CLASS__,'enforceLiteLimit']);
    }
    public static function registerCPT(): void {
        register_post_type('fee_rule',[
            'label'=>'Fee Rules',
            'public'=>false,
            'show_ui'=>true,
            'show_in_menu'=>false,
            'supports'=>['title'],
        ]);
    }
    public static function enforceLiteLimit(): void {
        $q = get_posts(['post_type'=>'fee_rule','post_status'=>'publish','numberposts'=>-1]);
        $enabled = [];
        foreach ($q as $p){ if (get_post_meta($p->ID,'_rf_enabled', true)) $enabled[] = $p->ID; }
        if (count($enabled) > 3){
            $to_disable = array_slice($enabled,3);
            foreach ($to_disable as $pid) update_post_meta($pid,'_rf_enabled',0);
            add_action('admin_notices', function(){
                echo '<div class="notice notice-warning"><p>RuleForge Lite allows up to 3 active rules. Extra rules were disabled. <a class="button button-primary" href="https://example.com/ruleforge" target="_blank">Upgrade to Pro</a></p></div>';
            });
        }
    }
    public static function activeRules(): array {
        $q = get_posts(['post_type'=>'fee_rule','post_status'=>'publish','numberposts'=>-1,'meta_key'=>'_rf_priority','orderby'=>'meta_value_num','order'=>'ASC']);
        $out = [];
        foreach ($q as $p){
            if (!get_post_meta($p->ID,'_rf_enabled', true)) continue;
            $out[] = [
                'id'=>$p->ID,
                'priority'=> (int) (get_post_meta($p->ID,'_rf_priority', true) ?: 10),
                'conditions'=> json_decode((string)get_post_meta($p->ID,'_rf_conditions', true) ?: '[]', true),
                'actions'=> json_decode((string)get_post_meta($p->ID,'_rf_actions', true) ?: '[]', true),
            ];
        }
        return $out;
    }
}
