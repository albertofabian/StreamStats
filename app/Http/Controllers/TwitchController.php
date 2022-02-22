<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\DB;
use Exception;
use App\Http\Helpers\TopStreamsHelper;
use App\Models\Stream;
use App\Models\TwitchUser;

class TwitchController extends Controller
{
    protected $gamesTotalStreams;
    protected $gamesTotalViewers;
    
    private $client;
    private $endpoint;
    private $client_id;
    private $client_secret;
    private $client_bearer_token;
    private $user_twitch_id;
    private $user_twitch_login;
    private $user_bearer_token;
/*
    public function __construct(\GuzzleHttp\Client $client)
    {
        $this->client        = $client;
        $this->endpoint      = getenv('TWITCH_ENDPOINT');
        $this->client_id     = getenv('TWITCH_CLIENT_ID');
        $this->client_secret        = getenv('TWITCH_CLIENT_SECRET');
        $this->client_bearer_token  = getenv('TWITCH_BEARER_TOKEN');
    }
*/  
    public function getGamesTotalStreams() {

        try { 
            $gameTotalStreams = DB::select(" SELECT game_name, COUNT(game_name) as num_of_streams
                                             FROM streamstats.streams
                                             GROUP BY game_name
                                             ORDER BY num_of_streams DESC");
        } catch (Exception $e) {
            die(__CLASS__.": ".$e->getMessage);
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
            die(__CLASS__.": ".$e->getMessage);
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
            die(__CLASS__.": ".$e->getMessage);
        }
        
       return $topStreamsViewers; 
    }
       
    public function showAllResults(Request $request) {
/*                      
        $value = 'something from somewhere';
        setcookie("TestCookie", $value, time() + 20);
*/
        //die('dai kvar');
        /*if (!isset($_COOKIE["TwitchID"])) {        
            echo "Ha expirado!";
        } else {
            echo $_COOKIE["TwitchID"];
        }        
        */
        //die('');
        
        //print_r($_SESSION);
        /*
        1. Reviso si el usuario está vigente en la cookie.
            Caso SI: tomo su ID de la cookie y busco sus datos en la base de datos
            Caso NO: voy a getAndSaveUser y genero la cookie 
            
           Tomo el valor del ID del usuario de la cookie y busco en la base de datos al usuario para 
           mostrar su username y para mandar el ID y token a la funcrion follow
        
        */
               
        $user_token = $request->get('user_token');
        
        if (!Stream::count()) {
            TopStreamsHelper::seedTopStreams();
        }
        
        $this->getAndSaveUser($user_token);
        
        $this->gamesTotalStreams    = $this->getGamesTotalStreams();
        $this->gamesTotalViewers    = $this->getGamesTotalViewers();        
        $avg_viewers                = $this->getAverageStreamsViewers();
        $topStreamsViewers          = $this->getTop100StreamsViewers();
        $started_times              = $this->getStreamsCountPerRoundedHour();
        $userFollowedTopStreams     = $this->getUserFollowedStreamsFromTop1000();
        $viewers_needed_to_top_list = $this->calcViewersFromFollowedStreamToTop1000();
        $tagsShared                 = $this->getSharedTagsBetweenUserFollowedStreamsAndTop1000Streams();
        //dd("Mejorando9...".$request->get('user_token'));
                
        $user_twitch_login = $this->user_twitch_login;
        $gamesTotalStreams = $this->gamesTotalStreams;
        $gamesTotalViewers = $this->gamesTotalViewers;
        
        return view('stream_statistics', compact("gamesTotalStreams", 
                                                 "user_twitch_login",
                                                 "gamesTotalViewers", 
                                                 "avg_viewers", 
                                                 "topStreamsViewers", 
                                                 "started_times", 
                                                 "userFollowedTopStreams",
                                                 "viewers_needed_to_top_list", 
                                                 "tagsShared")
                );
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
            die(__CLASS__.": ".$e->getMessage);
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
        
        ksort($result);
        
        return $result; 
    } 
    
    private function roundTimetoNearestHour($timestamp) {
        $timestamp = strtotime($timestamp);
        return date('Y-m-d H:i:s', round($timestamp / 3600) * 3600);
    }
    
    protected function getUserFollowedStreams() {
        
         ///// Values for testing ////
        $user_id = 603680830;
        $bearer = "xo49p1ctfhixtlkpzk1s6tqgn3v12y";        
        //////////////////////////////
        
        $user_id = $this->user_twitch_id;
        $bearer  = $this->user_bearer_token;        
        
        $cursor = "";
        $data = [];
        $count = 0; 
        try {
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
            
        } catch (Exception $e) {
            die(__CLASS__.": ".$e->getMessage);
        }  
        
        return $data;
    }
    
    public function getUserFollowedStreamsFromTop1000() {
        
        $data = $this->getUserFollowedStreams();

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
        
        $data = [];
        $tag_names = [];
        $userFollowedStreams = $this->getUserFollowedStreams();
        $followedTags = [];
        
        foreach ($userFollowedStreams as $stream) {
            if (isset($stream->tag_ids)) {
                foreach ($stream->tag_ids as $tag_id) {
                    $followedTags[] = "'{$tag_id}'";
                }
            }
        }
        
        $followedTags = array_unique($followedTags);
        
        if (sizeof($followedTags)) {
            
            $followedTags = implode(", ", $followedTags);
        
            $query = "SELECT DISTINCT tag_id FROM streamstats.stream_tags WHERE tag_id IN ({$followedTags})";

            $common_tags = DB::select($query);
            $tagIds = [];

            foreach ($common_tags as $tag) {
                $tagIds[] = $tag->tag_id;
            }

            $cursor = "";
            try {
                do {
                    $client = new \GuzzleHttp\Client([
                        'headers' => [
                            'client-id'     => getenv('TWITCH_CLIENT_ID'),
                            'Authorization' => 'Bearer ' . getenv('TWITCH_BEARER_TOKEN')
                         ]
                    ]);

                    $response = $client->request('GET', getenv('TWITCH_ENDPOINT') ."tags/streams", [
                        'query' => [ 
                            'tag_id'   => $tagIds,
                            'after'    => $cursor
                        ]

                    ]);
                    $response = json_decode($response->getBody());

                    if (isset($response->pagination->cursor)) {
                        $cursor = $response->pagination->cursor;            
                    }   

                    $data = array_merge($data, $response->data);

                    if (sizeof($data) >= 1000) break; 

                } while ($cursor);
            } catch (Exception $e) {
                die(__CLASS__.": ".$e->getMessage);
            }
            
            foreach ($data as $tag) {
                $tag_names[] = $tag->localization_descriptions->{'en-us'};
            }
        }
        
        return $tag_names;
    }
    
    public function getUserToken() {
        return view('user_token');
    }
    
    public function index() {
        return view("redirect_twitch_login");
    }
    
    public function getAndSaveUser($user_token) {
        
        try {
            $client= new \GuzzleHttp\Client([
                'headers' => [
                    'client-id'     => getenv('TWITCH_CLIENT_ID'),
                    'Authorization' => 'Bearer ' . $user_token
                ]
            ]);

            $response = $client->request('GET', getenv('TWITCH_ENDPOINT').'users', []);
            $response = json_decode($response->getBody(),true);

            if (isset($response['data'])) {
                $twitch_id_exists = TwitchUser::where('twitch_id', $response['data'][0]['id'])->count();
                if (!$twitch_id_exists) {
                    $twitchUser = new TwitchUser;                     
                    $twitchUser->twitch_id  = $response['data'][0]['id'];
                    $twitchUser->username   = $response['data'][0]['login'];
                    $twitchUser->email      = $response['data'][0]['email'];
                    $twitchUser->save();                            
                }    
                $value = $response['data'][0]['id'];

                //setcookie("TwitchID", $value, time() + 20);

                $this->user_twitch_id    = $response['data'][0]['id'];
                $this->user_twitch_login = $response['data'][0]['login'];
                $this->user_bearer_token = $user_token;
            }                
            return $response;
        } catch (Exception $e) {
            die(__CLASS__.": ".$e->getMessage);
        }
    }    
}
