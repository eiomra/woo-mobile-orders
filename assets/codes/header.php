<?php
add_action('admin_head', 'custom_admin_header_modifications');

function custom_admin_header_modifications() {
    // Get the current user object
    $current_user = wp_get_current_user();
    $username = esc_js($current_user->display_name);
    $menu_icon_url = plugins_url('../img/menu.svg', __FILE__);
    $home_icon_url = plugins_url('../img/home.svg', __FILE__);
    $comment_icon_url = plugins_url('../img/comment.svg', __FILE__);
    $plus_icon_url = plugins_url('../img/plus.svg', __FILE__);
    $pp_icon_url = plugins_url('../img/pp.svg', __FILE__);
    ?>
    <style>
        @media only screen and (max-width: 768px) {
            #wp-admin-bar-menu-toggle .ab-icon,
            #wp-admin-bar-site-name .ab-icon,
            #wp-admin-bar-new-content .ab-icon,
            #wp-admin-bar-comments .ab-icon,
            #wp-admin-bar-updates .ab-icon,
            #wp-admin-bar-wp-logo .ab-icon {
                display: none !important;
            }
            #wp-admin-bar-menu-toggle .ab-item,
            #wp-admin-bar-new-content .ab-item {
                display: flex !important;
                align-items: center !important;
            }
            #wp-admin-bar-comments .ab-item {
                display: flex !important;
                align-items: center !important;
                margin-right: 20px !important;
            }

            #wp-admin-bar-site-name .ab-item {
                padding-left: 13px !important;
                padding-right: -36px !important;
                display: flex !important;
                align-items: center !important;
                margin-left: 20px !important;
            }

            #wp-admin-bar-my-account .ab-item {
                background: none !important;
                color: #ffffff !important;
                display: flex !important;
                align-items: center !important;
                padding-right: 170px !important;
            }

            #wp-admin-bar-site-name > .ab-item::before {
                content: none !important;
            }
            .meespc {
                margin-left: 10px !important;
            } 
            .ppnb {
                background-color: transparent !important;
                border-color: transparent !important;
            }
        }
    </style>
    <script type="text/javascript">
        document.addEventListener("DOMContentLoaded", function() {
            if (window.innerWidth <= 768) { // Apply changes only for mobile view
                // menu icon
                var menuIcon = document.querySelector("#wp-admin-bar-menu-toggle .ab-item");
                if (menuIcon) {
                    menuIcon.innerHTML = "<img src='<?php echo $menu_icon_url; ?>' class='meespc'>";
                }

                // home icon
                var homeItem = document.querySelector("#wp-admin-bar-site-name > .ab-item");
                if (homeItem) {
                    homeItem.innerHTML = "<img src='<?php echo $home_icon_url; ?>'>";
                }

                // comments icon
                var commentsItem = document.querySelector("#wp-admin-bar-comments .ab-item");
                if (commentsItem) {
                    commentsItem.innerHTML = "<img src='<?php echo $comment_icon_url; ?>'>";
                }

                // + icon
                var addNewItem = document.querySelector("#wp-admin-bar-new-content .ab-item");
                if (addNewItem) {
                    addNewItem.innerHTML = "<img src='<?php echo $plus_icon_url; ?>'>";
                }

                var accountItem = document.querySelector("#wp-admin-bar-my-account > .ab-item");
                if (accountItem) {
                    // Remove the avatar image
                    var avatar = accountItem.querySelector("img.avatar");
                    if (avatar) {
                        avatar.remove();
                    }

                    // Replace with new text and icon
                    var pp_icon_url = "<?php echo $pp_icon_url; ?>";
                    accountItem.innerHTML = `
                        <img src='${pp_icon_url}' class='ppnb'>
                        <span class='utna'>
                            Howdy, <?php 
                            $usernamee = strlen($username) > 5 ? substr($username, 0, 6)  : $username;
                            echo $usernamee; ?>
                        </span>
                    `;
                }
            }
        });
    </script>
    <?php
}
?>
