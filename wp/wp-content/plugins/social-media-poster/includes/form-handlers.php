<?php

/**
 * Handles form actions for the social media poster.
 *
 * This function checks if the current user has the 'manage_options' capability.
 * If they do, it handles the 'add_new_record', 'update_record', 'delete', and 'send_now' actions.
 * These actions add a new post, update an existing post, delete a post, and send a post immediately, respectively.
 *
 * @return void
 */
function social_media_poster_handle_form_actions() {
    if (!current_user_can('manage_options')) return;

    // Handle add new record
    if (isset($_POST['action']) && $_POST['action'] === 'add_new_record') {
        // Validate and sanitize inputs
        $title = sanitize_text_field($_POST['post_title']);
        $content = sanitize_textarea_field($_POST['post_content']);
        $schedule = sanitize_text_field($_POST['schedule']); // Ensure this is validated properly as a date

        // Retrieve existing posts, add new one, and update option
        $posts = get_option('social_media_poster_posts', []);
        $posts[] = [
            'title' => $title,
            'content' => $content,
            'schedule' => $schedule,
            'status' => 'Scheduled' // Default status
        ];
        update_option('social_media_poster_posts', $posts);

        // Redirect to prevent form resubmission
        wp_redirect(add_query_arg('page', 'social-media-poster', admin_url('admin.php')));
        exit;
    }

    if (isset($_POST['action']) && $_POST['action'] === 'update_record') {
        // Make sure to fetch the current posts array first
        $posts = get_option('social_media_poster_posts', []);
        
        // Check if 'edit_index' is set and is a number. This handles index 0 correctly.
        if (isset($_POST['edit_index']) && ($_POST['edit_index'] !== "")) {
            $edit_index = intval($_POST['edit_index']);
            
            if (isset($posts[$edit_index])) { // Check if the post exists in the array
                $posts[$edit_index] = [
                    'title' => sanitize_text_field($_POST['post_title']),
                    'content' => sanitize_textarea_field($_POST['post_content']),
                    'schedule' => sanitize_text_field($_POST['schedule']),
                    'status' => $posts[$edit_index]['status'] // Keep the original status
                ];
                update_option('social_media_poster_posts', $posts);

                wp_redirect(add_query_arg('page', 'social-media-poster', admin_url('admin.php')));
                exit;
            } else {
                // Handle the case where the post doesn't exist at the index.
                throw new Exception('An error occurred while processing the form.');
            }
        }
    }

    // Handle delete record
    if (isset($_GET['delete'])) {
        $delete_index = intval($_GET['delete']); // Sanitize the input as an integer

        // Retrieve the existing posts
        $posts = get_option('social_media_poster_posts', []);

        // Check if the post to delete exists
        if (isset($posts[$delete_index])) {
            // Remove the post from the array
            array_splice($posts, $delete_index, 1);

            // Update the option with the modified array
            update_option('social_media_poster_posts', $posts);

            // Redirect to prevent resubmission
            wp_redirect(add_query_arg('page', 'social-media-poster', admin_url('admin.php')));
            exit;
        }
    }

    if (isset($_GET['action']) && $_GET['action'] === 'send_now' && isset($_GET['index'])) {
        $index = intval($_GET['index']);
        $posts = get_option('social_media_poster_posts', []);

        if (isset($posts[$index])) {
            $post_data = $posts[$index];
            $response = wp_remote_post(getenv('BACKEND_URI') . '/api/post/create', array(
                'body' => array('text' => $post_data['content']),
                'timeout' => '45',
                'headers' => array('Content-Type' => 'application/x-www-form-urlencoded; charset=utf-8'),
            ));

            if (is_wp_error($response)) {
                $error_message = $response->get_error_message();
                $posts[$index]['status'] = -1; // Mark as error
            } else {
                $body = wp_remote_retrieve_body($response);
                $data = json_decode($body, true);

                if ($data['status'] === true) {
                    $posts[$index]['status'] = 1; // Mark as sent
                } else {
                    $posts[$index]['status'] = -1; // Mark as error
                }
            }

            update_option('social_media_poster_posts', $posts);
            wp_redirect(add_query_arg('page', 'social-media-poster', admin_url('admin.php')));
            exit;
        }
    }
}
