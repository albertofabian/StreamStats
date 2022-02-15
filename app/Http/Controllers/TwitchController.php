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
        $viewers_needed_to_top_list = $this->calcViewersFromFollowedStreamToTop1000();
        $tagsShared = $this->getSharedTagsBetweenUserFollowedStreamsAndTop1000Streams();
                
        $gamesTotalStreams = $this->gamesTotalStreams;
        $gamesTotalViewers = $this->gamesTotalViewers;
        
        ksort($started_times);
        
        return view('stream_statistics', compact("gamesTotalStreams", 
                                                 "gamesTotalViewers", 
                                                 "avg_viewers", 
                                                 "topStreamsViewers", 
                                                 "started_times", 
                                                 "userFollowedTopStreams",
                                                 "viewers_needed_to_top_list", 
                                                 "tagsShared"   
                ));
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
    
    protected function getUserFollowedStreams() {
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
        
        return $data;
    }
    
    public function getUserFollowedStreamsFromTop1000() {
        
        $data = $this->getUserFollowedStreams();

        //dd($data);
        $stream_ids = [];
        foreach ($data as $user_stream) {
           $stream_ids[] =  "{$user_stream->id}";
        }
        
        $stream_ids = implode(", ", $stream_ids);
        //dd($stream_ids);
        
        $followedStreamsFromTop = DB::select( " SELECT stream_title 
                                                FROM streamstats.streams
                                                WHERE stream_id IN ('{$stream_ids}')"
                                             ); 
        return $followedStreamsFromTop;
    }
    
    public function calcViewersFromFollowedStreamToTop1000() {
        
        $userFollowedStreams = $this->getUserFollowedStreams();
        $min_folloewd_stream_viewers = 100000000;
        foreach ($userFollowedStreams as $stream) {
            if ($stream->viewer_count < $min_folloewd_stream_viewers) {
                $min_folloewd_stream_viewers = $stream->viewer_count;
            }
        }
        
        $stream = DB::select("SELECT min(number_of_viewers) as min_viewers FROM streamstats.streams")[0];
        
        $min_top1000_stream_viewers = $stream->min_viewers;
        return $min_top1000_stream_viewers - $min_folloewd_stream_viewers;
    }
    
    public function getSharedTagsBetweenUserFollowedStreamsAndTop1000Streams() {
        
        $userFollowedStreams = $this->getUserFollowedStreams();
        
        $followedTags = [];
        foreach ($userFollowedStreams as $stream) {
            foreach ($stream->tag_ids as $tag_id) {
                $followedTags[] = "'{$tag_id}'";
            }
        }
        $followedTags = array_unique($followedTags);
        $followedTags = implode(", ", $followedTags);
        
        $query = "SELECT DISTINCT tag_id FROM streamstats.stream_tags WHERE tag_id IN ({$followedTags})";
        
        $common_tags = DB::select($query);
        $params = "";
        
        foreach ($common_tags as $tag) {
            $params .= "tag_id=" . $tag . "&";
        }
    }
}
