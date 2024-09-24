<?php
/*
Plugin Name: Vincent Defaults
Plugin URI: https://vincentrozenberg.com
Description: Sets default options for WordPress installations.
Version: 1.4
Author: Vincent Rozenberg
Author URI: https://vincentrozenberg.com
*/

// Remove comment support from WordPress
function vincent_remove_comments() {
    remove_menu_page('edit-comments.php');
    add_action('admin_menu', 'vincent_remove_comments_admin_bar');
    add_action('init', 'vincent_comments_post_types_support');
}
add_action('admin_menu', 'vincent_remove_comments');

function vincent_comments_post_types_support() {
    foreach (get_post_types() as $post_type) {
        if (post_type_supports($post_type, 'comments')) {
            remove_post_type_support($post_type, 'comments');
            remove_post_type_support($post_type, 'trackbacks');
        }
    }
}

function vincent_remove_comments_admin_bar() {
    if (is_admin_bar_showing()) {
        remove_action('admin_bar_menu', 'wp_admin_bar_comments_menu', 60);
    }
}

// Hide the admin bar for new users by default
function vincent_hide_admin_bar_by_default($user_id) {
    update_user_meta($user_id, 'show_admin_bar_front', 'false');
}
add_action('user_register', 'vincent_hide_admin_bar_by_default');

// Force hide admin toolbar on the front end
function vincent_disable_admin_bar() {
    if (!is_admin()) {
        add_filter('show_admin_bar', '__return_false');
    }
}
add_action('after_setup_theme', 'vincent_disable_admin_bar');

// Hide WordPress Events and News from the dashboard
function vincent_remove_dashboard_meta() {
    remove_meta_box('dashboard_primary', 'dashboard', 'side');
}
add_action('admin_init', 'vincent_remove_dashboard_meta');

// Hide Activity from the dashboard
function vincent_remove_dashboard_activity() {
    remove_meta_box('dashboard_activity', 'dashboard', 'normal');
}
add_action('admin_init', 'vincent_remove_dashboard_activity');

// Hide Discussion Settings from the menu
function vincent_remove_discussion_settings() {
    remove_submenu_page('options-general.php', 'options-discussion.php');
}
add_action('admin_menu', 'vincent_remove_discussion_settings', 999);

// Set Discussion settings to disable comments
function vincent_disable_comments_settings() {
    // Default for new posts/pages
    update_option('default_comment_status', 'closed');
    update_option('default_ping_status', 'closed');

    // Already published posts/pages
    global $wpdb;
    $wpdb->query("
        UPDATE $wpdb->posts 
        SET comment_status = 'closed', ping_status = 'closed' 
        WHERE post_status = 'publish'
    ");
}
register_activation_hook(__FILE__, 'vincent_disable_comments_settings');

// Remove Quick Draft from the dashboard
function vincent_remove_dashboard_widgets() {
    remove_meta_box('dashboard_quick_press', 'dashboard', 'side');
}
add_action('wp_dashboard_setup', 'vincent_remove_dashboard_widgets' );

// Hide the 'About WordPress' icon in the admin bar
function vincent_remove_wp_logo_from_admin_bar() {
    global $wp_admin_bar;
    if (is_admin_bar_showing()) {
        $wp_admin_bar->remove_node('wp-logo');
    }
}
add_action('wp_before_admin_bar_render', 'vincent_remove_wp_logo_from_admin_bar');

// Hide 'Thank you for creating with WordPress' from the footer
function vincent_remove_footer_admin() {
    remove_filter('admin_footer_text', 'core_update_footer');
    add_filter('admin_footer_text', '__return_empty_string', 11);
}
add_action('admin_menu', 'vincent_remove_footer_admin');