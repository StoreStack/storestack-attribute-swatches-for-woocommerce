<?php

/**
 * Groups Class
 * 
 * @package StoreStackAttributeSwatchesForWooCommerce
 * @since 1.0.0
 */

declare(strict_types=1);

namespace StoreStackAttributeSwatchesForWooCommerce;

defined('ABSPATH') || exit;

class Groups extends AbstractBase
{
    /**
     * Initialize admin hooks
     */
    protected function initialize_admin(): void
    {
        add_action('woocommerce_after_add_attribute_fields', [$this, 'add_groups_field']);
        add_action('woocommerce_after_edit_attribute_fields', [$this, 'add_groups_field']);

        add_action('woocommerce_attribute_added', [$this, 'save_attribute_groups'], 10, 2);
        add_action('woocommerce_attribute_updated', [$this, 'save_attribute_groups'], 10, 3);
    }

    /**
     * Initialize frontend hooks
     */
    protected function initialize_frontend(): void {}

    /**
     * Add 'Groups' field to attribute add/edit forms
     */
    public function add_groups_field(): void
    {
        $attribute_id = absint($_GET['edit'] ?? 0);
        $attribute = wc_get_attribute($attribute_id);

        // Get existing groups
        $groups = get_option("ssasfw_attribute_groups_{$attribute_id}", []);

        // If an attribute exists, we're on edit page
?>
        <?php echo $attribute ? '<tr class="form-field"><th>' : '<div class="form-field">' ?>
        <label for="ssasfw_groups"><?php esc_html_e('Groups', 'storestack-attribute-swatches-for-woocommerce'); ?></label>
        <?php if ($attribute) echo '</th><td>' ?>
        <select id="ssasfw_groups" name="ssasfw_groups[]" class="ssasfw-select2" multiple="multiple" style="<?php echo $attribute ? '' : 'width: 95%;' ?>">
            <?php foreach ($groups as $group): ?>
                <option value="<?php echo esc_attr($group); ?>" selected><?php echo esc_html($group); ?></option>
            <?php endforeach; ?>
        </select>
        <p class="description"><?php esc_html_e('Select or type groups.', 'storestack-attribute-swatches-for-woocommerce'); ?></p>
        <?php wp_nonce_field('ssasfw_save_groups_action', 'ssasfw_nonce', false); ?>
        <?php echo $attribute ? '</td></tr>' : '</div>' ?>
<?php
    }

    /**
     * Save attribute groups on form submission
     * 
     * @param array<string, mixed> $data
     */
    public function save_attribute_groups(int $attribute_id, array $data, ?string $old_slug = null): void
    {
        if (!isset($_POST['ssasfw_nonce']) || !wp_verify_nonce(sanitize_text_field(wp_unslash($_POST['ssasfw_nonce'])), 'ssasfw_save_groups_action')) {
            return;
        }

        if (!current_user_can('manage_product_terms')) {
            return;
        }

        $old_groups = get_option("ssasfw_attribute_groups_{$attribute_id}", []);

        if (!empty($_POST['ssasfw_groups'])) {
            $new_groups = array_map('sanitize_text_field', wp_unslash((array) $_POST['ssasfw_groups']));
            update_option("ssasfw_attribute_groups_{$attribute_id}", $new_groups);
            $this->cleanup_terms($attribute_id, $new_groups, $old_groups);
        } else {
            delete_option("ssasfw_attribute_groups_{$attribute_id}");
            $this->cleanup_terms($attribute_id, [], $old_groups);
        }
    }

    /**
     * Clean up term meta when groups are updated for an attribute
     * 
     * @param array<string, string> $new_groups
     * @param array<string, string> $old_groups
     */
    private function cleanup_terms(int $attribute_id, array $new_groups, array $old_groups): void
    {
        $removed_groups = array_diff($old_groups, $new_groups);
        if (empty($removed_groups)) {
            return;
        }

        $terms = get_terms([
            'taxonomy' => wc_attribute_taxonomy_name_by_id($attribute_id),
            'hide_empty' => false,
            'fields' => 'ids',
        ]);

        if (is_wp_error($terms) || empty($terms)) {
            return;
        }

        $remove_all = empty($new_groups) && !empty($old_groups);

        foreach ($terms as $term_id) {
            if ($remove_all) {
                delete_term_meta($term_id, 'ssasfw_swatch_group');
            } else {
                $group = get_term_meta($term_id, 'ssasfw_swatch_group', true);
                if ($group && in_array($group, $removed_groups, true)) {
                    delete_term_meta($term_id, 'ssasfw_swatch_group');
                }
            }
        }
    }
}
