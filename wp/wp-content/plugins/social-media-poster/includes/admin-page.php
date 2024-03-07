<?php

/**
 * Adds a new menu page to the WordPress admin dashboard.
 *
 * This function uses the WordPress function `add_menu_page` to add a new menu page
 * for the Social Media Poster plugin. The page is titled "Social Media Poster Settings",
 * and the menu title is "Social Media Poster". The page is only visible to users with
 * the 'manage_options' capability.
 *
 * @return void
 */
function social_media_poster_menu() {
  add_menu_page('Social Media Poster Settings', 'Social Media Poster', 'manage_options', 'social-media-poster', 'social_media_poster_settings_page');
}

/**
 * Renders the settings page for the Social Media Poster.
 *
 * This function displays a form that allows the user to add a new post or edit an existing post.
 * It also displays a table of all existing posts, with options to edit, delete, or send each post.
 *
 * @return void
 */
function social_media_poster_settings_page() {
  $edit_mode = isset($_GET['edit']);
  $edit_index = $edit_mode ? intval($_GET['edit']) : null;
  $posts = get_option('social_media_poster_posts', []);
  $current_post = $edit_mode && isset($posts[$edit_index]) ? $posts[$edit_index] : null;
  
  // Adjust the form action and button label based on edit mode
  $form_action = $edit_mode ? "update_record" : "add_new_record";
  $button_label = $edit_mode ? "Update" : "Create New";

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
                      case 2:
                          $statusText = 'Fail';
                          break;
                  }

                  echo "<tr>
                          <td>{$index}</td>
                          <td>{$post['title']}</td>
                          <td>{$post['content']}</td>
                          <td>{$post['schedule']}</td>
                          <td>{$statusText}</td>
                          <td>
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
