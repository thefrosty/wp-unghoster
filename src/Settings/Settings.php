<?php declare(strict_types=1);

namespace TheFrosty\WpUnghoster\Settings;

use Dwnload\WpSettingsApi\Api\Options;
use Dwnload\WpSettingsApi\Api\PluginSettings;
use Dwnload\WpSettingsApi\Api\SettingField;
use Dwnload\WpSettingsApi\Api\SettingSection;
use Dwnload\WpSettingsApi\Settings\FieldManager;
use Dwnload\WpSettingsApi\Settings\FieldTypes;
use Dwnload\WpSettingsApi\Settings\SectionManager;
use Dwnload\WpSettingsApi\SettingsApiFactory;
use Dwnload\WpSettingsApi\WpSettingsApi;
use TheFrosty\WpUtilities\Plugin\AbstractHookProvider;
use TheFrosty\WpUtilities\Plugin\Plugin;
use function get_plugin_data;
use function sanitize_text_field;

/**
 * Class Settings
 * @package TheFrosty\WpUnghoster\Settings
 */
class Settings extends AbstractHookProvider
{

    public const SECTION = 'unghoster';
    public const FIELD_ACCOUNT_ID = 'account_id';
    public const FIELD_ENABLE_FOR_USER = 'enable_for_user';
    private const PREFIX = 'unghoster_';

    /**
     * Create the PluginSettings object.
     * @param Plugin $plugin
     * @return PluginSettings
     */
    public static function factory(Plugin $plugin): PluginSettings
    {
        return SettingsApiFactory::create([
            'domain' => $plugin->getSlug(),
            'file' => __FILE__, // Path to WpSettingsApi file.
            'menu-slug' => $plugin->getSlug(),
            'menu-title' => 'Unghoster', // Title found in menu
            'page-title' => 'Unghoster Settings', // Title output at top of settings page
            'prefix' => self::PREFIX,
            'version' => get_plugin_data($plugin->getFile(), false, false)['Version'],
        ]);
    }

    /**
     * Returns the enabled setting.
     * @return bool
     */
    public static function isEnabled(): bool
    {
        return !empty(self::getAccountId());
    }

    /**
     * Returns the account id field.
     * @return string
     */
    public static function getAccountId(): string
    {
        return sanitize_text_field(Options::getOption(self::FIELD_ACCOUNT_ID, self::SECTION));
    }

    /**
     * Returns the enable for WordPress user (logged-in) field.
     * @return string
     */
    public static function getEnableForUser(): string
    {
        return sanitize_text_field(Options::getOption(self::FIELD_ENABLE_FOR_USER, self::SECTION));
    }

    /**
     * Register our callback to the WP Settings API action hook
     * `WpSettingsApi::ACTION_PREFIX . 'init'`. This custom action passes three parameters (two prior to version 2.7)
     * so you have to register a priority and the parameter count.
     */
    public function addHooks(): void
    {
        $this->addAction(WpSettingsApi::HOOK_INIT, [$this, 'init'], 10, 3);
        $this->addFilter('plugin_action_links_' . $this->getPlugin()->getBasename(), [$this, 'addSettingsLink']);
    }

    /**
     * Initiate our setting to the Section & Field Manager classes.
     * @param SectionManager $section_manager
     * @param FieldManager $field_manager
     * @param WpSettingsApi $wp_settings_api
     * @see SettingField for additional options for each field passed to the output
     */
    protected function init(
        SectionManager $section_manager,
        FieldManager $field_manager,
        WpSettingsApi $wp_settings_api
    ): void {
        if ($wp_settings_api->getPluginInfo()->getMenuSlug() !== $this->getPlugin()->getSlug()) {
            return;
        }

        $section_id = $section_manager->addSection(
            new SettingSection([
                SettingSection::SECTION_ID => self::SECTION, // Unique section ID
                SettingSection::SECTION_TITLE => 'Unghoster Settings',
            ])
        );

        $field_manager->addField(
            new SettingField([
                SettingField::NAME => self::FIELD_ACCOUNT_ID,
                SettingField::LABEL => \esc_html__('Unghoster ID', 'wp-unghoster'),
                SettingField::DESC => \esc_html__('Your domain account ID.', 'wp-unghoster'),
                SettingField::TYPE => FieldTypes::FIELD_TYPE_TEXT,
                SettingField::SECTION_ID => $section_id,
            ])
        );

        $field_manager->addField(
            new SettingField([
                SettingField::NAME => self::FIELD_ENABLE_FOR_USER,
                SettingField::LABEL => \esc_html__('Enable for users?', 'wp-unghoster'),
                SettingField::DESC => \esc_html__('Your domain account ID.', 'wp-unghoster'),
                SettingField::TYPE => FieldTypes::FIELD_TYPE_CHECKBOX,
                SettingField::DEFAULT => 'on',
                SettingField::SECTION_ID => $section_id,
            ])
        );
    }

    /**
     * Add settings page link to the plugins page.
     * @param array $actions
     * @return array
     */
    protected function addSettingsLink(array $actions): array
    {
        \array_unshift(
            $actions,
            \sprintf(
                '<a href="%s" aria-label="%s">%s</a>',
                \menu_page_url($this->getPlugin()->getSlug(), false),
                \esc_attr__('Settings for Unghoster', 'wp-unghoster'),
                \esc_html__('Settings', 'default')
            ),
        );

        return $actions;
    }
}
