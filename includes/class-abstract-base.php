<?php

/**
 * AbstractBase Class
 * 
 * @package StoreStackAttributeSwatchesForWooCommerce
 * @since 1.0.0
 */

declare(strict_types=1);

namespace StoreStackAttributeSwatchesForWooCommerce;

defined('ABSPATH') || exit;


abstract class AbstractBase
{
    /**
     * Constructor
     */
    public function __construct()
    {
        if (is_admin() && !wp_doing_ajax()) {
            $this->initialize_admin();
        } else {
            $this->initialize_frontend();
        }
    }

    /**
     * Initialize admin hooks - override in child classes
     */
    protected function initialize_admin(): void
    {
        // Override in child classes
    }

    /**
     * Initialize frontend hooks - override in child classes
     */
    protected function initialize_frontend(): void
    {
        // Override in child classes
    }
}
