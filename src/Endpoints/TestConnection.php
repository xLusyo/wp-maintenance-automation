<?php

namespace WPMA\Endpoints;

class TestConnection {

   

    public static function testConnection () {
        register_rest_route(
            route_namespace: 'wp-maintenance/v1', 
            route: '/test-connection', 
            args: [
                'methods' => 'GET',
                'callback' => [self::class, 'handler'],
                'permission_callback' => [self::class, 'authenticate']
            ]
        );
    }

    public static function authenticate (\WP_REST_Request $request) {
        $provided_key = $request->get_header('X-WPMA-API-KEY');
        $internal_key = get_option('wpma_setting_key');

        return !empty($provided_key) && hash_equals($internal_key, $provided_key);
    }

    public static function handler () {
        return rest_ensure_response(['success' => true, 'message' => 'Authenticated']);
    }
}