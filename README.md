# Social Media Post - Proof-of-Concept

## Project Description and Instructions
This project consists of a WordPress terminal with a custom plugin and a Laravel backend. The WordPress plugin allows users to schedule and manage posts to a social media platform. At the moment, only Twitter available for a proof-of-concept. The Laravel backend handles the OAuth process with the social media platform and sends the posts.

### Clone the repository
- `git clone https://github.com/jorge-mauricio/social-media-post.git`

## WordPress Plugin
The WordPress plugin provides an interface in the WordPress admin dashboard where users can create new posts, edit existing posts, delete posts, and send posts immediately or schedule. It uses the WordPress Cron API to schedule posts.
**Note:** It's not a perfect solution for scheduling posts, as it requires the WordPress site to receive traffic in order to execute the scheduled posts. A more robust solution would be to use a server-side cron job to trigger the WordPress Cron API (time constraints).

### Installation
- `cd social-media-post/wp`
- Copy the `.env.example` file to `.env`
- Edit the `.env` file and set the `DB_NAME`, `DB_USER`, `DB_PASSWORD`, and `DB_HOST` environment variables to your MySQL database credentials.
Important: (TODO)
- `docker compose up`
- access WordPress admin at `http://localhost:8080/wp-admin`
- Proceed with the WordPress installation
- Navigate to the Plugins page in your WordPress admin dashboard
- Locate the plugin and click the "Activate" link
- Access the Social Media Poster plugin in your WordPress admin dashboard


### Usage
After activating the plugin, navigate to the plugin page in your WordPress admin dashboard.
- -To add a new post, fill out the form at the top of the page and click the "Add New Record" button.
- To edit an existing post, click the "Edit" link next to the post in the table. The form will be populated with the post's data. Make your changes and click the "Update Record" button.
- To delete a post, click the "Delete" link next to the post in the table.
- To send a post immediately, click the "Send Now" link next to the post in the table.

## Laravel Backend
The Laravel backend handles the OAuth process with the social media platform and sends the posts. It provides endpoints for redirecting the user to the social media platform for authentication just for validating and handling the callback from the OAuth process.

### Installation
- `cd laravel-backend`
- Copy the `.env.example` file to `.env`
- Edit the `.env` file and set the `TWITTER_CONSUMER_KEY` and `TWITTER_CONSUMER_SECRET` environment variables to your Twitter API credentials.
TODO: complete the installation instructions
- Run `composer install`
- Run `npm install`
- Run `php artisan key:generate` to generate an application key.
- Run `php artisan serve` to start the server.

### Usage
To start the OAuth process, send a GET request to the `http://127.0.0.1/auth/twitter` endpoint. This will redirect the user to the social media platform for authentication.


#### WordPress
DB_NAME: The name of the WordPress database.
DB_USER: The username for accessing the database.
DB_PASSWORD: The password for accessing the database.
DB_HOST: The hostname of the database server.
WP_DEBUG: Whether to enable debugging mode. Set to true to enable.

#### Laravel
APP_KEY: The application key. This can be generated with php artisan key:generate.
APP_DEBUG: Whether to enable debugging mode. Set to true to enable.
APP_URL: The URL of the application.
DB_CONNECTION: The database connection type. Set to mysql for a MySQL database.
DB_HOST: The hostname of the database server.
DB_PORT: The port number of the database server.
DB_DATABASE: The name of the database.
DB_USERNAME: The username for accessing the database.
DB_PASSWORD: The password for accessing the database.

