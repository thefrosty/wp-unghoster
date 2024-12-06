<?php
/**
 * Plugin Name: Unghoster
 * Description: Retrieve person-level contact information including emails and Linkedin for your website visitors. <a href="https://www.unghoster.com/signup?ref=thefrosty" target="_blank">Signup for free</a>.
 * Author: Austin Passy
 * Author URI: https://github.com/thefrosty
 * Version: 0.1.0
 * Requires at least: 6.6
 * Tested up to: 6.7.1
 * Requires PHP: 8.1
 * Plugin URI: https://github.com/thefrosty/wp-unghoster
 * GitHub Plugin URI: https://github.com/thefrosty/wp-unghoster
 * Primary Branch: develop
 * Release Asset: true
 */

namespace TheFrosty\WpUnghoster;

defined('ABSPATH') || exit;

use Dwnload\WpSettingsApi\WpSettingsApi;
use TheFrosty\WpUnghoster\Scripts\Unghoster;
use TheFrosty\WpUnghoster\Settings\Settings;
use TheFrosty\WpUtilities\Plugin\PluginFactory;
use TheFrosty\WpUtilities\WpAdmin\DisablePluginUpdateCheck;
use function add_action;
use function defined;
use function is_admin;
use function is_readable;

if (is_readable(__DIR__ . '/vendor/autoload.php')) {
    include_once __DIR__ . '/vendor/autoload.php';
}

$plugin = PluginFactory::create('unghoster');
$plugin->add(new Unghoster());

if (is_admin()) {
    $plugin
        ->add(new DisablePluginUpdateCheck())
        ->add(new Settings())
        ->add(new WpSettingsApi(Settings::factory($plugin)));
}

add_action('plugins_loaded', static function () use ($plugin): void {
    $plugin->initialize();
});
