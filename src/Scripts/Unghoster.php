<?php

declare(strict_types=1);

namespace TheFrosty\WpUnghoster\Scripts;

use TheFrosty\WpUnghoster\Settings\Settings;
use TheFrosty\WpUtilities\Plugin\AbstractContainerProvider;
use function is_user_logged_in;
use function TheFrosty\WpUtilities\wp_enqueue_script;
use function TheFrosty\WpUtilities\wp_register_script;
use function wp_add_inline_script;

/**
 * Class Unghoster
 * @package TheFrosty\WpUnghoster
 * @psalm-suppress PropertyNotSetInConstructor
 */
class Unghoster extends AbstractContainerProvider
{

    final public const HANDLE_UNGHOSTER = 'unghoster';

    /**
     * Add class hooks.
     */
    public function addHooks(): void
    {
        $this->addFilter('wp_enqueue_scripts', [$this, 'enqueueScripts']);
    }

    /**
     * Enqueue our wp_add_inline_script script.
     */
    protected function enqueueScripts(): void
    {
        if (!Settings::isEnabled() || (Settings::getEnableForUser() && is_user_logged_in())) {
            return;
        }

        wp_register_script(handle: self::HANDLE_UNGHOSTER, src: '', ver: null, args: ['in_footer' => false]);
        wp_enqueue_script(self::HANDLE_UNGHOSTER);
        wp_add_inline_script(self::HANDLE_UNGHOSTER, $this->addInlineScript());
    }

    /**
     * Add our inline script.
     * @return string
     */
    private function addInlineScript(): string
    {
        $account_id = Settings::getAccountId();
        return <<<JS
!function(i,o,r){i[r]&&i[r].isLoaded||(i[r]=i[r]||function(){(i[r].q=i[r].q||[]).push(arguments)},i[r].init=function(n,e){var t;i[r].loading||(i[r].loading=!0,(script=o.createElement("script")).type="text/javascript",script.src="https://cdn.unghoster.com/unghoster.js",script.async=!0,script.onload=function()
{for(i[r].loaded=!0,i[r].loading=!1,i[r].load&&i[r].load(n,e);i[r].q&&i[r].q.length;){var t=i[r].q.shift();i[r].apply(null,t)}},(t=o.getElementsByTagName("script")[0]).parentNode.insertBefore(script,t))})}
(window,document,"unghoster"),unghoster.init("$account_id");
JS;
    }
}
