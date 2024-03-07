# Social Media Post - Proof-of-Concept

## Project Description and Instructions
This project consists of a WordPress instance with a custom plugin and a Laravel backend instance. The WordPress plugin allows users to schedule and manage posts to a social media platform. At the moment, only Twitter available as a proof-of-concept. The Laravel backend handles the OAuth process with the social media platform and sends the posts.

### Prerequisites
- PHP 8.1^
- Docker / Docker Compose
- Twitter Developer Account

### Clone the repository
- `git clone https://github.com/jorge-mauricio/social-media-post.git`
- Open the directory `social-media-post` in the terminal or IDE terminal

## Twitter API Configuration
- Access [Twitter Developer Portal](https://developer.twitter.com/en/portal/dashboard)
Note: If you don't have an account, create one.
- Either create a new project or work in the project already created.
- Then, either create a new application or work in an existing application.
- In the user authentication settings, enable it if not already enabled.
        - App Permissions: Read & Write
        - Type of App: Web App
        - App info - Callback URI / Redirect URL: http://127.0.0.1:8000/auth/twitter/callback (or http://localhost:8000/auth/twitter/callback)
        - App info - Website URL: http://yourdomain.com
        - Save
- Then, on your App dashboard, click on "Keys and tokens";
- Generate (or regenerate) all keys and tokens and store them in a safe place. 

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

## Laravel Backend
The Laravel backend handles the OAuth process with the social media platform and sends the posts. It provides endpoints for redirecting the user to the social media platform for authentication just for validating and handling the callback from the OAuth process. The backend serving method was defined as with php artisan serve instead of docker, so we can observe how docker containers interact with endpoints outside docker.

### Installation
- `cd laravel-backend`
- Copy the `.env.example` file to `.env` (`cp .env.example .env`)
- Edit the `.env` file and set the `TWITTER_CONSUMER_KEY`, `TWITTER_CONSUMER_SECRET`, `TWITTER_ACCESS_TOKEN`, `TWITTER_ACCESS_TOKEN_SECRET`, `TWITTER_BEARER_TOKEN` environment variables to your Twitter API credentials you stored earlier.
- Edit the `.env` file with the database information it was defined in the WordPress instance (`DB_PASSWORD`).
- Run `composer install`
- Run `npm install`
- Run `php artisan key:generate` to generate an application key.
- Run `php artisan serve` to start the server.
- Check if the server is running on `http://127.0.0.1`.
Note: run `php artisan config:cache` on any .env variable value change. 

### Usage
In order to validate if the tokens are working correctly, we create a special endpoint to login with the tokens defined in the `.env` file.
- Access `http://127.0.0.1/auth/twitter`
- Proceed with the authentication.
- If successful, it should redirect you to a simple json response 200 page.
- If error, check and regenerate your tokens.
From this moment on, your WordPress instance is wired up with the Laravel Backend.

### Troubleshoot
- cURL certificate issue (windows):
If you come across an error such as: `error: cURL error 60: SSL certificate problem: unable to get local issuer certificate (see https://curl.haxx.se/libcurl/c/libcurl-errors.html) for https://api.twitter.com/oauth/request_token?oauth_callback=http%3A%2F%2F127.0.0.1%3A8000%2Fauth%2Ftwitter%2Fcallback`, follow the steps below:
This error can occur if cURL not being able to verify the certificate provided by the server. This is common when you're working in a local development environment.

One way to solve this issue is by downloading a file with the most common root certificates and tell PHP to use this file when making requests.

Here's how you can do it:
- Download the cacert.pem file from the curl website: https://curl.se/docs/caextract.html
- Save this file somewhere in your system, for example, C:\ssl\cacert.pem.
- Open your php.ini file. You can find this file by running php --ini in your terminal.
- Find the [curl] section in the php.ini file, and add the following line:
- Also find the [openssl] section and add the following line:
- Save the php.ini file and restart your server.
Remember to replace "C:\ssl\cacert.pem" with the actual path where you saved the cacert.pem file.
This should solve your issue. If you're still having problems, please let me know! O MacOS, this problem didn't seem to happen.
