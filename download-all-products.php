<?php
/**
 * Plugin Name: Download All Products
 * Description: Download all WooCommerce products in CSV format including category, image, description, and price.
 * Version: 1.1
 * Author: Zioun + ChatGPT
 */

add_action('admin_menu', 'dap_add_admin_menu');
add_action('admin_post_dap_download_csv', 'dap_download_csv');

function dap_add_admin_menu() {
    add_menu_page(
        'Download Products',
        'Download Products',
        'manage_woocommerce',
        'download-products',
        'dap_download_page',
        'dashicons-download',
        56
    );
}

function dap_download_page() {
    echo '<div class="wrap"><h1>Download All Products</h1>';
    echo '<form method="post" action="' . admin_url('admin-post.php') . '">';
    echo '<input type="hidden" name="action" value="dap_download_csv">';
    submit_button("Download CSV");
    echo '</form></div>';
}

function dap_download_csv() {
    if (!current_user_can('manage_woocommerce')) {
        wp_die('Unauthorized user');
    }

    $args = array(
        'limit' => -1,
        'return' => 'ids',
    );

    $products = wc_get_products($args);
    $filename = "products_" . date('Y-m-d_H-i-s') . ".csv";

    header('Content-Type: text/csv');
    header('Content-Disposition: attachment; filename=' . $filename);

    $output = fopen('php://output', 'w');
    fputcsv($output, array('ID', 'Name', 'Category', 'Image URL', 'Description', 'Price'));

    foreach ($products as $product_id) {
        $product = wc_get_product($product_id);
        $categories = wp_get_post_terms($product_id, 'product_cat', array('fields' => 'names'));
        $category_list = implode(', ', $categories);
        $image_id = $product->get_image_id();
        $image_url = $image_id ? wp_get_attachment_url($image_id) : '';
        $description = strip_tags($product->get_description());

        fputcsv($output, array(
            $product->get_id(),
            $product->get_name(),
            $category_list,
            $image_url,
            $description,
            $product->get_price()
        ));
    }

    fclose($output);
    exit;
}
?>
