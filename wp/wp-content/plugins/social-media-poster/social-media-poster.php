<?php
/**
 * Plugin Name: Social Media Poster
 * Description: Posts content from WordPress to a social media platform via a Laravel API.
 * Version: 1.0
 * Author: JM - Jorge Mauricio
 */

add_action('admin_menu', 'social_media_poster_menu');

function social_media_poster_menu() {
    add_menu_page('Social Media Poster Settings', 'Social Media Poster', 'manage_options', 'social-media-poster', 'social_media_poster_settings_page');
}

add_action('admin_init', 'social_media_poster_handle_form_actions');

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
    wp_enqueue_script('social-media-poster-script', plugins_url('/js/social-media-poster.js', __FILE__), array('flatpickr-script'), false, true);
}
add_action('admin_enqueue_scripts', 'social_media_poster_enqueue_scripts');


function social_media_poster_schedule_post($post_id, $schedule_timestamp) {
    if (! wp_next_scheduled('social_media_poster_send_post', array($post_id))) {
        wp_schedule_single_event($schedule_timestamp, 'social_media_poster_send_post', array($post_id));
    }
}

add_action('social_media_poster_send_post', 'social_media_poster_send_scheduled_post');

function social_media_poster_send_scheduled_post($post_id) {
    $posts = get_option('social_media_poster_posts', []);
    if (isset($posts[$post_id])) {
        $post_data = $posts[$post_id];
        
        // Logic to send the post.
        $response = wp_remote_post('http://host.docker.internal:8000/api/post/create', array(
            'body' => array('text' => $post_data['content']),
            'timeout' => '45',
            'headers' => array('Content-Type' => 'application/x-www-form-urlencoded; charset=utf-8'),
        ));

        if (is_wp_error($response)) {
            // Handle error, log it or update post status to -1 (error)
            $posts[$post_id]['status'] = -1;
        } else {
            $body = wp_remote_retrieve_body($response);
            $data = json_decode($body, true);
            if ($data && $data['status'] === true) {
                // Update post status to 1 (sent) if successful
                $posts[$post_id]['status'] = 1;
            } else {
                // Handle error, log it or update post status to -1 (error)
                $posts[$post_id]['status'] = -1;
            }
        }

        // Update the posts option with the new status
        update_option('social_media_poster_posts', $posts);
    }
}

function social_media_poster_schedule_post_check() {
    $posts = get_option('social_media_poster_posts', []);
    $current_time = current_time('timestamp'); // WordPress function that respects site's timezone settings

    foreach ($posts as $index => $post) {
        // Check if the post's status is 'Scheduled' and the current time is at or past the scheduled time
        if ($post['status'] === 'Scheduled') {
            $schedule_time = strtotime($post['schedule']); // Convert schedule string to Unix timestamp
            if ($current_time >= $schedule_time) {
                social_media_poster_send_scheduled_post($index); // Send post if it's time
            }
        }
    }
}

// Hook this function to run at a regular interval or upon certain actions
add_action('wp_loaded', 'social_media_poster_schedule_post_check');

function social_media_poster_handle_form_actions() {
    if (!current_user_can('manage_options')) return;

    // Example: Handle add new record
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
                // You might want to add an error message or handle this scenario differently.
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
            // $response = wp_remote_post('http://127.0.0.1:8000/api/post/create', array(
            $response = wp_remote_post('http://host.docker.internal:8000/api/post/create', array(
                'body' => array('text' => $post_data['content']),
                'timeout' => '45',
                'headers' => array('Content-Type' => 'application/x-www-form-urlencoded; charset=utf-8'),
            ));
            // var_dump('response=');
            // var_dump($response);

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



// Note: we can either set the settings in the laravel .env or create a settings configuration in the plugin for the secrets



function social_media_poster_settings_page() {
    $edit_mode = isset($_GET['edit']);
    $edit_index = $edit_mode ? intval($_GET['edit']) : null;
    $posts = get_option('social_media_poster_posts', []);
    $current_post = $edit_mode && isset($posts[$edit_index]) ? $posts[$edit_index] : null;
    
    // Adjust the form action and button label based on edit mode
    $form_action = $edit_mode ? "update_record" : "add_new_record";
    $button_label = $edit_mode ? "Update" : "Include";

    ?>
    <div class="wrap">
    
        <h1>Social Media Poster</h1>

        <h2><?php echo $edit_mode ? 'Edit Record' : 'Add New Record'; ?></h2>
        <div class="wrap" style="max-width: 500px;">
            <h1>Social Media Poster</h1>
            <form method="post" action="">
                <input type="hidden" name="action" value="<?php echo $form_action; ?>">
                <?php if ($edit_mode): ?>
                    <input type="hidden" name="edit_index" value="<?php echo $edit_index; ?>">
                <?php endif; ?>
                <div style="display: flex; justify-content: space-between; margin-bottom: 20px;">
                    <div style="flex-basis: 48%;">
                        <p>
                            <label>Post Title</label><br>
                            <input type="text" name="post_title" value="<?php echo $edit_mode ? esc_attr($current_post['title']) : ''; ?>" required style="width: 100%;">
                        </p>
                    </div>
                    <div style="flex-basis: 48%;">
                        <p>
                            <label>Schedule (YYYY-MM-DD HH:MM)</label><br>
                            <input type="text" name="schedule" id="schedule" value="<?php echo $edit_mode ? esc_attr($current_post['schedule']) : ''; ?>" required style="width: 100%;">
                        </p>
                    </div>
                </div>
                <p>
                    <label>Post Content</label><br>
                    <textarea name="post_content" required style="width: 100%; height: 150px;"><?php echo $edit_mode ? esc_textarea($current_post['content']) : ''; ?></textarea>
                </p>
                <p>
                    Current Server Time: <?php echo date('Y-m-d H:i'); ?>
                </p>
                <p>
                    <input type="submit" class="button-primary" value="<?php echo $button_label; ?>">
                    <?php if ($edit_mode): ?>
                        <a href="?page=social-media-poster" class="button-secondary">Cancel</a>
                    <?php endif; ?>
                </p>
            </form>
        </div>

        <h2>Posts</h2>
        <table class="wp-list-table widefat fixed striped">
            <thead>
                <tr>
                    <th>Id</th>
                    <th>Post Title</th>
                    <th>Post Content</th>
                    <th>Scheduled Date/Time</th>
                    <th>Status</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
                <?php
                // Example of fetching records. Replace with actual data retrieval logic.
                $posts = get_option('social_media_poster_posts', []); // Assuming posts are stored as an array of arrays in a wp_option for simplicity.
                foreach ($posts as $index => $post) {
                    $statusText = '';
                    switch($post['status']) {
                        case 0:
                            $statusText = 'Scheduled';
                            break;
                        case 1:
                            $statusText = 'Sent';
                            break;
                        case -1:
                            $statusText = 'Error';
                            break;
                    }

                    echo "<tr>
                            <td>{$index}</td>
                            <td>{$post['title']}</td>
                            <td>{$post['content']}</td>
                            <td>{$post['schedule']}</td>
                            <!--td>{$post['status']}</td-->
                            <td>{$statusText}</td>
                            <td>
                                <!--a href='?page=social-media-poster&send={$index}' class='button'>Send Now</a-->
                                <a href='?page=social-media-poster&action=send_now&index={$index}' class='button'".($post['status'] == 1 ? " disabled='disabled'" : "").">Send Now</a>
                                <a href='?page=social-media-poster&edit={$index}' class='button'>Edit</a>
                                <a href='?page=social-media-poster&delete={$index}' class='button' onclick='return confirm(\"Are you sure you want to delete this record?\")'>Delete</a>
                            </td>
                          </tr>";
                }
                ?>
            </tbody>
        </table>

    </div>
    <?php
}

// TODO:
// 0 - scheduled (and in the table, print Scheduled)
// -1 - error (and in the table, print error)
// 1 - sent (and in the table, print Sent)

