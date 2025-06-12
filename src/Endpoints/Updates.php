<?php

namespace WPMA\Endpoints;

class Updates {

    public static function register () {

        register_rest_route(
            route_namespace: 'wp-maintenance/v1', 
            route: '/updates', 
            args: [
                'methods' => 'GET',
                'callback' => [self::class, 'handler'],
                'permission_callback' => [self::class, 'authenticate']
            ]
        );
    }

    public static function authenticate (\WP_REST_Request $request) {

        $request_key = $request->get_header('X-WPMA-API-KEY');
        $internal_key = get_option('wpma_setting_key');

        return !empty($request_key) && $request_key === $internal_key;
    }

    public static function handler () {

        include_once ABSPATH . 'wp-admin/includes/plugin.php';
        include_once ABSPATH . 'wp-admin/includes/theme.php';
        include_once ABSPATH . 'wp-admin/includes/update.php';

        wp_version_check();
        wp_update_plugins();
        wp_update_themes();

        $updates = [
            'core' => self::getCoreInfo(),
            'plugins' => self::getPluginsInfo(),
            'themes' => self::getThemesInfo()
        ];

        return rest_ensure_response([
            'success' => true,
            'updates' => $updates
        ]);
    }

    private static function getCoreInfo () : array {
        $current_version = get_bloginfo('version');
        $core_update = get_site_transient('update_core');

        $data = [
            'current_version' => $current_version,
            'new_version' => null,
            'upgradeable' => false,
            'required_php' => null,
            'requrired_wp' => null,
            'reason' => null
        ];

        if( ! empty($core_update->updates) ) {
            foreach($core_update->updates as $update) {
                if($update->response === 'upgrade') {
                    $data['new_version'] = $update->current;
                    $data['upgradable'] = true;
                    $data['requires_php'] = $update->php_version ?? null;
                    $data['requires_wp'] = $update->version ?? null;
                }

                if ($update->php_version && version_compare(PHP_VERSION, $update->php_version, '<')) {
                    $data['upgradable'] = false;
                    $data['reason'] = "Requires PHP {$update->php_version}, current is " . PHP_VERSION;
                }
            }
        }

        return $data;
    }

    private static function getPluginsInfo(): array {
        $all_plugins = get_plugins();
        $plugin_updates = get_site_transient('update_plugins');

        $results = [];

        foreach ($all_plugins as $plugin_file => $plugin_data) {
            $update_data = $plugin_updates->response[$plugin_file] ?? null;
            $no_update_data = $plugin_updates->no_update[$plugin_file] ?? null;

            $requires_php = $update_data->requires_php ?? $no_update_data->requires_php ?? null;
            $requires_wp = $update_data->requires ?? $no_update_data->requires ?? null;

            $is_upgradable = $update_data ? true : false;
            $reason = null;

            if ($requires_php && version_compare(PHP_VERSION, $requires_php, '<')) {
                $is_upgradable = false;
                $reason = "Requires PHP {$requires_php}, current is " . PHP_VERSION;
            }

            $results[] = [
                'slug'         => $plugin_data['TextDomain'],
                'file'         => $plugin_file,
                'name'         => $plugin_data['Name'],
                'version'      => $plugin_data['Version'],
                'new_version'  => $update_data->new_version ?? null,
                'requires_php' => $requires_php,
                'requires_wp'  => $requires_wp,
                'upgradable'   => $is_upgradable,
                'reason'       => $reason,
                'is_active'    => is_plugin_active($plugin_file),
            ];
        }

        return $results;
    }
    private static function getThemesInfo(): array {
        $themes = wp_get_themes();
        $theme_updates = get_site_transient('update_themes');

        $current_theme_slug = get_stylesheet(); // active theme slug

        $results = [];

        foreach ($themes as $slug => $theme) {
            $update_data = $theme_updates->response[$slug] ?? null;

            $requires_php = $update_data['requires_php'] ?? null;
            $requires_wp = $update_data['requires'] ?? null;

            $is_upgradable = $update_data ? true : false;
            $reason = null;

            if ($requires_php && version_compare(PHP_VERSION, $requires_php, '<')) {
                $is_upgradable = false;
                $reason = "Requires PHP {$requires_php}, current is " . PHP_VERSION;
            }

            $results[] = [
                'slug'         => $slug,
                'name'         => $theme->get('Name'),
                'version'      => $theme->get('Version'),
                'new_version'  => $update_data['new_version'] ?? null,
                'requires_php' => $requires_php,
                'requires_wp'  => $requires_wp,
                'upgradable'   => $is_upgradable,
                'reason'       => $reason,
                'is_active'    => $slug === $current_theme_slug,
            ];
        }

        return $results;
    }

}