<?php

namespace App\Http\Controllers;

use Facebook\Facebook;
use Illuminate\Http\Request;
// use Facebook\Exceptions\FacebookResponseException;
// use Facebook\Exceptions\FacebookSDKException;

class SocialAuthController extends Controller
{
    protected $facebook;

    public function __construct()
    {
        $this->facebook = new Facebook([
            'app_id' => env('FACEBOOK_APP_ID'),
            'app_secret' => env('FACEBOOK_APP_SECRET'),
            'default_graph_version' => 'v2.10',
        ]);
    }

    public function facebookAuth()
    {
        $helper = $this->facebook->getRedirectLoginHelper();
        $permissions = ['email']; // Optional permissions
        $loginUrl = $helper->getLoginUrl(env('FACEBOOK_REDIRECT_URI'), $permissions);

        return redirect()->to($loginUrl);
    }

    public function callback(Request $request)
    {
        $helper = $this->facebook->getRedirectLoginHelper();

        try {
            $accessToken = $helper->getAccessToken();
            } catch(\Facebook\Exceptions\FacebookResponseException $e) {
            // When Graph returns an error
            echo 'Graph returned an error: ' . $e->getMessage();
            exit;
            } catch(\Facebook\Exceptions\FacebookSDKException $e) {
            // When validation fails or other local issues
            echo 'Facebook SDK returned an error: ' . $e->getMessage();
            exit;
        }

        if (! isset($accessToken)) {
            if ($helper->getError()) {
                // Handle errors and log them
                // Error details: $helper->getErrorReason(), $helper->getErrorDescription()
            } else {
                // Generic error message
                echo 'Bad request';
            }
            exit;
        }

        // Logged in
        // Now you can redirect the user to another page or perform actions with their Facebook data
        // Remember to securely handle and store the access token
    }
}
