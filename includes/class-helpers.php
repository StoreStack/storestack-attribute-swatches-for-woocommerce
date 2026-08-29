<?php

/**
 * Helpers Class
 * 
 * @package StoreStackAttributeSwatchesForWooCommerce
 * @since 1.0.0
 */

declare(strict_types=1);

namespace StoreStackAttributeSwatchesForWooCommerce;

defined('ABSPATH') || exit;

class Helpers
{
    /**
     * Get a WooCommerce attribute object based on the provided taxonomy name.
     */
    public static function get_attribute(string $taxonomy_name): \stdClass|null
    {
        $attribute_id = wc_attribute_taxonomy_id_by_name($taxonomy_name);
        $attribute = wc_get_attribute($attribute_id);

        return $attribute;
    }
}
