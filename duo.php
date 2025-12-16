<?php
/*
Plugin Name: Duo
Description: Minimal plugin that adds a single admin page.
Version: 1.0.0
Author: Duo
*/

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * Register a single top-level admin page for Duo.
 */
function duo_register_admin_page() {
    add_menu_page(
        'Duo',               // Page title
        'Duo',               // Menu title
        'manage_options',    // Capability
        'duo-admin',         // Menu slug
        'duo_render_admin_page', // Callback
        'dashicons-admin-generic', // Icon
        80                   // Position
    );
}
add_action( 'admin_menu', 'duo_register_admin_page' );

/**
 * Render the Duo admin page content.
 */
function duo_render_admin_page() {
    if ( ! current_user_can( 'manage_options' ) ) {
        return;
    }

    echo '<div class="wrap">';
    echo '<h1>Duo</h1>';
    echo '<p>This is a minimal admin page provided by the Duo plugin.</p>';
    echo '</div>';
}
