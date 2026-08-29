<?php

/**
 * Plugin Name:        StoreStack Attribute Swatches for WooCommerce
 * Plugin URI:         https://github.com/StoreStack/storestack-attribute-swatches-for-woocommerce
 * Description:        This plugin allows you to add color, image, button and radio swatches to WooCommerce attributes, so you can easily manage and display them on your product pages.
 * Version:            1.0.1
 * Author:             StoreStack
 * Author URI:         https://github.com/StoreStack
 * License:            GPLv3 or later
 * License URI:        https://www.gnu.org/licenses/gpl-3.0.html
 * Text Domain:        storestack-attribute-swatches-for-woocommerce
 * Requires at least:  6.2
 * Tested up to:       7.1
 * Requires Plugins:   woocommerce
 * WC tested up to:    11.0
 * Requires PHP:       8.2
 */

declare(strict_types=1);

namespace StoreStackAttributeSwatchesForWooCommerce;

defined('ABSPATH') || exit;

class Loader
{
    /**
     * Singleton instance
     */
    private static ?self $instance = null;

    /**
     * Constructor
     */
    public function __construct()
    {
        add_action('before_woocommerce_init', [$this, 'declare_wc_support']);
        add_action('plugins_loaded', [$this, 'init']);
        add_action('admin_enqueue_scripts', [$this, 'enqueue_admin_scripts']);
        add_action('wp_enqueue_scripts', [$this, 'enqueue_frontend_scripts']);
    }

    /**
     * Run the plugin loader by returning the singleton instance
     */
    public static function run(): Loader
    {
        if (empty(self::$instance)) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    /**
     * Define plugin constants
     */
    private function define_constants(): void
    {
        define('SSASFW_PLUGIN_VERSION', '1.0.1');
        define('SSASFW_PLUGIN_PATH', plugin_dir_path(__FILE__));
        define('SSASFW_PLUGIN_URL', plugin_dir_url(__FILE__));
    }

    /**
     * Load required classes
     */
    private function load_classes(): void
    {
        $includes_dir = SSASFW_PLUGIN_PATH . 'includes/';

        require_once $includes_dir . 'class-abstract-base.php';

        require_once $includes_dir . 'class-helpers.php';

        require_once $includes_dir . 'class-swatches.php';
        new Swatches();

        require_once $includes_dir . 'class-options.php';
        new Options();

        require_once $includes_dir . 'class-groups.php';
        new Groups();
    }

    /**
     * Initialize the plugin
     */
    public function init(): void
    {
        $this->define_constants();
        $this->load_classes();

        $installed_version = get_option('ssasfw_attribute_swatches_plugin_version');

        if ($installed_version !== SSASFW_PLUGIN_VERSION) {
            update_option('ssasfw_attribute_swatches_plugin_version', SSASFW_PLUGIN_VERSION);
        }
    }

    /**
     * Enqueue admin scripts and styles
     */
    public function enqueue_admin_scripts(): void
    {
        wp_enqueue_media();
        wp_enqueue_script('storestack-attribute-swatches-for-woocommerce-admin', SSASFW_PLUGIN_URL . 'assets/js/admin.js', array('jquery', 'wp-color-picker'), SSASFW_PLUGIN_VERSION, true);
        wp_localize_script('storestack-attribute-swatches-for-woocommerce-admin', 'ssasfwAdminParams', [
            'placeholderImg' => wc_placeholder_img_src('thumbnail'),
            'groupsPlaceholder' => esc_js(__('Select or type groups', 'storestack-attribute-swatches-for-woocommerce')),
        ]);
    }

    /**
     * Enqueue frontend scripts and styles
     */
    public function enqueue_frontend_scripts(): void
    {
        wp_enqueue_style('storestack-attribute-swatches-for-woocommerce', SSASFW_PLUGIN_URL . 'assets/css/frontend.css', array(), SSASFW_PLUGIN_VERSION);
        wp_enqueue_script('storestack-attribute-swatches-for-woocommerce', SSASFW_PLUGIN_URL . 'assets/js/frontend.js', array('jquery'), SSASFW_PLUGIN_VERSION, true);
    }

    /**
     * Declare compatibility with WooCommerce HPOS custom order tables
     */
    public function declare_wc_support(): void
    {
        if (class_exists(\Automattic\WooCommerce\Utilities\FeaturesUtil::class)) {
            \Automattic\WooCommerce\Utilities\FeaturesUtil::declare_compatibility('custom_order_tables', __FILE__, true);
        }
    }
}


/**
 * Run the plugin
 */
Loader::run();
