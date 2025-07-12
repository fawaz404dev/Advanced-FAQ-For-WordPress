<?php
/**
 * Advanced FAQ for WordPress - Uninstall Script
 * 
 * This file is executed when the plugin is uninstalled.
 * It cleans up all plugin data from the database.
 * 
 * Author: فوزي جمعة
 * Website: fjomah.com
 */

// If uninstall not called from WordPress, then exit
if (!defined('WP_UNINSTALL_PLUGIN')) {
    exit;
}

// Check if user has permission to uninstall plugins
if (!current_user_can('activate_plugins')) {
    exit;
}

// Get WordPress database connection
global $wpdb;

// Define table name
$table_name = $wpdb->prefix . 'advanced_faq';

// Drop the FAQ table
$wpdb->query("DROP TABLE IF EXISTS $table_name");

// Delete plugin options
delete_option('afaq_settings');

// Delete all post meta related to FAQ
$wpdb->delete(
    $wpdb->postmeta,
    array('meta_key' => '_afaq_show_faq'),
    array('%s')
);
$wpdb->delete(
    $wpdb->postmeta,
    array('meta_key' => '_afaq_type'),
    array('%s')
);
$wpdb->delete(
    $wpdb->postmeta,
    array('meta_key' => '_afaq_custom_faqs'),
    array('%s')
);

// Clear any cached data
wp_cache_flush();

// Optional: Log the uninstall (for debugging purposes)
if (defined('WP_DEBUG') && WP_DEBUG) {
    error_log('Advanced FAQ for WordPress: Plugin uninstalled and data cleaned up.');
}

?>