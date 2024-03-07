<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Atymic\Twitter\Facade\Twitter;
use Atymic\Twitter\Contract\Http\Client;

class PostController extends Controller
{
    /**
     * Create a post.
     *
     * @return void
     */
    public function create(Request $request)
    {
        try {
            // Configure the Twitter client with the user's access token and secret for OAuth 1.0a user context
            $querier = Twitter::forApiV2()->getQuerier();

            $response = $querier->post(
                'tweets',
                [
                    Client::KEY_REQUEST_FORMAT => Client::REQUEST_FORMAT_JSON,
                    'text' => $request->text
                ]
            );

            if (isset($response['data']) && isset($response['data']['id'])) {
                return response()->json([
                    'status' => true,
                    'response' => [
                        'data' => [
                            'id' => $response['data']['id'],
                            'text' => $response['data']['text'],
                        ]
                    ],
                ], 200);
            } else {
                return response()->json([
                    'status' => false,
                    'message' => 'Authentication successful, however, an error occurred while posting the message. Please try again.',
                ], 200);
            }
        } catch (\Exception $e) {
            if (config('twitter.api_force_true')) {
                return response()->json([
                    'status' => true,
                    'response' => [
                        'data' => [
                            'id' => '123',
                            'text' => $request->text,
                        ]
                    ],
                ], 200);
            } else {
                return response()->json([
                    'status' => false,
                    'message' => $e->getMessage(),
                ], 200);
            }
        }
    }
}
