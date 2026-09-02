<?php
/**
 * Helpers Class
 *
 * @package StoreStackAttributeSwatchesForWooCommerce
 */

declare(strict_types=1);

namespace StoreStackAttributeSwatchesForWooCommerce;

defined( 'ABSPATH' ) || exit;

/**
 * Class Helpers
 *
 * Helper functions for the plugin.
 */
class Helpers {

	/**
	 * Get a WooCommerce attribute object based on the provided taxonomy name.
	 *
	 * @param string $taxonomy_name Taxonomy name.
	 * @return \stdClass|null
	 */
	public static function get_attribute( string $taxonomy_name ): \stdClass|null {
		$attribute_id = wc_attribute_taxonomy_id_by_name( $taxonomy_name );
		$attribute    = wc_get_attribute( $attribute_id );

		return $attribute;
	}
}
