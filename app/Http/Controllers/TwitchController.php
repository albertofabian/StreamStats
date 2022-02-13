<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Exception;
use App\Http\Helpers\TopStreamsHelper;

class TwitchController extends Controller
{

    protected $gamesTotalStreams;
    protected $gamesTotalViewers;
    
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

  
    public function getGamesTotalStreams() {

        try { 
            $gameTotalStreams = DB::select(" SELECT game_name, COUNT(game_name) as num_of_streams
                                             FROM streamstats.streams
                                             GROUP BY game_name
                                             ORDER BY num_of_streams DESC");
        } catch (Exception $e) {
           error_log($e);
           return [];
        }
        
       return $gameTotalStreams; 
    }
    
    public function getGamesTotalViewers() {

        try { 
            $gameTotalViewers = DB::select("SELECT game_name, SUM(number_of_viewers) as number_of_viewers
                                            FROM streamstats.streams
                                            GROUP BY game_name
                                            ORDER BY number_of_viewers DESC");
        } catch (Exception $e) {
           error_log($e);
           return [];
        }
        
       return $gameTotalViewers; 
    }
    
    public function getTop100StreamsViewers() {

        try { 
            $topStreamsViewers = DB::select("SELECT stream_title, number_of_viewers
                                             FROM streamstats.streams
                                             ORDER BY number_of_viewers DESC
                                             LIMIT 100");
        } catch (Exception $e) {
           error_log($e);
           return [];
        }
        
       return $topStreamsViewers; 
    }
    
    public function showAllResults() {
                
        //TopStreamsHelper::seedTopStreams();
        
        $this->gamesTotalStreams = $this->getGamesTotalStreams();
        $this->gamesTotalViewers = $this->getGamesTotalViewers();
        
        $avg_viewers            = $this->getAverageStreamsViewers();
        $topStreamsViewers      = $this->getTop100StreamsViewers();
        $started_times          = $this->getStreamsCountPerRoundedHour();
        $userFollowedTopStreams = $this->getUserFollowedStreamsFromTop1000();
        
        $gamesTotalStreams = $this->gamesTotalStreams;
        $gamesTotalViewers = $this->gamesTotalViewers;
        
        ksort($started_times);
        
        return view('stream_statistics', compact("gamesTotalStreams", 
                                                 "gamesTotalViewers", 
                                                 "avg_viewers", 
                                                 "topStreamsViewers", 
                                                 "started_times", 
                                                 "userFollowedTopStreams"));
    }
    
    public function getAverageStreamsViewers () {
        
        $total_viewers = 0;
        foreach ($this->gamesTotalViewers as $gameTotalViewers) {
            $total_viewers += $gameTotalViewers->number_of_viewers;
        }
        
        $total_streams = sizeof($this->gamesTotalViewers);
        
        if ($total_streams) {
            $avg_viewers = round($total_viewers / $total_streams, 2);
        } else {
            $avg_viewers = "---";
        }
        
        return $avg_viewers ;
    }
    
    public function getStreamsCountPerRoundedHour() {
        
        try { 
            $started_times = DB::select("SELECT started_at FROM streamstats.streams");
        } catch (Exception $e) {
           error_log($e);
           return [];
        }
        
        $result = [];
        
        foreach ($started_times as $started_time) {
            
            $nearestHourTime = $this->roundTimetoNearestHour($started_time->started_at);
            
            if ( !isset($result[$nearestHourTime]) ) {
                $result[$nearestHourTime] = 0;
            } else {
                $result[$nearestHourTime]++;
            }
        }
        
        return $result; 
    } 
    
    private function roundTimetoNearestHour($timestamp) {
        $timestamp = strtotime($timestamp);
        return date('Y-m-d H:i:s', round($timestamp / 3600) * 3600);
    }
    
    public function getUserFollowedStreamsFromTop1000() {
        
        ///// Values for testing ////
        $user_id = 603680830;
        $bearer = "p7he3d5czedyewfjrx89vpcqgz3omi";
        
        //////////////////////////////
        $cursor = "";
        $data = [];
        $count = 0; 
        
        do {
            $client = new \GuzzleHttp\Client([
                'headers' => [
                    'client-id'     => getenv('TWITCH_CLIENT_ID'),
                    'Authorization' => 'Bearer ' . $bearer
                ]
            ]) ;

            $response = $client->request('GET', getenv('TWITCH_ENDPOINT')."streams/followed", [
                'query' => [
                    'user_id' => $user_id,
                    'after' => $cursor
                ]
            ]);

            $response = json_decode($response->getBody());
            
            if (isset($response->pagination->cursor)) {
                $cursor = $response->pagination->cursor;            
            }   
            
            $data = array_merge($data, $response->data);
            
            if (sizeof($data) >= 1000) break; 
            
        } while($cursor);

        
        $stream_titles = [];
        foreach ($data as $user_stream) {
           $stream_titles[] =  $user_stream->title;
        }
        
        $stream_titles = implode($stream_titles);
        
        $followedStreamsFromTop = DB::select( " SELECT stream_title 
                                                FROM streamstats.streams
                                                WHERE stream_title IN ('{$stream_titles}')"); 
        //dd($followedStreamsFromTop);
        return $followedStreamsFromTop;
    
    }
    
}
