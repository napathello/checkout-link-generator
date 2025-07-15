<?php
/*
Plugin Name: NP Checkout Link Generator
Description: Generate WooCommerce checkout links with selected products and coupons from the admin page.
Version: 1.1
Author: Napat
*/

if ( ! defined( 'ABSPATH' ) ) exit;

// Load textdomain for translation
add_action('plugins_loaded', function() {
    load_plugin_textdomain('np-checkout-link-generator', false, dirname(plugin_basename(__FILE__)) . '/languages');
});

// Check WooCommerce version
add_action('admin_init', function() {
    if (!class_exists('WooCommerce') || version_compare(WC()->version, '10.0', '<')) {
        add_action('admin_notices', function() {
            echo '<div class="notice notice-error"><p>' . __('NP Checkout Link Generator requires WooCommerce 10.0 or higher', 'np-checkout-link-generator') . '</p></div>';
        });
    }
});

// Add admin menu
add_action('admin_menu', function() {
    add_submenu_page(
        'edit.php?post_type=product',
        __('NP Checkout Link Generator', 'np-checkout-link-generator'),
        __('Checkout Link Generator', 'np-checkout-link-generator'),
        'manage_woocommerce',
        'np-checkout-link',
        'npclg_render_admin_page',
        56
    );
});

// Enqueue scripts and styles
add_action('admin_enqueue_scripts', function($hook) {
    if (isset($_GET['page']) && $_GET['page'] === 'np-checkout-link') {
        // Select2
        wp_enqueue_style('select2', 'https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css');
        wp_enqueue_script('select2', 'https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js', ['jquery'], null, true);
        // Custom
        wp_enqueue_script('npclg-admin', plugin_dir_url(__FILE__).'npclg-admin.js', ['jquery','select2'], null, true);
        wp_localize_script('npclg-admin', 'NPCLG_AJAX', [
            'ajax_url' => admin_url('admin-ajax.php'),
            'site_url' => site_url(),
        ]);
        wp_enqueue_style('npclg-admin', plugin_dir_url(__FILE__).'npclg-admin.css');
    }
});

// AJAX: Search products (by title or SKU)
add_action('wp_ajax_npclg_search_products', function() {
    if (!current_user_can('manage_woocommerce')) wp_send_json([]);
    $term = isset($_GET['term']) ? sanitize_text_field($_GET['term']) : '';
    $args = [
        'post_type' => ['product', 'product_variation'],
        'post_status' => 'publish',
        'posts_per_page' => 20,
        's' => $term,
        'fields' => 'ids',
    ];
    $ids = get_posts($args);
    // Search by SKU
    global $wpdb;
    $sku_ids = $wpdb->get_col($wpdb->prepare(
        "SELECT post_id FROM {$wpdb->postmeta} WHERE meta_key = '_sku' AND meta_value LIKE %s",
        '%' . $wpdb->esc_like($term) . '%'
    ));
    $ids = array_unique(array_merge($ids, $sku_ids));
    $results = [];
    foreach ($ids as $id) {
        $product = wc_get_product($id);
        if (!$product) continue;
        $sku = $product->get_sku();
        $label = $product->get_name();
        if ($sku) $label .= " (SKU: $sku)";
        $results[] = [
            'id' => $id,
            'text' => $label,
        ];
    }
    wp_send_json($results);
});

// AJAX: Search coupons
add_action('wp_ajax_npclg_search_coupons', function() {
    if (!current_user_can('manage_woocommerce')) wp_send_json([]);
    $term = isset($_GET['term']) ? sanitize_text_field($_GET['term']) : '';
    $args = [
        'post_type' => 'shop_coupon',
        'post_status' => 'publish',
        'posts_per_page' => 20,
        's' => $term,
        'fields' => 'ids',
    ];
    $ids = get_posts($args);
    $results = [];
    foreach ($ids as $id) {
        $code = get_the_title($id);
        $results[] = [
            'id' => $code,
            'text' => $code,
        ];
    }
    wp_send_json($results);
});

// Render admin page
function npclg_render_admin_page() {
    ?>
    <div class="wrap">
        <h1><?php _e('NP Checkout Link Generator', 'np-checkout-link-generator'); ?></h1>
        <form id="npclg-form" onsubmit="return false;">
            <table class="form-table">
                <tbody id="npclg-products-list">
                    <tr>
                        <th><?php _e('Product', 'np-checkout-link-generator'); ?></th>
                        <td class="npclg-product-row">
                            <select class="npclg-product-select" style="width:300px;"></select>
                            <input type="number" class="npclg-qty" min="1" value="1" style="width:60px;">
                            <button type="button" class="button npclg-add-product">+</button>
                        </td>
                    </tr>
                </tbody>
                <tbody>
                    <tr>
                        <th><?php _e('Coupon', 'np-checkout-link-generator'); ?></th>
                        <td>
                            <select class="npclg-coupon-select" style="width:300px;"></select>
                        </td>
                    </tr>
                </tbody>
            </table>
            <p>
                <button type="button" class="button button-primary" id="npclg-generate"><?php _e('Generate Link', 'np-checkout-link-generator'); ?></button>
            </p>
            <div id="npclg-result" style="display:none;">
                <input type="text" id="npclg-link" readonly style="width:60%;">
                <button type="button" class="button" id="npclg-copy"><?php _e('Copy', 'np-checkout-link-generator'); ?></button>
                <a href="#" class="button" id="npclg-preview" target="_blank"><?php _e('Preview', 'np-checkout-link-generator'); ?></a>
            </div>
        </form>
    </div>
    <?php
}

// Inline JS (for demo, but will be loaded as file by enqueue)
add_action('admin_footer', function() {
    if (isset($_GET['page']) && $_GET['page'] === 'np-checkout-link') {
        ?>
        <script>
        jQuery(function($){
            function initProductSelect(el) {
                el.select2({
                    ajax: {
                        url: NPCLG_AJAX.ajax_url,
                        dataType: 'json',
                        delay: 250,
                        data: function(params) {
                            return { action: 'npclg_search_products', term: params.term };
                        },
                        processResults: function(data) {
                            return { results: data };
                        },
                        cache: true
                    },
                    placeholder: '<?php _e('Search products...', 'np-checkout-link-generator'); ?>'
                });
            }
            function initCouponSelect(el) {
                el.select2({
                    ajax: {
                        url: NPCLG_AJAX.ajax_url,
                        dataType: 'json',
                        delay: 250,
                        data: function(params) {
                            return { action: 'npclg_search_coupons', term: params.term };
                        },
                        processResults: function(data) {
                            return { results: data };
                        },
                        cache: true
                    },
                    placeholder: '<?php _e('Search coupons...', 'np-checkout-link-generator'); ?>'
                });
            }
            // Create a new product row
            function createProductRow() {
                var row = $('<tr>');
                var td = $('<td class="npclg-product-row">');
                var select = $('<select class="npclg-product-select" style="width:300px;"></select>');
                var qty = $('<input type="number" class="npclg-qty" min="1" value="1" style="width:60px;">');
                var addBtn = $('<button type="button" class="button npclg-add-product">+</button>');
                var delBtn = $('<button type="button" class="button npclg-del-product">–</button>');
                td.append(select).append(qty).append(addBtn).append(delBtn);
                row.append('<th><?php _e('Product', 'np-checkout-link-generator'); ?></th>').append(td);
                initProductSelect(select);
                return row;
            }
            // Render +/Delete buttons correctly
            function updateProductRowButtons() {
                var rows = $('#npclg-products-list tr');
                rows.each(function(i){
                    var addBtn = $(this).find('.npclg-add-product');
                    var delBtn = $(this).find('.npclg-del-product');
                    if (i === rows.length - 1) {
                        addBtn.show();
                    } else {
                        addBtn.hide();
                    }
                    if (rows.length === 1) {
                        delBtn.hide();
                    } else {
                        delBtn.show();
                    }
                });
            }
            // Initial
            $('#npclg-products-list').html('');
            $('#npclg-products-list').append(createProductRow());
            updateProductRowButtons();
            initCouponSelect($('.npclg-coupon-select'));
            // Add product row
            $(document).on('click', '.npclg-add-product', function(){
                $('#npclg-products-list').append(createProductRow());
                updateProductRowButtons();
            });
            // Delete product row
            $(document).on('click', '.npclg-del-product', function(){
                $(this).closest('tr').remove();
                updateProductRowButtons();
            });
            // Generate link
            $('#npclg-generate').on('click', function(){
                var products = [];
                $('#npclg-products-list tr').each(function(){
                    var pid = $(this).find('select').val();
                    var qty = $(this).find('input.npclg-qty').val();
                    if(pid && qty) products.push(pid+':'+qty);
                });
                var coupon = $('.npclg-coupon-select').val();
                if(products.length === 0) {
                    alert('<?php _e('Please select at least 1 product.', 'np-checkout-link-generator'); ?>');
                    return;
                }
                var url = NPCLG_AJAX.site_url + '/checkout-link/?products=' + products.join(',');
                if(coupon) url += '&coupon=' + encodeURIComponent(coupon);
                $('#npclg-link').val(url);
                $('#npclg-preview').attr('href', url);
                $('#npclg-result').show();
            });
            // Copy
            $('#npclg-copy').on('click', function(){
                var link = $('#npclg-link');
                link[0].select();
                document.execCommand('copy');
            });
        });
        </script>
        <style>
        .select2-container { min-width: 250px; }
        #npclg-link { font-family: monospace; }
        .npclg-del-product { margin-left: 4px; color: #a00; }
        </style>
        <?php
    }
}); 