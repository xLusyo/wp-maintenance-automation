<?php 

namespace WPMA;

use WPMA\Endpoints\TestConnection;
use WPMA\Settings\Settings;
use WPMA\Endpoints\Updates;

class Init {

    public function install() {
        
        add_action('admin_menu', [Settings::class, 'registerSettingsPage']);
        add_action('admin_init', [Settings::class, 'registerSettings']);
        add_action('rest_api_init', [Updates::class, 'register']);
        add_action('rest_api_init', [TestConnection::class, 'testConnection']);
        
    }
}