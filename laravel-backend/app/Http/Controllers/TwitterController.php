<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Atymic\Twitter\Facade\Twitter;
// use Twitter;
// use Thujohn\Twitter\Twitter;
use Atymic\Twitter\Twitter as TwitterContract;
use Illuminate\Support\Facades\Http;

class TwitterController extends Controller
{
    private $twitter;

    public function __construct(TwitterContract $twitter)
    {
        $this->twitter = $twitter;
    }

    // public function redirectToTwitter()
    // {
    //     $temporaryCredentials = $this->twitter->getTemporaryCredentials();
    //     $url = Twitter::getAuthorizationUrl($temporaryCredentials);

    //     // Store temporary credentials in the session, or wherever you want
    //     session(['temporary_credentials' => serialize($temporaryCredentials)]);

    //     return redirect($url);
    // }

    // http://127.0.0.1:8000/auth/twitter
    public function redirectToTwitter()
    {
        $query = [
            'oauth_callback' => 'http://127.0.0.1:8000/auth/twitter/callback'
        ];
        $authCallback = 'http://127.0.0.1:8000/auth/twitter/callback';

        $response = $this->twitter->usingCredentials(
            config('twitter.access_token'),
            config('twitter.access_token_secret'),
            config('twitter.consumer_key'),
            config('twitter.consumer_secret')
        )
        ->getRequestToken($authCallback);
        // ->getRequestToken($query);

        // Debug.
        // echo 'response=';
        // dump($response);
        // dd();

        // $response = $this->twitter->getRequestToken(
        //     config('twitter.credentials.access_token'),
        //     config('twitter.credentials.access_token_secret'),
        //     config('twitter.credentials.consumer_key'),
        //     config('twitter.credentials.consumer_secret')
        // );

        // $url = $response->getOAuthRequestTokenUrl();
        // $url = $response['oauth_callback_confirmed'] ? $response['url'] : null;
        $url = 'https://api.twitter.com/oauth/authenticate?oauth_token=' . $response['oauth_token'];


        // Store the oauth_token and oauth_token_secret in the session, or wherever you want
        session(['oauth_token' => $response['oauth_token']]);
        session(['oauth_token_secret' => $response['oauth_token_secret']]);


        // TODO: create try-catch block to handle errors and message to regenerate the token


        return redirect($url);
    }

    // public function redirectToProvider()
    // {
    //     $signInTwitter = true; // Whether to force the user to sign in again
    //     $token = Twitter::getRequestToken(route('twitter.callback'));

    //     if (isset($token['oauth_token_secret'])) {
    //         session(['oauth_state' => 'start']);
    //         session(['oauth_request_token' => $token['oauth_token']]);
    //         session(['oauth_request_token_secret' => $token['oauth_token_secret']]);

    //         return redirect(Twitter::getAuthorizeURL($token, $signInTwitter));
    //         // return redirect(Twitter::getAuthorizationUrl($token, $signInTwitter));
    //         // return redirect(Twitter::getAuthorizeURL($token));
    //     }

    //     return redirect()->route('twitter.error'); // Redirect to a route if there's an error
    // }

    // public function redirectToProvider()
    // {
    //     // Assuming the package provides a new way to initiate OAuth and get the authorization URL
    //     // This is a pseudo-code based on common OAuth flow implementations
    //     $oauthToken = Twitter::oauth('oauth/request_token', ['oauth_callback' => route('twitter.callback')]);

    //     if (isset($oauthToken['oauth_token_secret'])) {
    //         session(['oauth_state' => 'start']);
    //         session(['oauth_request_token' => $oauthToken['oauth_token']]);
    //         session(['oauth_request_token_secret' => $oauthToken['oauth_token_secret']]);

    //         $authorizeUrl = Twitter::oauth('oauth/authorize', ['oauth_token' => $oauthToken['oauth_token']]);
    //         return redirect($authorizeUrl);
    //     }

    //     return redirect()->route('twitter.error');
    // }

    // public function redirectToProvider()
    // {
    //     // The newer versions might use a different approach for initiating OAuth.
    //     // This example assumes a simplified OAuth flow.
    //     try {
    //         $authenticationUrl = Twitter::getOauthUrl(route('twitter.callback'));
    //         return redirect($authenticationUrl);
    //     } catch (\Exception $e) {
    //         // Log the error or handle it as per your application's needs
    //         return redirect()->route('twitter.error')->withErrors('Failed to connect to Twitter');
    //     }
    // }

    // public function handleProviderCallback()
    // {
    //     if (session('oauth_state') !== 'start' || !request()->has('oauth_token')) {
    //         session()->forget('oauth_state');
    //         return redirect()->route('twitter.error'); // Redirect to a route if there's an error
    //     }

    //     $token = Twitter::getAccessToken(request('oauth_verifier'));

    //     if (!isset($token['oauth_token_secret'])) {
    //         return redirect()->route('twitter.error'); // Redirect to a route if there's an error
    //     }

    //     session(['oauth_state' => 'done']);
    //     session(['oauth_request_token' => $token['oauth_token']]);
    //     session(['oauth_request_token_secret' => $token['oauth_token_secret']]);

    //     // At this point, you have authenticated with Twitter. You can store the tokens in your database associated with the user's account.

    //     return redirect()->route('your.home.route'); // Redirect to your desired route after successful authentication
    // }

    // http://127.0.0.1:8000/auth/twitter
    public function handleProviderCallback(Request $request)
    {
        $oauthToken = $request->query('oauth_token');
        $oauthVerifier = $request->query('oauth_verifier');

        // Debug.
        // echo 'oauthToken=';
        // dump($oauthToken);
        // echo 'oauth_token(session)=';
        // dump(session('oauth_token'));
        // echo 'oauthVerifier=';
        // dump($oauthVerifier);
        // dd();
        // $tokenCredentials = Twitter::getAccessToken($oauthVerifier);
        // $tokenCredentials = Twitter::getAccessToken($oauthVerifier, $oauthToken);
        // echo 'tokenCredentials=';
        // dump($tokenCredentials);

        // $tokenCredentials = $this->twitter->usingCredentials(session('oauth_token'), session('oauth_token_secret'))
        // ->getAccessToken($oauthVerifier);
        // echo 'tokenCredentials=';
        // dump($tokenCredentials); // working
        // dd();

        if (!$oauthToken || !$oauthVerifier) {
            return redirect()->route('twitter.error')->withErrors('Invalid OAuth response from Twitter');
        }

        // Ensure the oauth_token matches the one stored in the session.
        if ($oauthToken !== session('oauth_token')) {
            // dd('OAuth token mismatch');
            // TODO: Log the error or handle it as per your application's needs

            // return redirect()->route('twitter.error')->withErrors('OAuth token mismatch');
        }

        try {
            // Exchange the OAuth verifier for access tokens.
            // $tokenCredentials = Twitter::getAccessToken($oauthVerifier);

            $tokenCredentials = $this->twitter->usingCredentials(session('oauth_token'), session('oauth_token_secret'))
            ->getAccessToken($oauthVerifier);

            // Debug.
            // echo 'tokenCredentials=';
            // dump($tokenCredentials);
            // dd();

            // Store the token credentials in the session or database as needed.
            // session([
            //     'twitter_access_token' => $tokenCredentials->getToken(),
            //     'twitter_token_secret' => $tokenCredentials->getSecret(),
            // ]);
            // Store the token credentials in the session or database as needed.
            session([
                'twitter_access_token' => $tokenCredentials['oauth_token'],
                'twitter_token_secret' => $tokenCredentials['oauth_token_secret'],
            ]);

            // Post a tweet or perform other actions with the authenticated user's token credentials.
            // $this->postTweet($request);
            if ($this->postTweet($request)) {
            // if ($this->postTweetV2($request)) {

                // The tweet was successfully posted
                return response()->json(['status' => 'OAuth Success'], 200);
            } else {
                // The tweet was not posted
                return response()->json(['status' => 'OAuth Success, but error posting message'], 200);
            }

            // return redirect()->route('your.home.route');
            // return response()->json(['status' => 'OAuth Success'], 200);
        } catch (\Exception $e) {
            // Handle errors, such as logging or displaying a message
            return redirect()->route('twitter.error')->withErrors('Failed to obtain access tokens from Twitter');

            // Error message

            // Whoa there!

            // The request token for this page is invalid. It may have already been used, or expired because it is too old. Please go back to the site or application that sent you here and try again; it was probably just a mistake.

        }
    }

    // public function postTweet()
    // {
    //     $status = 'This is a tweet posted from my Laravel application.';
    //     Twitter::postTweet(['status' => $status, 'format' => 'json']);

    //     return redirect()->back()->with('message', 'Tweet successfully posted!');
    // }

    // public function postTweet(Request $request)
    // {
    //     $status = $request->input('status', 'This is a tweet posted from my Laravel application.');

    //     try {
    //         // Ensure you're setting the access token and secret from the user's session or database.
    //         Twitter::reconfig(['token' => session('twitter_access_token'), 'secret' => session('twitter_token_secret')]);
    //         Twitter::postTweet(['status' => $status]);

    //         // return back()->with('message', 'Tweet successfully posted!');
    //         return response()->json(['status' => 'Message posted'], 200);

    //     } catch (\Exception $e) {
    //         // Handle any errors, such as logging or displaying an error message
    //         // return back()->withErrors('Failed to post tweet');
    //         return response()->json([
    //             'status' => 'error posting message',
    //             'error' => $e
    //         ], 500);
    //     }
    // }

    // http://127.0.0.1:8000/auth/twitter
    // public function postTweet(Request $request)
    // {
    //     $status = $request->input('status', 'This is a tweet posted from my Laravel application.');
    //     // dd('in postTweet');
    //     dump('in postTweet');

    //     try {
    //         // Get the Twitter service from the service container
    //         $twitter = app('twitter');

    //         dump('twitter(1)=');
    //         dump($twitter);

    //         // Create a new instance with the user's access token and secret
    //         $twitter = $twitter->usingCredentials(session('twitter_access_token'), session('twitter_token_secret'));

    //         dump('twitter(2)=');
    //         dump($twitter);

    //         // Post the tweet
    //         $twitter->postTweet(['status' => $status]);

    //         dump('twitter(3)=');
    //         dump($twitter);

    //         return response()->json(['status' => 'Message posted'], 200);
    //         return true;

    //     } catch (\Exception $e) {

    //         // return response()->json([
    //         //     'status' => 'error posting message',
    //         //     'error' => $e->getMessage()
    //         // ], 500);

    //         dump('error posting message');
    //         dump($e->getMessage());

    //         // TODO: handle this access level error:
    //         # [453] You currently have access to a subset of Twitter API v2 endpoints and limited v1.1 endpoints (e.g. media post, oauth) only. If you need access to this endpoint, you may need a different access level. You can learn more here: https://developer.twitter.com/en/portal/product


    //         return false;
    //     }
    // }

    // http://127.0.0.1:8000/auth/twitter
    public function postTweet(Request $request)
    {
        $status = $request->input('status', 'This is a tweet posted from my Laravel application using API v2.');

        // Ensure you have correctly set these tokens in your .env file and loaded them in your config/twitter.php
        // $accessToken = config('twitter.access_token');
        // $accessTokenSecret = config('twitter.access_token_secret');

        try {
            // Configure the Twitter client with the user's access token and secret for OAuth 1.0a user context
            $twitter = $this->twitter->usingCredentials(
                config('twitter.access_token'),
                config('twitter.access_token_secret'),
                config('twitter.consumer_key'),
                config('twitter.consumer_secret')
            );

            // dump('twitter=');
            // dump($twitter[]);

            // Post the tweet
            $response = $twitter->postTweet(['status' => $status]);

            // $uploaded_media = Twitter::uploadMedia(['media' => File::get(public_path('filename.jpg'))]);
            // return Twitter::postTweet(['status' => 'Laravel is beautiful', 'media_ids' => $uploaded_media->media_id_string]);

            if (isset($response['data']) && isset($response['data']['id'])) {
                // return response()->json(['status' => 'Message posted', 'tweet_id' => $response['data']['id']], 200);
                return true;
            } else {
                // return response()->json(['status' => 'Error posting message', 'error' => $response], 500);
                dump('response=');
                dump($response);
                return false;
            }
        } catch (\Exception $e) {
            dump('error posting message');
            dump($e->getMessage());
            // return response()->json(['status' => 'Exception caught', 'error' => $e->getMessage()], 500);
            return false;
        }
    }

    public function postTweetV2(Request $request)
    {
        $status = $request->input('status', 'This is a tweet posted from my Laravel application using API v2.');
        $bearerToken = config('twitter.bearer_token');

        dump('bearerToken(postTweetV2)=');
        dump($bearerToken);

        try {
            $response = Http::withHeaders([
                'Authorization' => 'Bearer ' . $bearerToken,
                'Content-Type' => 'application/json',
            ])->post('https://api.twitter.com/2/tweets', [
                'text' => $status,
            ]);

            dump('response(postTweetV2)=');
            dump($response);


            if ($response->successful()) {
                return true;
                //return response()->json(['status' => 'Message posted', 'data' => $response->json()], 200);
            } else {
                return false;
                //return response()->json(['status' => 'Error posting message', 'error' => $response->body()], 500);
            }
        } catch (\Exception $e) {
            dump('error posting message');
            dump($e->getMessage());
            return false;

            //return response()->json(['status' => 'Exception caught', 'error' => $e->getMessage()], 500);
        }
    }


    public function showError(Request $request)
    {
        // Get all error messages
        $errors = $request->session()->get('errors');

        // Return a view and pass the error messages to it
        // return view('twitter.error', ['errors' => $errors]);
        return response()->json(['errors' => $errors], 500);
    }
}
