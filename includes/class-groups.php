<?php
/**
 * Groups Class
 *
 * @package StoreStackAttributeSwatchesForWooCommerce
 */

declare(strict_types=1);

namespace StoreStackAttributeSwatchesForWooCommerce;

defined( 'ABSPATH' ) || exit;

/**
 * Class Groups
 *
 * Handles swatch groups for attributes.
 */
class Groups extends AbstractBase {

	/**
	 * Initialize admin hooks
	 *
	 * @return void
	 */
	protected function initialize_admin(): void {
		add_action( 'woocommerce_after_add_attribute_fields', array( $this, 'add_groups_field' ) );
		add_action( 'woocommerce_after_edit_attribute_fields', array( $this, 'add_groups_field' ) );

		add_action( 'woocommerce_attribute_added', array( $this, 'save_attribute_groups' ), 10, 2 );
		add_action( 'woocommerce_attribute_updated', array( $this, 'save_attribute_groups' ), 10, 3 );
	}

	/**
	 * Initialize frontend hooks
	 *
	 * @return void
	 */
	protected function initialize_frontend(): void {}

	/**
	 * Add 'Groups' field to attribute add/edit forms
	 *
	 * @return void
	 */
	public function add_groups_field(): void {
		// phpcs:ignore WordPress.Security.NonceVerification.Recommended -- Nonce is verified when form is submitted.
		$attribute_id = absint( $_GET['edit'] ?? 0 );
		$attribute    = wc_get_attribute( $attribute_id );

		// Get existing groups.
		$groups = get_option( "ssasfw_attribute_groups_{$attribute_id}", array() );

		// If an attribute exists, we're on edit page.
		?>
		<?php echo $attribute ? '<tr class="form-field"><th>' : '<div class="form-field">'; ?>
		<label for="ssasfw_groups"><?php esc_html_e( 'Groups', 'storestack-attribute-swatches-for-woocommerce' ); ?></label>
		<?php
		if ( $attribute ) {
			echo '</th><td>';}
		?>
		<select id="ssasfw_groups" name="ssasfw_groups[]" class="ssasfw-select2" multiple="multiple" style="<?php echo $attribute ? '' : 'width: 95%;'; ?>">
			<?php foreach ( $groups as $group ) : ?>
				<option value="<?php echo esc_attr( $group ); ?>" selected><?php echo esc_html( $group ); ?></option>
			<?php endforeach; ?>
		</select>
		<p class="description"><?php esc_html_e( 'Select or type groups.', 'storestack-attribute-swatches-for-woocommerce' ); ?></p>
		<?php wp_nonce_field( 'ssasfw_save_groups_action', 'ssasfw_nonce', false ); ?>
		<?php echo $attribute ? '</td></tr>' : '</div>'; ?>
		<?php
	}

	/**
	 * Save attribute groups on form submission
	 *
	 * @param int                  $attribute_id Attribute ID.
	 * @param array<string, mixed> $data         Attribute data.
	 * @param string|null          $old_slug     Old attribute slug.
	 * @return void
	 */
	public function save_attribute_groups( int $attribute_id, array $data, ?string $old_slug = null ): void {
		if ( ! isset( $_POST['ssasfw_nonce'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['ssasfw_nonce'] ) ), 'ssasfw_save_groups_action' ) ) {
			return;
		}

		// phpcs:ignore WordPress.WP.Capabilities.Unknown -- WooCommerce custom capability.
		if ( ! current_user_can( 'manage_product_terms' ) ) {
			return;
		}

		$old_groups = get_option( "ssasfw_attribute_groups_{$attribute_id}", array() );

		if ( ! empty( $_POST['ssasfw_groups'] ) ) {
			$new_groups = array_map( 'sanitize_text_field', wp_unslash( (array) $_POST['ssasfw_groups'] ) );
			update_option( "ssasfw_attribute_groups_{$attribute_id}", $new_groups );
			$this->cleanup_terms( $attribute_id, $new_groups, $old_groups );
		} else {
			delete_option( "ssasfw_attribute_groups_{$attribute_id}" );
			$this->cleanup_terms( $attribute_id, array(), $old_groups );
		}
	}

	/**
	 * Clean up term meta when groups are updated for an attribute
	 *
	 * @param int                   $attribute_id Attribute ID.
	 * @param array<string, string> $new_groups   New groups array.
	 * @param array<string, string> $old_groups   Old groups array.
	 * @return void
	 */
	private function cleanup_terms( int $attribute_id, array $new_groups, array $old_groups ): void {
		$removed_groups = array_diff( $old_groups, $new_groups );
		if ( empty( $removed_groups ) ) {
			return;
		}

		$terms = get_terms(
			array(
				'taxonomy'   => wc_attribute_taxonomy_name_by_id( $attribute_id ),
				'hide_empty' => false,
				'fields'     => 'ids',
			)
		);

		if ( is_wp_error( $terms ) || empty( $terms ) ) {
			return;
		}

		$remove_all = empty( $new_groups ) && ! empty( $old_groups );

		foreach ( $terms as $term_id ) {
			if ( $remove_all ) {
				delete_term_meta( $term_id, 'ssasfw_swatch_group' );
			} else {
				$group = get_term_meta( $term_id, 'ssasfw_swatch_group', true );
				if ( $group && in_array( $group, $removed_groups, true ) ) {
					delete_term_meta( $term_id, 'ssasfw_swatch_group' );
				}
			}
		}
	}
}
