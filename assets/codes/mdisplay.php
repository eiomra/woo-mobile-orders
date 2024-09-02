<?php
function woo_mobile_orders_admin_styles() {
    echo '
    <style>
        // @media (max-width: 767px) {
        //     #toplevel_page_woocommerce ul.wp-submenu li:nth-child(3) {
        //         display: none; /* Hides default Orders menu item on mobile */
        //     }
        // }
        @media (min-width: 768px) {
            #toplevel_page_woocommerce ul.wp-submenu li a[href="admin.php?page=mobile-orders"] {
                display: none; /* Hides custom Mobile Orders menu item on larger screens */
            }
        }
    </style>
    ';
}
?>