<?php

namespace WPMA\Settings;

class Settings {

    public static function registerSettingsPage () {

        add_options_page(
            page_title: 'WordPress Maintence Automation Settings',
            menu_title: 'WPMA',
            capability: 'manage_options',
            menu_slug: 'wpma-settings',
            callback: [self::class, 'renderSettingsPage']
        );
    }

    public static function registerSettings(){

        register_setting(
            option_group: 'wpma_settings_group', 
            option_name: 'wpma_setting_key'
        );
    }

    public static function renderSettingsPage() {
        ?>
        <div class="wrap">
            <h1>WordPress Maintence Automation Key</h1>
            <form method="post" action="options.php">
                <?php
                settings_fields('wpma_settings_group');
                do_settings_sections('wpma-settings');
                ?> 
                <label for="wpma_setting_key">API Key: </label>
                <input type="text" name="wpma_setting_key" value="<?php echo esc_attr(get_option('wpma_setting_key', '')); ?>" />
                <?php
                submit_button();
                ?>
            </form>
        </div>
        <?php
    }
}