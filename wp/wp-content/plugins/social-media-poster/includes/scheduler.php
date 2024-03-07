<?php
/**
 * Sends a scheduled post to a social media platform.
 *
 * This function retrieves the post data from the WordPress options,
 * sends a POST request to the social media platform's API, and updates
 * the post's status in the WordPress options based on the response.
 *
 * @param int $post_id The ID of the post to send.
 *
 * @return void
 */
function social_media_poster_send_scheduled_post($post_id) {
  $posts = get_option('social_media_poster_posts', []);
  if (isset($posts[$post_id])) {
      $post_data = $posts[$post_id];
      
      // Logic to send the post.
      $response = wp_remote_post(getenv('BACKEND_URI') . '/api/post/create', array(
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

/**
 * Checks for scheduled posts and sends them if it's time.
 *
 * This function retrieves the scheduled posts from the WordPress options,
 * checks if the current time is at or past the scheduled time, and sends
 * the post if it's time.
 *
 * @return void
 */
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
