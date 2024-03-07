<?php

/**
 * Enqueues scripts and styles for the Social Media Poster plugin.
 *
 * This function is hooked into the WordPress 'admin_enqueue_scripts' action and is called
 * whenever scripts or styles need to be enqueued in the WordPress admin. It checks if the
 * current page is the Social Media Poster plugin page, and if it is, it enqueues the Flatpickr
 * date picker library and a custom JavaScript file.
 *
 * @param string $hook The current admin page.
 *
 * @return void
 */
function social_media_poster_enqueue_scripts($hook) {
    // Check if we're on the plugin's page
    if($hook != 'toplevel_page_social-media-poster') {
        return;
    }

    // Enqueue Flatpickr CSS
    wp_enqueue_style('flatpickr-style', 'https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css');

    // Enqueue Flatpickr JS
    wp_enqueue_script('flatpickr-script', 'https://cdn.jsdelivr.net/npm/flatpickr', array(), false, true);

    // Enqueue your custom JS for initialization (explained in the next step)
    wp_enqueue_script('social-media-poster-script', plugins_url('../js/social-media-poster.js', __FILE__), array('flatpickr-script'), false, true);
}
