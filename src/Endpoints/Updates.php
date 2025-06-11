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

        include_once ABSPATH . 'wp-admin/includes/update.php';

        wp_version_check();
        wp_update_plugins();
        wp_update_themes();

        $updates = [
            'core' => [],
            'plugins' => [],
            'themes' => []
        ];

        $core = get_site_transient('update_core');
        if( ! empty($core->updates) ) {
            foreach( $core->updates as $item ) {
                if($item->response === 'upgrade') {
                    $updates['core'][] = [
                        'current' => get_bloginfo('version'),
                        'new' => $item->current,
                        'package' => $item->package
                    ];
                }
            }
        }

        $plugins = get_site_transient('update_plugins');
        if ( ! empty($plugins->response) ) {
            foreach ($plugins->response as $file => $data) {
                $updates['plugins'][] = [
                    'slug'    => $file,
                    'current' => $data->old_version ?? '',
                    'new'     => $data->new_version,
                    'name'    => $data->slug ?? '',
                ];
            }
        }

        $themes = get_site_transient('update_themes');
        if (!empty($themes->response)) {
            foreach ($themes->response as $slug => $data) {
                $updates['themes'][] = [
                    'slug'    => $slug,
                    'current' => $data['new_version'],
                    'new'     => $data['new_version'],
                ];
            }
        }

        return rest_ensure_response([
            'success' => true,
            'updates' => $updates,
        ]);
    }
}