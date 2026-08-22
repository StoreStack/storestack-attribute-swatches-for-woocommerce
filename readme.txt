=== StoreStack Attribute Swatches for WooCommerce ===
Contributors: tiagosartor3, storestack
Tags: woocommerce, swatches, variations, product attributes, visual selectors
Requires at least: 6.2
Tested up to: 7.0
Requires PHP: 8.1
Stable tag: 1.0.1
License: GPLv3 or later
License URI: https://www.gnu.org/licenses/gpl-3.0.html

Transform WooCommerce variation dropdowns into beautiful, interactive color, image, button, and radio swatches with custom grouping and tooltips.

== Description ==

**StoreStack Attribute Swatches for WooCommerce** replaces default, plain WooCommerce variation dropdowns with visual, interactive swatches. Easily display product colors, pattern images, inline text buttons, and radio options to improve user experience and boost conversions on your online store.

### Features

* **Multiple Swatch Types**: Convert default attribute dropdowns into **Color**, **Image**, **Button**, or **Radio** swatches.
* **Color Swatches**: Integrated HEX Color Picker allows precise color assignments for each attribute option.
* **Image Swatches**: Assign custom images or texture swatches directly from the WordPress Admin.
* **Interactive Tooltips**: Hovering over image swatches triggers a dynamic thumbnail preview.
* **Button Swatches**: Ideal for sizes (S, M, L, XL), dimensions, and specifications.
* **Radio Swatches**: Classic radio list options for clean, structured variation choices.
* **Swatch Grouping**: Group related terms into custom categories (e.g. "Primary Colors", "Metallic Finishes", "Standard Sizes") with visual section headers.
* **Fully Responsive & Lightweight**: Pure CSS styling and minimal JavaScript overhead ensure fast load times and seamless mobile browsing.
* **Developer Friendly**: Built-in filter hooks and CSS selectors for seamless theme integration and customization.

### Integrations

Works seamlessly with the following plugins:

* **[WooCommerce](https://wordpress.org/plugins/woocommerce)**
* **[StoreStack Attribute Fees for WooCommerce](https://wordpress.org/plugins/storestack-attribute-fees-for-woocommerce)**

== Need Help or Have Feedback? ==

Before leaving a negative review, please consider reaching out to us first! If you encounter any bugs, unexpected behavior, or have ideas for new features and improvements, please open a support request or report an issue on our GitHub repository. We will gladly work with you to resolve any problems.

== Contributing & GitHub Repository ==

We welcome and appreciate contributions from the community! Whether you want to fix a bug, improve documentation, or propose a new feature, feel free to fork our repository, submit pull requests, or open issues on GitHub:

[GitHub Repository](https://github.com/StoreStack/storestack-attribute-swatches-for-woocommerce)

Your feedback and code contributions help make this plugin better for everyone.

== Installation ==

### Getting Started

1. **Install and Activate**:
   - Download the `.zip` file from WordPress.org and install via **WordPress Admin > Plugins > Add New > Upload Plugin**.
   - Click **Activate** to enable the plugin.

2. **Configure Attribute Types**:
   - Go to **Products > Attributes** in your WordPress Admin.
   - Edit an existing attribute or create a new one, then set the **Type** to **Color**, **Image**, **Button**, or **Radio**.
   - Create **Groups** if you want to separate options into subgroups.

3. **Assign Swatches & Groups**:
   - Click **Configure terms** for your attribute.
   - Add or edit terms to select colors using the color picker or to upload images via the Media Library.
   - Assign the desired **Group** to the option if you have created one in the previous step.

4. **View on Product Pages**:
   - Create or edit a **Variable Product**, assign the configured attribute under the **Attributes** tab, and enable **Used for variations**.
   - Your custom swatches will automatically replace standard dropdowns on the single product page.
   - *On some hosting providers, it may be needed to clear the server-level cache in order for the changes to be reflected on the frontend.*

== Frequently Asked Questions ==

= What swatch types are supported? =
The plugin currently supports four swatch types: **Color**, **Image**, **Button**, and **Radio**.

= How do Swatch Groups work? =
When editing an attribute under **Products > Attributes**, you can define custom group names. When editing individual terms (e.g. Blue, Red, Metallic Silver), assign them to a group. On the frontend, swatches will be organized under titled section headers for better visual hierarchy.

= I've configured the swatches as instructed, but I can't see the changes =
After setting or updating the swatches, it may be needed to clear the server-level cache in order for the changes to be reflected on the frontend.

= Is it possible to change the appearance of the swatches? =
Yes! The swatches use clean, standard HTML markup and CSS selectors. You can easily override styles in your theme's `style.css` or via the WordPress Customizer.

= Can developers customize the swatch HTML or tooltip markup? =
Yes! The plugin provides custom filter hooks to allow developers to modify or wrap the output as needed.

= Does this plugin support WooCommerce High-Performance Order Storage (HPOS)? =
Yes! The plugin declares full compatibility with WooCommerce HPOS (Custom Order Tables).

== Screenshots ==

1. Add a new attribute or edit an existing one, choosing a desired swatch type and assigning groups if needed.
2. Click "configure terms" to create or edit attribute options.
3. Add new attribute options or edit existing ones.
4. Assign the global attribute to a variable product, making sure "Used for variations" is checked.
5. Frontend demo with the Storefront theme.
6. Hovering over or clicking on an image swatch will trigger a tooltip to showcase a larger version of the image.

== Changelog ==

= 1.0.1 - 2026/08/16 =
* Rename 'text' swatch to 'button' swatch.
* Improve CSS class selectors for better theme compatibility.
* Update README.

= 1.0.0 - 2026/08/15 =
* Initial release.
