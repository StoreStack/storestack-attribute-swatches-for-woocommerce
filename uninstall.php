<?php

declare(strict_types=1);

namespace StoreStackAttributeSwatchesForWooCommerce;

defined('WP_UNINSTALL_PLUGIN') || exit; // Prevent direct access and ensure this runs only on uninstall


// Check if user opted to remove all plugin data
if (get_option('ssasfw_attribute_swatches_remove_data', false)) {
    global $wpdb;

    delete_option('ssasfw_attribute_swatches_remove_data');
    delete_option('ssasfw_attribute_swatches_plugin_version');

    // Remove any additional options matching ssasfw_%
    $wpdb->query("DELETE FROM {$wpdb->options} WHERE option_name LIKE 'ssasfw_%'");
}
