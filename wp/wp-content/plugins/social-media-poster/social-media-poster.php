<?php
/**
 * Plugin Name: Social Media Poster
 * Description: Posts content from WordPress to a social media platform via a Laravel API.
 * Version: 1.0
 * Author: JM - Jorge Mauricio
 */

// Development notes (compromises due to time constraints):
// - Ideally, this would be coded in an OOP manner, but for simplicity, we'll use procedural code.
// - Send button should function with an AJAX request.


// Define plugin constants
define( 'SMP_PATH', plugin_dir_path( __FILE__ ) );
define( 'SMP_URL', plugin_dir_url( __FILE__ ) );

// Require necessary files
require_once SMP_PATH . 'includes/enqueue-scripts.php';
require_once SMP_PATH . 'includes/form-handlers.php';
require_once SMP_PATH . 'includes/scheduler.php';
require_once SMP_PATH . 'includes/admin-page.php';

// Hook into WordPress
add_action( 'admin_menu', 'social_media_poster_menu' );
add_action( 'admin_enqueue_scripts', 'social_media_poster_enqueue_scripts' );
add_action( 'admin_init', 'social_media_poster_handle_form_actions' );
add_action( 'social_media_poster_send_post', 'social_media_poster_send_scheduled_post' );
add_action( 'wp_loaded', 'social_media_poster_schedule_post_check' );
