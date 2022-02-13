<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Exception;

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
        
        $this->gamesTotalStreams = $this->getGamesTotalStreams();
        $gamesTotalStreams = $this->gamesTotalStreams;
        
        $this->gamesTotalViewers = $this->getGamesTotalViewers();
        $gamesTotalViewers = $this->gamesTotalViewers;
        
        $avg_viewers        = $this->getAverageStreamsViewers();
        $topStreamsViewers  = $this->getTop100StreamsViewers();
        $started_times      = $this->getStreamsCountPerRoundedHour();
        ksort($started_times);
        
        return view('stream_statistics', compact("gamesTotalStreams", "gamesTotalViewers", "avg_viewers", "topStreamsViewers", "started_times"));
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
    
    public function getUserFollowedStreams() {
        
    }
    
}
