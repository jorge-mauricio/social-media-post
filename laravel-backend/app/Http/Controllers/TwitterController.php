<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Atymic\Twitter\Twitter as TwitterContract;
use Illuminate\Support\Facades\Http;

class TwitterController extends Controller
{
    private $twitter;

    public function __construct(TwitterContract $twitter)
    {
        $this->twitter = $twitter;
    }

    /**
     * Redirects the user to Twitter for authentication.
     *
     * This function uses the TwitterOAuth library to obtain a request token from Twitter.
     * It then redirects the user to Twitter's authentication page, passing the request token as a parameter.
     * The request token and request token secret are also stored in the session for later use.
     *
     * @throws \Exception If an error occurs while obtaining the request token.
     *
     * @return \Illuminate\Http\RedirectResponse A redirect response object that can be returned from a controller.
     */
    public function redirectToTwitter(): \Illuminate\Http\RedirectResponse
    {
        $authCallback = config('twitter.callback_url');

        try {
            $response = $this->twitter->usingCredentials(
                config('twitter.access_token'),
                config('twitter.access_token_secret'),
                config('twitter.consumer_key'),
                config('twitter.consumer_secret')
            )
            ->getRequestToken($authCallback);

            $url = config('twitter.authenticate_url') . '?oauth_token=' . $response['oauth_token'];

            // Store the oauth_token and oauth_token_secret in the session, or wherever you want
            session(['oauth_token' => $response['oauth_token']]);
            session(['oauth_token_secret' => $response['oauth_token_secret']]);
        } catch (\Exception $e) {
            // Handle errors, such as logging or displaying a message
            return redirect()->route('twitter.error')->withErrors('Failed to obtain access tokens from Twitter');
        }

        return redirect($url);
    }

    /**
     * Handles the callback from Twitter's OAuth process.
     *
     * Just for testing/debugging and checking if the OAuth process is working.
     *
     * @param Request $request The request object, which should contain the OAuth token and verifier as query parameters.
     *
     * @throws \Exception If an error occurs while exchanging the OAuth verifier for access tokens.
     *
     * @return \Illuminate\Http\JsonResponse A JSON response that indicates whether the authentication process was successful.
     */
    public function handleProviderCallback(Request $request): \Illuminate\Http\JsonResponse
    {
        $oauthToken = $request->query('oauth_token');
        $oauthVerifier = $request->query('oauth_verifier');

        if (!$oauthToken || !$oauthVerifier) {
            return redirect()->route('twitter.error')->withErrors('Invalid OAuth response from Twitter');
        }

        // Ensure the oauth_token matches the one stored in the session.
        if ($oauthToken !== session('oauth_token')) {
            return redirect()->route('twitter.error')->withErrors('Invalid OAuth response from Twitter');
        }

        try {
            $tokenCredentials = $this->twitter->usingCredentials(session('oauth_token'), session('oauth_token_secret'))
            ->getAccessToken($oauthVerifier);

            session([
                'twitter_access_token' => $tokenCredentials['oauth_token'],
                'twitter_token_secret' => $tokenCredentials['oauth_token_secret'],
            ]);

            return response()->json(['status' => 'OAuth Success'], 200);
        } catch (\Exception $e) {
            return response()->json(['status' => 'Error authenticating'], 500);
        }
    }

    /**
     * Posts a tweet to Twitter.
     *
     * This function uses the TwitterOAuth library to post a tweet to Twitter.
     * It uses the user's access token and access token secret, which should be stored securely.
     *
     * @param Request $request The request object, which should contain the tweet text as a parameter.
     *
     * @return \Illuminate\Http\JsonResponse A JSON response that indicates whether the tweet was posted successfully.
     */
    public function showError(Request $request): \Illuminate\Http\JsonResponse
    {
        // Get all error messages
        $errors = $request->session()->get('errors');

        // Return a view and pass the error messages to it
        return response()->json(['errors' => $errors], 500);
    }
}
