<?php
namespace RuleForgeLite;

class Plugin {
    public static function init($main_file): void {
        load_plugin_textdomain('ruleforge-lite', false, dirname(plugin_basename($main_file)).'/languages');
        Admin\Menu::init();
        Core\Repository::init();
        Woo\Hooks::init();
        Rest\Routes::init();
    }
}
