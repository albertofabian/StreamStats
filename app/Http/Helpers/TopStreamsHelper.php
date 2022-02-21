<?php

namespace App\Http\Helpers;

use App\Models\Stream;
use App\Models\StreamTags;
use Exception;

/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

class TopStreamsHelper
{       
    static function seedTopStreams() {
        
        $cursor = "";
        $data = [];
        $streamTags = [];
        
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
            
        } while($cursor);
        
        $data = array_slice($data, 0, 1000);
        
        shuffle($data);
        
        try {
            
            Stream::truncate();
            StreamTags::truncate();
            $allRecords = [];
            
            foreach($data as $element) {
                               
                $started_at = str_replace("T", " ", $element->started_at);
                $started_at = str_replace("Z", " ", $started_at);
                
                array_push ($allRecords, [
                    'stream_id'         => $element->id,
                    'client_id'         => $element->user_id,
                    'channel_name'      => $element->user_name,
                    'stream_title'      => $element->title,
                    'game_name'         => $element->game_name,
                    'number_of_viewers' => $element->viewer_count,
                    'started_at'        => $started_at
                ]);
                
                foreach ($element->tag_ids as $tag_id) {
                    array_push($streamTags, [
                        'stream_id' => $element->id,
                        'tag_id'    => $tag_id
                    ]);        
                }                
            }
            
            Stream::insert($allRecords);
            StreamTags::insert($streamTags);
            
        } catch (Exception $e) {
           error_log($e);
           return false;
        }        
        return true;
    }
}
