<?php
/**
 * Options Class
 *
 * @package StoreStackAttributeSwatchesForWooCommerce
 */

declare(strict_types=1);

namespace StoreStackAttributeSwatchesForWooCommerce;

defined( 'ABSPATH' ) || exit;

/**
 * Class Options
 *
 * Handles saving swatch metadata for term options.
 */
class Options extends AbstractBase {

	/**
	 * Initialize admin hooks
	 *
	 * @return void
	 */
	protected function initialize_admin(): void {
		add_action( 'created_term', array( $this, 'on_save_attribute_option' ), 10, 4 );
		add_action( 'edited_term', array( $this, 'on_save_attribute_option' ), 10, 4 );
	}

	/**
	 * Initialize frontend hooks
	 *
	 * @return void
	 */
	protected function initialize_frontend(): void {
		// Fallback for terms created or edited via AJAX requests.
		// (Our logic treats AJAX requests as frontend requests).
		add_action( 'created_term', array( $this, 'on_save_attribute_option' ), 10, 4 );
		add_action( 'edited_term', array( $this, 'on_save_attribute_option' ), 10, 4 );
	}

	/**
	 * Save attribute option metadata on term creation or edit
	 *
	 * @param int                  $term_id          Term ID.
	 * @param int                  $term_taxonomy_id Term taxonomy ID.
	 * @param string               $taxonomy_slug    Taxonomy slug.
	 * @param array<string, mixed> $args             Additional arguments.
	 * @return void
	 */
	public function on_save_attribute_option( int $term_id, int $term_taxonomy_id, string $taxonomy_slug, array $args ): void {
		if ( ! isset( $_POST['ssasfw_nonce'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['ssasfw_nonce'] ) ), 'ssasfw_save_option_meta_action' ) ) {
			return;
		}

		// phpcs:ignore WordPress.WP.Capabilities.Unknown -- WooCommerce custom capability.
		if ( ! current_user_can( 'manage_product_terms' ) ) {
			return;
		}

		$fields = array(
			'ssasfw_swatch_group' => 'sanitize_text_field',
			'ssasfw_swatch_color' => 'sanitize_hex_color',
			'ssasfw_swatch_image' => 'absint',
		);

		foreach ( $fields as $key => $sanitize_callback ) {
			if ( ! isset( $_POST[ $key ] ) ) {
				continue;
			}

			$value = $sanitize_callback( wp_unslash( $_POST[ $key ] ) ); // phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized

			if ( ! empty( $value ) ) {
				update_term_meta( $term_id, $key, $value );
			} else {
				delete_term_meta( $term_id, $key );
			}
		}
	}
}
