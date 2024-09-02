<?php
add_action('admin_post_wc_orders_bulk_action', function() {
    if (!current_user_can('manage_woocommerce') || !check_admin_referer('wc_orders_bulk_action_nonce')) {
        wp_die('You do not have sufficient permissions to access this page.');
    }

    global $wpdb;

    $action = isset($_POST['bulk_action']) ? sanitize_text_field($_POST['bulk_action']) : '';
    $order_ids = isset($_POST['post']) ? array_map('intval', $_POST['post']) : [];

    if (!empty($order_ids) && in_array($action, ['mark_processing', 'mark_on-hold', 'mark_completed', 'mark_cancelled', 'trash'])) {
        $status_map = [
            'mark_processing' => 'wc-processing',
            'mark_on-hold'    => 'wc-on-hold',
            'mark_completed'  => 'wc-completed',
            'mark_cancelled'  => 'wc-cancelled'
        ];

        foreach ($order_ids as $order_id) {
            if ($action === 'trash') {
                $wpdb->delete("{$wpdb->prefix}wc_orders", ['id' => $order_id], ['%d']);
            } else {
                $wpdb->update(
                    "{$wpdb->prefix}wc_orders",
                    ['status' => $status_map[$action]],
                    ['id' => $order_id],
                    ['%s'],
                    ['%d']
                );
            }
        }
    }

    wp_redirect(add_query_arg('page', 'mobile-orders', admin_url('admin.php')));
    exit;
});

?>