## WordPress

### Installation
- `cd wp`
- Copy the `.env.example` file to `.env` (`cp .env.example .env`)
- Edit the `.env` file and set the `MYSQL_DATABASE`, `MYSQL_USER`, `MYSQL_PASSWORD`, and `MYSQL_ROOT_PASSWORD` environment variables to your MySQL database credentials.
Note: You can set to whatever value, as the docker compose will create the database with those credentials.
Important: No need to change `BACKEND_URI` value, unless experimenting with another specific location.
- `docker compose up`
- access WordPress admin at `http://localhost:8080/wp-admin`
- Proceed with the WordPress installation
- Copy username and password for later use
- Log into WordPress
- Navigate to the Plugins page (sidebar) in your WordPress admin dashboard
- Locate the plugin Social Media Poster and click the "Activate" link
- Access the Social Media Poster plugin in your WordPress admin dashboard (sidebar)

#### phpMyAdmin
We also set up an container/endpoint for phpMyAdmin, in case it's needed.
- http://localhost:8081/

### Plugin Usage
The WordPress plugin provides an interface in the WordPress admin dashboard where users can create new posts, edit existing posts, delete posts, and send posts immediately or schedule. It uses the WordPress Cron API to schedule posts.
**Note:** It's not a perfect solution for scheduling posts, as it requires the WordPress site to receive traffic in order to execute the scheduled posts. A more robust solution would be to use a server-side cron job to trigger the WordPress Cron API (time constraints).

After activating the plugin, navigate to the plugin page in your WordPress admin dashboard.
- To add a new post, fill out the form at the top of the page and click the "Create New" button.
Note: There's a reference for the server's current time below the Post Content field, so the scheduling mechanism can be tested.
- To edit an existing post, click the "Edit" link next to the post in the table. The form will be populated with the post's data. Make your changes and click the "Update" button.
- To delete a post, click the "Delete" link next to the post in the table and confirm the alert window.
- To send a post immediately, click the "Send Now" link next to the post in the table.
- To test the scheduling, wait for the time to be after the scheduled time, reload the page and observe if the "Send Now" button is disabled and the status of the post and the post sent it the account it was configured to.
Note: In order to test this, Laravel Backend must be running.
