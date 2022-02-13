<?php

namespace App\Http\Helpers;

use App\Models\Stream;
use Illuminate\Database\Eloquent;
use Exception;

/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

class TopStreamsHelper
{       
    static function seedTopStreams() {
        
        //echo "BEGIN: Top 1000 streams seed\n";
        $cursor = "";
        $data = [];
        $count = 0; 
        
        //for ($i = 0; $i < 50; $i++) {
        do {
            $client = new \GuzzleHttp\Client([
                'headers' => [
                    'client-id'     => getenv('TWITCH_CLIENT_ID'),
                    'Authorization' => 'Bearer ' . getenv('TWITCH_BEARER_TOKEN')
                ]
            ]) ;

            $response = $client->request('GET', getenv('TWITCH_ENDPOINT')."streams", [
                'query' => [
                    'after' => $cursor
                ]
            ]);

            $response = json_decode($response->getBody());
            $cursor = $response->pagination->cursor;            
            $data = array_merge($data, $response->data);
            
            if (sizeof($data) >= 1000) break; 
            
        } while(sizeof($response->data));
        
        $data = array_slice($data, 0, 1000);
        
        shuffle($data);

        try {
            
            Stream::truncate();
            $all_records = [];
            
            foreach($data as $element) {
                               
                $started_at = str_replace("T", " ", $element->started_at);
                $started_at = str_replace("Z", " ", $started_at);
                
                array_push($all_records, [
                    'client_id'         => $element->user_id,
                    'channel_name'      => $element->user_name,
                    'stream_title'      => $element->title,
                    'game_name'         => $element->game_name,
                    'number_of_viewers' => $element->viewer_count,
                    'started_at'        => $started_at
                ]);
            }
            
            Stream::insert($all_records);
            
        } catch (Exception $e) {
           error_log($e);
           return false;
        }
        
        //echo "END: Top 1000 streams seed\n";
        return true;
    }
}
