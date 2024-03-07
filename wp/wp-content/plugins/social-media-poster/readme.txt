=== Social Media Poster Plugin for WordPress === 
Contributors: jorge-mauricio
Tags: social media, api integration
Requires at least: 5.0
Tested up to: 6.4
Stable tag: 5.1
License: GPLv3 or later

=== Description === 
The Social Media Poster is a WordPress plugin that allows users to schedule and manage posts to a social media platform. The plugin provides an interface in the WordPress admin dashboard where users can create new posts, edit existing posts, delete posts, and send posts immediately.

The plugin uses the WordPress Cron API to schedule posts, and it sends posts to the social media platform's API using the WordPress HTTP API. It also uses the Flatpickr date picker library for selecting the date and time to schedule posts.

=== Installation === 
- Download the plugin files and upload them to your WordPress plugins directory (usually wp-content/plugins/).
- Navigate to the Plugins page in your WordPress admin dashboard - Social Media Poster.
- Locate the Social Media Poster plugin and click the "Activate" link.

=== Usage === 
- After activating the plugin, navigate to the Social Media Poster page in your WordPress admin dashboard.
- To add a new post, fill out the form at the top of the page and click the "Create New" button.
- To edit an existing post, click the "Edit" link next to the post in the table. The form will be populated with the post's data. Make your changes and click the "Update Record" button.
- To delete a post, click the "Delete" button next to the post in the table.
- To send a post immediately, click the "Send Now" link next to the post in the table.

=== Configuration === 
The plugin requires the URL of the social media platform's API to be set as an environment variable named BACKEND_URI. This can be set in your server's configuration, or in a .env file if you're using a tool that supports it.

=== Error Handling === 
The plugin logs errors that occur when sending posts to the social media platform. If an error occurs, the status of the post will be set to -1. You can view the error log in the debug.log file in the wp-content directory.
