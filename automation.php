<?php


/*
 * Plugin Name: WP Maintenance Automation
 * Description: Enables external web apps to manage core, plugin, and theme updates.
 * Version: 1.0
 * Author: Christian Baloncio
 * Requires PHP: 8.2
 * Text Domain: wpma 
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}


require_once __DIR__ . '/vendor/autoload.php';

$plugin = new WPMA\Init();
$plugin->install();

 