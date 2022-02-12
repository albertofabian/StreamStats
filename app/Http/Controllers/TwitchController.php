<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

namespace App\Helpers;
use Exception;


class TwitchController extends Controller
{

    private $client;
    private $endpoint;
    private $twitch_client_id;
    private $twitch_client_secret;
    private $twitch_bearer_token;

    public function __construct(\GuzzleHttp\Client $client)
    {
        $this->client = $client;
        $this->endpoint             = getenv('TWITCH_ENDPOINT');
        $this->twitch_client_id     = getenv('TWITCH_CLIENT_ID');
        $this->twitch_client_secret = getenv('TWITCH_CLIENT_SECRET');
        $this->twitch_bearer_token  = getenv('TWITCH_BEARER_TOKEN');
    }

    public function getStreams()
    {
        $response = $this->client->request('GET', $this->endpoint . 'streams', [
            'query' => [
                'first'       => '100',
                'access_token' => $this->facebookPage->page_access_token
            ]
        ]);

        $responseBody = $this->handleResponse($response);

        return $responseBody;
    }

  
    public function linkInfluencer($accessToken, $pageId, Influencer $influencer)
    {
        if ($influencer->facebook_page_id) {
            throw new FacebookException('This Influencer is already associated with a Facebook Page', true);
        }

        $facebookPage = FacebookPage::has('influencer')->where('page_id', $pageId)->first();

        if ($facebookPage) {
             throw new FacebookException('This Facebook Page is already associated with an Influencer', true);
        }

        $this->findOrCreateAccount($accessToken);

        $pageData = $this->getPage($pageId);

        if (!$pageData['is_eligible_for_branded_content']) {
            throw new FacebookException('This Facebook Page is not eligible for branded content', true);
        }

        $this->createPage($pageData);

        $influencer->facebook_page_id = $this->facebookPage->id;

        if (!$influencer->profile_picture_object_image_id && $this->facebookPage->profile_picture_object_image_id) {
            $influencer->profile_picture_object_image_id = $this->facebookPage->profile_picture_object_image_id;
        }

        $influencer->save();

        SocialNetworksHelper::updateInfluencerSocialNetworkAggregate($influencer->id);

        EventHelper::dispatch(EventHelper::FACEBOOK_CONNECT, ['influencer_id' => $influencer->id]);
    }    
}
