<?php


if (!defined('WP_UNINSTALL_PLUGIN')) {
    exit;
}

delete_option('wpma_api_key');

if (is_multisite()) {
    global $wpdb;

    $blog_ids = $wpdb->get_col("SELECT blog_id FROM $wpdb->blogs");
    foreach ($blog_ids as $blog_id) {
        switch_to_blog($blog_id);
        delete_option('wpma_api_key');
        restore_current_blog();
    }
}