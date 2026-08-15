<?php

/**
 * Swatches Class
 * 
 * @package StoreStackAttributeSwatchesForWooCommerce
 * @since 1.0.0
 */

declare(strict_types=1);

namespace StoreStackAttributeSwatchesForWooCommerce;

defined('ABSPATH') || exit;

class Swatches extends AbstractBase
{
    /**
     * Initialize admin hooks
     */
    protected function initialize_admin(): void
    {
        add_filter('product_attributes_type_selector', [$this, 'register_swatch_types']);

        foreach (wc_get_attribute_taxonomies() as $taxonomy_obj) {
            $taxonomy_name = wc_attribute_taxonomy_name($taxonomy_obj->attribute_name);

            add_action("{$taxonomy_name}_add_form_fields", [$this, 'add_swatch_selector']);
            add_action("{$taxonomy_name}_edit_form_fields", [$this, 'add_swatch_selector_to_edit_page'], 10, 2);

            add_action("{$taxonomy_name}_add_form_fields", [$this, 'add_group_selector']);
            add_action("{$taxonomy_name}_edit_form_fields", [$this, 'add_group_selector_to_edit_page'], 10, 2);

            add_filter("manage_edit-{$taxonomy_name}_columns", [$this, 'add_swatch_column']);
            add_filter("manage_{$taxonomy_name}_custom_column", [$this, 'add_swatch_column_content'], 10, 3);

            add_filter("manage_edit-{$taxonomy_name}_columns", [$this, 'add_group_column']);
            add_filter("manage_{$taxonomy_name}_custom_column", [$this, 'add_group_column_content'], 10, 3);
        }
    }

    /**
     * Initialize frontend hooks
     */
    protected function initialize_frontend(): void
    {
        add_filter('woocommerce_dropdown_variation_attribute_options_html', [$this, 'attribute_options_html'], 10, 2);
        add_action('woocommerce_after_variations_table', [$this, 'tooltip_html']);
    }


    //********************
    // ADMIN FUNCTIONS
    //********************

    /**
     * Register custom swatch types for WooCommerce attributes.
     * 
     * @param array $types Existing attribute types
     * @return array Merged array with custom swatch types added
     */
    public function register_swatch_types(array $types): array
    {
        // Only add custom types on attribute edit pages, not product edit pages
        $screen = get_current_screen();
        if ($screen && $screen->id === 'product') {
            return $types;
        }

        return array_merge($types, [
            'color'  => __('Color', 'storestack-attribute-swatches-for-woocommerce'),
            'image'  => __('Image', 'storestack-attribute-swatches-for-woocommerce'),
            'button' => __('Button', 'storestack-attribute-swatches-for-woocommerce'),
            'radio'  => __('Radio', 'storestack-attribute-swatches-for-woocommerce')
        ]);
    }

    /**
     * Add swatch selector to add option (term) page
     */
    public function add_swatch_selector(string $taxonomy_name): void
    {
        $attribute = Helpers::get_attribute($taxonomy_name);

        if (!$attribute) {
            return;
        }

        match ($attribute->type) {
            'color' => $this->render_color_picker(),
            'image' => $this->render_image_picker(),
            default => null
        };
    }

    /**
     * Add swatch selector to edit option (term) page
     */
    public function add_swatch_selector_to_edit_page(\WP_Term $term, string $taxonomy_slug): void
    {
        $attribute = Helpers::get_attribute($taxonomy_slug);

        if (!$attribute) {
            return;
        }

        match ($attribute->type) {
            'color' => $this->render_color_picker($term->term_id, true),
            'image' => $this->render_image_picker($term->term_id, true),
            default => null
        };
    }

    /**
     * Render color picker field
     */
    public function render_color_picker(?int $term_id = null, bool $is_option_edit_page = false): void
    {
        $color = $term_id ? get_term_meta($term_id, 'ssasfw_swatch_color', true) : false;
?>
        <?php echo $is_option_edit_page ? '<tr class="form-field"><th>' : '<div class="form-field">' ?>
        <label for="ssasfw_swatch_color" style="margin-bottom: 8px;"><?php esc_html_e('Color', 'storestack-attribute-swatches-for-woocommerce'); ?></label>
        <?php if ($is_option_edit_page) echo '</th><td>' ?>
        <input type="text" name="ssasfw_swatch_color" id="ssasfw_swatch_color" class="color-picker" value="<?php if ($color) echo esc_attr($color); ?>" style="display: none;" />
        <?php wp_nonce_field('ssasfw_save_option_meta_action', 'ssasfw_nonce', false); ?>
        <?php echo $is_option_edit_page ? '</td></tr>' : '</div>' ?>
    <?php
    }

    /**
     * Render image picker field
     */
    public function render_image_picker(?int $term_id = null, bool $is_option_edit_page = false): void
    {
        $img_id = $term_id ? get_term_meta($term_id, 'ssasfw_swatch_image', true) : false;
        $img_url = $img_id ? wp_get_attachment_image_url($img_id, 'thumbnail') : wc_placeholder_img_src('thumbnail');
    ?>
        <?php echo $is_option_edit_page ? '<tr class="form-field"><th>' : '<div class="form-field">' ?>
        <label for="ssasfw_swatch_image" style="margin-bottom: 8px;"><?php esc_html_e('Image', 'storestack-attribute-swatches-for-woocommerce'); ?></label>
        <?php if ($is_option_edit_page) echo '</th><td>' ?>
        <img src="<?php echo esc_url($img_url); ?>" class="swatch-image-preview" style="max-width: 80px; max-height: 80px; border:1px solid #ddd; border-radius:4px;" />
        <button type="button" class="button swatch-image-upload-button"><?php esc_html_e('Add Image', 'storestack-attribute-swatches-for-woocommerce'); ?></button>
        <button type="button" class="button swatch-image-remove-button"><?php esc_html_e('Remove Image', 'storestack-attribute-swatches-for-woocommerce'); ?></button>
        <input type="hidden" id="ssasfw_swatch_image" name="ssasfw_swatch_image" value="<?php if ($img_id) echo esc_attr($img_id); ?>" />
        <?php wp_nonce_field('ssasfw_save_option_meta_action', 'ssasfw_nonce', false); ?>
        <?php echo $is_option_edit_page ? '</td></tr>' : '</div>' ?>
    <?php
    }

    /**
     * Add swatch column header to attribute terms table
     */
    public function add_swatch_column(array $columns): array
    {
        $taxonomy = sanitize_text_field(wp_unslash($_GET['taxonomy'] ?? ''));
        $attribute = Helpers::get_attribute($taxonomy);

        if (!$attribute || !in_array($attribute->type, ['color', 'image'])) {
            return $columns;
        }

        $ordered_columns = [];

        foreach ($columns as $key => $value) {
            if ($key === 'description') {
                $type = $attribute->type === 'color' ? __('color', 'storestack-attribute-swatches-for-woocommerce') : __('image', 'storestack-attribute-swatches-for-woocommerce');
                $ordered_columns[$attribute->type] = $type;
            }
            $ordered_columns[$key] = $value;
        }

        return $ordered_columns;
    }

    /**
     * Add swatch column content to attribute terms table
     */
    public function add_swatch_column_content(string $content, string $column_name, int $term_id): string
    {
        if ($column_name === 'color') {
            $color = get_term_meta($term_id, 'ssasfw_swatch_color', true);
            return '<span style="display:inline-block; width:35px; height:35px; background-color:' . esc_attr($color) . '; border:1px solid #ddd; border-radius:4px;"></span>';
        }

        if ($column_name === 'image') {
            $img_id = get_term_meta($term_id, 'ssasfw_swatch_image', true);
            return wp_get_attachment_image($img_id, 'thumbnail', false, ['style' => 'width:35px; height:35px; border:1px solid #ddd; border-radius:4px;']);
        }

        return $content;
    }

    /**
     * Add 'group' column header to attribute terms table
     */
    public function add_group_column(array $columns): array
    {
        $taxonomy = sanitize_text_field(wp_unslash($_GET['taxonomy'] ?? ''));
        $attribute = Helpers::get_attribute($taxonomy);

        if (!$attribute) {
            return $columns;
        }

        $groups = get_option("ssasfw_attribute_groups_{$attribute->id}", []);

        if (empty($groups)) {
            return $columns;
        }

        $ordered_columns = [];

        foreach ($columns as $key => $value) {
            if ($key === 'description') {
                $ordered_columns['group'] = __('Group', 'storestack-attribute-swatches-for-woocommerce');
            }
            $ordered_columns[$key] = $value;
        }

        return $ordered_columns;
    }

    /**
     * Add 'group' column content to attribute terms table
     */
    public function add_group_column_content(string $content, string $column_name, int $term_id): string
    {
        if ($column_name === 'group') {
            $group = get_term_meta($term_id, 'ssasfw_swatch_group', true);
            return esc_html($group);
        }

        return $content;
    }

    /**
     * Add 'group' selector to 'add attribute term' form
     */
    public function add_group_selector(string $taxonomy_name): void
    {
        $attribute = Helpers::get_attribute($taxonomy_name);

        if (!$attribute) {
            return;
        }

        $groups = get_option("ssasfw_attribute_groups_{$attribute->id}", []);

        if (!$groups) {
            return;
        }

    ?>
        <div class="form-field">
            <label for="ssasfw_swatch_group"><?php esc_html_e('Group', 'storestack-attribute-swatches-for-woocommerce'); ?></label>
            <select id="ssasfw_swatch_group" name="ssasfw_swatch_group">
                <option value=""><?php esc_html_e('None', 'storestack-attribute-swatches-for-woocommerce'); ?></option>
                <?php foreach ($groups as $group): ?>
                    <option value="<?php echo esc_attr($group); ?>"><?php echo esc_html($group); ?></option>
                <?php endforeach; ?>
            </select>
            <?php wp_nonce_field('ssasfw_save_option_meta_action', 'ssasfw_nonce', false); ?>
        </div>
    <?php
    }

    /**
     * Add 'group' selector to 'edit attribute term' form
     */
    public function add_group_selector_to_edit_page(\WP_Term $term, string $taxonomy_slug): void
    {
        $attribute = Helpers::get_attribute($taxonomy_slug);

        if (!$attribute) {
            return;
        }

        $groups = get_option("ssasfw_attribute_groups_{$attribute->id}", []);

        if (!$groups) {
            return;
        }

        $selected = get_term_meta($term->term_id, 'ssasfw_swatch_group', true);
    ?>
        <tr class="form-field">
            <th>
                <label for="ssasfw_swatch_group"><?php esc_html_e('Group', 'storestack-attribute-swatches-for-woocommerce'); ?></label>
            </th>
            <td>
                <select id="ssasfw_swatch_group" name="ssasfw_swatch_group">
                    <option value=""><?php esc_html_e('None', 'storestack-attribute-swatches-for-woocommerce'); ?></option>
                    <?php foreach ($groups as $group): ?>
                        <option value="<?php echo esc_attr($group); ?>" <?php selected($selected, $group); ?>><?php echo esc_html($group); ?></option>
                    <?php endforeach; ?>
                </select>
                <?php wp_nonce_field('ssasfw_save_option_meta_action', 'ssasfw_nonce', false); ?>
            </td>
        </tr>
    <?php
    }


    //*********************
    // FRONTEND FUNCTIONS
    //*********************

    /**
     * Render attribute options as swatches on frontend
     */
    public function attribute_options_html(string $html, array $args): string
    {
        $product = $args['product'];
        $attribute_name = $args['attribute'];
        $attribute = Helpers::get_attribute($attribute_name);

        if (!$attribute || $attribute->type === 'select') {
            return $html;
        }

        $terms = wc_get_product_terms(
            $product->get_id(),
            $attribute_name,
            ['fields' => 'all']
        );

        if (empty($terms)) {
            return $html;
        }

        // Group terms by their assigned group
        $grouped_terms = [];
        $ungrouped_terms = [];

        foreach ($terms as $term) {
            $group = get_term_meta($term->term_id, 'ssasfw_swatch_group', true);

            if ($group) {
                $grouped_terms[$group][] = $term;
            } else {
                $ungrouped_terms[] = $term;
            }
        }

        $swatch_html = sprintf('<div class="ssasfw-swatch-container ssasfw-swatch-%s %s">', esc_attr($attribute->type), esc_attr($attribute_name));

        // Render ungrouped swatches
        if (!empty($ungrouped_terms)) {
            foreach ($ungrouped_terms as $term) {
                $swatch_html .= $this->render_swatch($term, $attribute, $args);
            }
        }

        // Render grouped swatches
        foreach ($grouped_terms as $group => $group_terms) {
            $swatch_html .= sprintf('<div class="ssasfw-swatch-group" data-group="%s">', esc_attr($group));
            $swatch_html .= sprintf('<div class="ssasfw-swatch-group-label">%s</div>', esc_html($group));
            $swatch_html .= '<div class="ssasfw-swatch-group-items">';

            foreach ($group_terms as $term) {
                $swatch_html .= $this->render_swatch($term, $attribute, $args);
            }

            $swatch_html .= '</div></div>';
        }

        $swatch_html .= '</div>';

        return apply_filters('ssasfw_swatch_html', $swatch_html . "<div style='display:none!important;'>{$html}</div>", $args);
    }

    /**
     * Render individual swatch HTML
     */
    private function render_swatch(\WP_Term $term, object $attribute, array $args): string
    {
        $selected = sanitize_title($args['selected']) === $term->slug ? 'selected' : '';
        $img_placeholder = wc_placeholder_img('thumbnail', ['class' => 'image-swatch']);

        /**
         * Apply filter to allow custom option label modifications, such as additional info.
         */
        $option_label = apply_filters('woocommerce_variation_option_name', $term->name, $term, $attribute->slug, $args['product']); // phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedHooknameFound

        $html = sprintf(
            '<div class="ssasfw-swatch-wrapper ssasfw-swatch-%s %s" data-slug="%s" data-label="%s">',
            esc_attr($attribute->type),
            esc_attr($selected),
            esc_attr($term->slug),
            esc_attr($option_label)
        );

        if ($attribute->type === 'color') {
            $color = get_term_meta($term->term_id, 'ssasfw_swatch_color', true);
            $html .= $color ? sprintf('<div class="color-swatch" style="background-color: %s;"></div>', esc_attr($color)) : wp_kses_post($img_placeholder);
        } elseif ($attribute->type === 'image') {
            $img_id = get_term_meta($term->term_id, 'ssasfw_swatch_image', true);
            $html .= $img_id ? wp_get_attachment_image($img_id, 'thumbnail', false, ['class' => 'image-swatch']) : wp_kses_post($img_placeholder);
        } elseif ($attribute->type === 'button') {
            $html .= sprintf('<div class="button-swatch">%s</div>', esc_html($option_label));
        } elseif ($attribute->type === 'radio') {
            $html .= sprintf('<label class="radio-swatch"><input type="radio" %s><span>%s</span></label>', $selected ? 'checked' : '', esc_html($option_label));
        }

        $html .= '<svg viewBox="0 0 24 24" stroke-width="4" stroke="#fff" fill="none" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="m4.5 12.75 6 6 9-13.5"/></svg>';
        $html .= '</div>';

        return $html;
    }

    /**
     * Render tooltip HTML for swatches
     */
    public function tooltip_html(): void
    {
        ob_start();
    ?>
        <div id="ssasfw-tooltip" class="ssasfw-tooltip" role="tooltip" aria-hidden="true">
            <div class="ssasfw-tooltip-content">
                <div class="ssasfw-tooltip-thumbnail"></div>
                <div class="ssasfw-tooltip-label"></div>
            </div>
            <div class="ssasfw-tooltip-arrow"></div>
        </div>
<?php
        $tooltip_html = (string) ob_get_clean();
        echo wp_kses_post(apply_filters('ssasfw_tooltip_html', $tooltip_html));
    }
}
