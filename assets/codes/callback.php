<?php
function woo_mobile_orders_page_callback() {
    global $wpdb;

    $status = isset($_GET['status']) ? sanitize_text_field($_GET['status']) : 'all';
    $search = isset($_GET['s']) ? sanitize_text_field($_GET['s']) : '';
    $filter_search = isset($_GET['search']) ? sanitize_text_field($_GET['search']) : '';
    $year = isset($_GET['year']) ? sanitize_text_field($_GET['year']) : '';
    $orderby = isset($_GET['orderby']) ? sanitize_text_field($_GET['orderby']) : 'date_created_gmt';
    $order = isset($_GET['order']) ? sanitize_text_field($_GET['order']) : 'DESC';

    $items_per_page = 30;
    $current_page = isset($_GET['paged']) ? max(1, intval($_GET['paged'])) : 1;
    $offset = ($current_page - 1) * $items_per_page;

    $status_query = "SELECT status, COUNT(*) FROM {$wpdb->prefix}wc_orders GROUP BY status";
    $counts = $wpdb->get_results($status_query, ARRAY_A);

    $count_map = [
        'all'        => 0,
        'wc-pending' => 0,
        'wc-processing' => 0,
        'wc-completed' => 0,
        'wc-cancelled' => 0
    ];

    foreach ($counts as $row) {
        $count_map[$row['status']] = intval($row['COUNT(*)']);
        $count_map['all'] += $count_map[$row['status']];
    }

    $where_clauses = [];

    if ($status !== 'all') {
        $where_clauses[] = $wpdb->prepare("o.status = %s", $status);
    }

    if (!empty($search)) {
        $search_term = '%' . $wpdb->esc_like($search) . '%';
        $where_clauses[] = $wpdb->prepare(
            "(o.id LIKE %s OR o.billing_email LIKE %s OR a.first_name LIKE %s OR a.last_name LIKE %s)",
            $search_term, $search_term, $search_term, $search_term
        );
    }

    if (!empty($filter_search)) {
        $filter_search_term = '%' . $wpdb->esc_like($filter_search) . '%';
        $where_clauses[] = $wpdb->prepare(
            "(u.user_login LIKE %s OR o.billing_email LIKE %s OR a.first_name LIKE %s OR a.last_name LIKE %s)",
            $filter_search_term, $filter_search_term, $filter_search_term, $filter_search_term
        );
    }

    if (!empty($year)) {
        $year_start = $year . '-01-01 00:00:00';
        $year_end = $year . '-12-31 23:59:59';
        $where_clauses[] = $wpdb->prepare("o.date_created_gmt BETWEEN %s AND %s", $year_start, $year_end);
    }

    $where_clause = !empty($where_clauses) ? 'WHERE ' . implode(' AND ', $where_clauses) : '';

    $allowed_orderby = ['id', 'date_created_gmt', 'total_amount', 'status'];
    $allowed_order = ['ASC', 'DESC'];

    $orderby = in_array($orderby, $allowed_orderby) ? $orderby : 'date_created_gmt';
    $order = in_array(strtoupper($order), $allowed_order) ? strtoupper($order) : 'DESC';

    $query = "
        SELECT o.id, o.status, o.total_amount, o.date_created_gmt, o.currency, o.billing_email, a.first_name, a.last_name
        FROM {$wpdb->prefix}wc_orders o
        LEFT JOIN {$wpdb->prefix}wc_order_addresses a ON o.id = a.order_id AND a.address_type = 'billing'
        LEFT JOIN {$wpdb->prefix}users u ON o.customer_id = u.ID
        $where_clause
        ORDER BY $orderby $order
        LIMIT %d OFFSET %d
    ";

    $orders = $wpdb->get_results($wpdb->prepare($query, $items_per_page, $offset));

    $total_query = "
        SELECT COUNT(*)
        FROM {$wpdb->prefix}wc_orders o
        LEFT JOIN {$wpdb->prefix}wc_order_addresses a ON o.id = a.order_id AND a.address_type = 'billing'
        LEFT JOIN {$wpdb->prefix}users u ON o.customer_id = u.ID
        $where_clause
    ";

    $total_orders = $wpdb->get_var($total_query);
    $total_pages = ceil($total_orders / $items_per_page);

    $years = $wpdb->get_results("SELECT DISTINCT DATE_FORMAT(date_created_gmt, '%Y') AS year FROM {$wpdb->prefix}wc_orders ORDER BY year DESC", ARRAY_A);
    ?>
    <div class="wrap">
        <span class='ttord'>Orders</span>
        <hr class='wp-header-end'>
        <ul class='subsubsub'>
            <?php foreach ($count_map as $key => $count) : ?>
                <li class='<?php echo $status === $key ? "$key current" : $key; ?>'>
                    <a href='<?php echo esc_url(add_query_arg(['page' => 'mobile-orders', 'status' => $key], admin_url('admin.php'))); ?>'>
                        <?php echo ucwords(str_replace('wc-', '', $key)); ?> <span class='count'>(<?php echo esc_html($count); ?>)</span>
                    </a> |
                </li>
            <?php endforeach; ?>
        </ul>

        <form id="mobile-orders-filter" method="get" action="<?php echo esc_url(admin_url('admin.php')); ?>">
            <input type="hidden" name="page" value="mobile-orders">
            <p class="search-box">
                <div class="container tcontaine text-center">
                    <div class="row">
                        <div class="col-7">
                            <input type="search" class="tywooip" id="orders-search-input" name="s" value="<?php echo esc_attr($search); ?>" />
                        </div>
                        <div class="col-4">
                            <?php wp_nonce_field('wc_orders_bulk_action_nonce'); ?>
                            <button type="submit" id="search-submit" class="button">Search Orders</button>
                        </div>
                    </div>
                </div>
            </p>
        </form>

        <form id="filters-form" method="get" action="<?php echo esc_url(admin_url('admin.php')); ?>">
            <div class="container tcontaine text-start">
                <div class="row">
                    <div class="col-8">  
                        <input type="hidden" name="page" value="mobile-orders">
                        <input type="text" class="tywooip" name="search" placeholder="Filter by customer" value="<?php echo esc_attr($filter_search); ?>">
                    </div>
                    <div class="col-4 text-center">
                        <input type="submit" value="Filter" class="button">
                    </div>
                </div>
            </div>
        </form>

        <form id="orders-filter" method="post" action="<?php echo esc_url(admin_url('admin-post.php')); ?>">
            <input type="hidden" name="action" value="wc_orders_bulk_action" />
            <input type="hidden" name="page" value="mobile-orders" />
            <?php wp_nonce_field('wc_orders_bulk_action_nonce'); ?>

            <div class="container tcontaine text-start">
                <div class="row">
                    <div class="col-5">
                        <select class="form-select tywooip" aria-label="Default select example" name="bulk_action" id="bulk-action-selector-top">
                            <option value="-1">Bulk actions</option>
                            <option value="mark_processing">Change status to processing</option>
                            <option value="mark_on-hold">Change status to on-hold</option>
                            <option value="mark_completed">Change status to completed</option>
                            <option value="mark_cancelled">Change status to cancelled</option>
                            <option value="trash">Move to Trash</option>
                        </select>
                    </div>
                    <div class="col-3 text-start">
                        <input type="submit" id="doaction" class="button action" value="Apply" />
                    </div>
                    <div class="col-4 text-center">
                        <select class="form-select" name="year" id="filter-year">
                            <option value=" ">All dates</option>
                            <?php foreach ($years as $year_option) : ?>
                                <option value="<?php echo esc_attr($year_option['year']); ?>" <?php selected($year, $year_option['year']); ?>><?php echo esc_html($year_option['year']); ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>
            </div>

            <?php if (!empty($orders)) : ?>
                <?php foreach ($orders as $order) : ?>
                    <div class="status-<?php echo esc_attr(str_replace('wc-', '', $order->status)); ?>">
                        <div class="container text-start tumazrebgor">
                            <div class="row row-cols-1 row-cols-sm-1 row-cols-md-1">
                                <div class="col">
                                    <a href="<?php echo esc_url(admin_url('admin.php?page=wc-orders&action=edit&id=' . $order->id)); ?>">
                                        # <?php echo esc_html($order->id); ?> <?php echo esc_html($order->first_name . ' ' . $order->last_name); ?>
                                    </a>
                                </div>
                                <div class="col">
                                    <span class="ordat">Date: </span><span class="ordat2"> <?php echo date('F Y', strtotime($order->date_created_gmt)); ?></span>
                                    &nbsp;&nbsp;| &nbsp;&nbsp;<span class="ordat">Total: </span><span class="ordat2"> <?php echo wc_price($order->total_amount, ['currency' => $order->currency]); ?></span>
                                </div>
                            </div>
                        </div>
                        <div class="container text-center tebgor">
                            <div class="row">
                                <div class="col-1">
                                    <input type="checkbox" name="post[]" value="<?php echo esc_attr($order->id); ?>">
                                </div>
                                <div class="col-6 column-status">
                                    <span class="status-text"><?php echo esc_html(ucwords(str_replace('wc-', '', $order->status))); ?></span>
                                </div>
                                <div class="col-5">
                                    <button type="button" class="mtwov" onclick="window.location.href='<?php echo esc_url(admin_url('admin.php?page=wc-orders&action=edit&id=' . $order->id)); ?>'"><span class="dashicons dashicons-visibility"></span> View</button></div>
                            </div>
                        </div>
                    </div>

                    
                <?php endforeach; ?>
            <?php else : ?>
                <div class="container text-center tumazrebgor">
                    <div class="row row-cols-1 row-cols-sm-1 row-cols-md-1">
                        <div class="col">No orders found.</div>
                    </div>
                </div>
            <?php endif; ?>

            
            
            <div class="tablenav bottom">
                <div class="alignleft actions bulkactions">
                    <label for="bulk-action-selector-bottom" class="screen-reader-text">Select bulk action</label>
                    <select name="bulk_action" id="bulk-action-selector-bottom">
                        <option value="-1">Bulk actions</option>
                        <option value="mark_processing">Change status to processing</option>
                        <option value="mark_on-hold">Change status to on-hold</option>
                        <option value="mark_completed">Change status to completed</option>
                        <option value="mark_cancelled">Change status to cancelled</option>
                        <option value="trash">Move to Trash</option>
                    </select>
                    <input type="submit" id="doaction2" class="button action" value="Apply" />
                </div>
                
                
            </div>
        </form>
    </div>
            </div>


                <div class="container text-center pagtn">
  <div class="row">
    <div class="col-3"><?php echo $total_orders; ?> items</div>

    <?php if ($current_page > 1) : ?>
    <div class="col-1">
    <a href="<?php echo add_query_arg('paged', 1); ?>">
        <button class="mtwovu"><i class="bi bi-chevron-left"></i></button></a>
    </div>
    <div class="col-1">
    <a href="<?php echo add_query_arg('paged', $current_page - 1); ?>">
        <button class="mtwovu"><i class="bi bi-chevron-double-left"></i></button></a>
    </div>    
    <?php endif; ?>


    <div class="col-4">
<?php echo $current_page; ?> of <?php echo $total_pages; ?>
    </div>


    <?php if ($current_page < $total_pages) : ?>

<div class="col-1">
<a href="<?php echo add_query_arg('paged', $current_page + 1); ?>">
    <button class="mtwovt"><i class="bi bi-chevron-right"></i></button></a>
    </div>

    <div class="col-1">
    <a href="<?php echo add_query_arg('paged', $total_pages); ?>">
    <button class="mtwovt"><i class="bi bi-chevron-double-right"></i></button></a>
    </div>
    <?php endif; ?>

  </div>
</div>



                </div>

    <script type="text/javascript">
        document.getElementById('filter-year').addEventListener('change', function() {
            var selectedYear = this.value;
            var url = new URL(window.location.href);
            if (selectedYear) {
                url.searchParams.set('year', selectedYear);
            } else {
                url.searchParams.delete('year');
            }
            window.location.href = url.href;
        });
    </script>
    <?php
}

?>