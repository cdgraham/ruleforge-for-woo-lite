<?php
namespace RuleForgeLite\Admin;

class Menu {
    public static function upgrade_notice();
    public static function init(): void {
        add_action('admin_menu',[__CLASS__,'register']);
        add_action('admin_notices',[__CLASS__,'upgrade_notice']);
        add_action('wp_ajax_ruleforge_lite_dismiss_upgrade', function(){ check_ajax_referer('rf_lite_ajax'); update_user_meta(get_current_user_id(),'ruleforge_lite_dismiss_upgrade',1); wp_die('ok'); });
        add_action('admin_enqueue_scripts',[__CLASS__,'enqueue']);
        add_action('add_meta_boxes',[__CLASS__,'boxes']);
        add_action('save_post_fee_rule',[__CLASS__,'save']);
    }
    public static function register(): void {
        add_submenu_page('woocommerce','RuleForge Lite Rules','RuleForge Lite','manage_woocommerce','ruleforge-lite',[__CLASS__,'render']);
    }
    public static function enqueue($hook): void {
        if ($hook !== 'post.php' && $hook !== 'post-new.php') return;
        $screen = get_current_screen();
        if ($screen && $screen->post_type==='fee_rule'){
            wp_enqueue_style('ruleforge-lite', plugins_url('assets/css/admin.css', dirname(__FILE__,2)), [], '0.9.0');
            wp_enqueue_script('ruleforge-lite', plugins_url('assets/js/editor.js', dirname(__FILE__,2)), ['jquery'], '0.9.0', true);
        }
    }
    public static function boxes(): void {
        add_meta_box('rf-lite-conditions','Conditions',[__CLASS__,'box_conditions'],'fee_rule','normal','high');
        add_meta_box('rf-lite-actions','Actions',[__CLASS__,'box_actions'],'fee_rule','normal','default');
        add_meta_box('rf-lite-settings','Rule Settings',[__CLASS__,'box_settings'],'fee_rule','side','default');
    }
    public static function render(): void {
        echo '<div class="wrap"><h1>RuleForge for Woo Lite</h1><p>Create up to 3 active rules. <a href="https://example.com/ruleforge" target="_blank">Upgrade to Pro</a> for unlimited rules, import/export, and analytics.</p></div>';
    }
    public static function box_conditions($post): void {
        $conds = get_post_meta($post->ID,'_rf_conditions',true);
        echo '<textarea id="rf-conditions" name="_rf_conditions" rows="8" class="large-text" placeholder='[{"type":"subtotal","op":">=","value":100}]'>'.esc_textarea($conds).'</textarea>';
        echo '<p><button type="button" class="button" id="rf-add-cond">Add subtotal ≥ 100</button></p>';
    }
    public static function box_actions($post): void {
        $acts = get_post_meta($post->ID,'_rf_actions',true);
        echo '<textarea id="rf-actions" name="_rf_actions" rows="6" class="large-text" placeholder='[{"type":"fee_percent","value":2,"label":"Surcharge"}]'>'.
              esc_textarea($acts).'</textarea>';
        echo '<p><button type="button" class="button" id="rf-add-action">Add 2% surcharge</button></p>';
    }
    public static function box_settings($post): void {
        $enabled = (bool) get_post_meta($post->ID,'_rf_enabled',true);
        $priority = (int) (get_post_meta($post->ID,'_rf_priority',true) ?: 10);
        echo '<p><label><input type="checkbox" name="_rf_enabled" value="1" '.checked($enabled,true,false).'> Enabled</label></p>';
        echo '<p><label>Priority<br/><input type="number" name="_rf_priority" value="'.esc_attr($priority).'" class="small-text"></label></p>';
        echo '<p class="description"><a href="https://example.com/ruleforge" target="_blank">Upgrade to Pro</a> for import/export, preview, stacking controls, and more.</p>';
    }
    public static function save($post_id): void {
        if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) return;
        if (!current_user_can('manage_woocommerce',$post_id)) return;
        update_post_meta($post_id,'_rf_enabled', isset($_POST['_rf_enabled']) ? 1:0);
        if (isset($_POST['_rf_priority'])) update_post_meta($post_id,'_rf_priority', intval($_POST['_rf_priority']));
        if (isset($_POST['_rf_conditions'])) update_post_meta($post_id,'_rf_conditions', wp_kses_post(stripslashes($_POST['_rf_conditions'])));
        if (isset($_POST['_rf_actions'])) update_post_meta($post_id,'_rf_actions', wp_kses_post(stripslashes($_POST['_rf_actions'])));
    
public static function upgrade_notice() {
    if (!current_user_can('manage_woocommerce')) return;
    $screen = get_current_screen();
    if (!$screen || $screen->id !== 'woocommerce_page_ruleforge-lite') return;
    $dismissed = get_user_meta(get_current_user_id(), 'ruleforge_lite_dismiss_upgrade', true);
    if ($dismissed) return;
    $url = esc_url('https://example.com/ruleforge');
    echo '<div class="notice notice-info is-dismissible ruleforge-lite-upgrade"><p><strong>Need more than 3 rules?</strong> Upgrade to RuleForge Pro for unlimited rules, import/export, recipe presets, and analytics. <a class="button button-primary" target="_blank" href="'.$url.'">Upgrade to Pro</a></p></div>';
    echo '<script>jQuery(function($){$(document).on("click",".ruleforge-lite-upgrade .notice-dismiss",function(){jQuery.post(ajaxurl,{action:"ruleforge_lite_dismiss_upgrade",_ajax_nonce:"'+wp_create_nonce('rf_lite_ajax')+'"});});});</script>';
}

}
