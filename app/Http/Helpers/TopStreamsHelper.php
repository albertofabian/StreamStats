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
        
        echo "Seed BEGIN\n";
        $cursor = "";
        $data = [];
        
        for ($i = 0; $i < 50; $i++) {
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
        }

        try {
            Stream::truncate();
            
            shuffle($data);
            
            foreach($data as $element) {
                $stream = new Stream();
                $stream->client_id          = $element->user_id;
                $stream->channel_name       = $element->user_name;
                $stream->stream_title       = $element->title;
                $stream->game_name          = $element->game_name;
                $stream->number_of_viewers  = $element->viewer_count;
                $started_at = str_replace("T", " ", $element->started_at);
                $started_at = str_replace("Z", " ", $started_at);
                $stream->started_at         = $started_at;
                $stream->save();        
                unset($stream);
            }
        } catch (Exception $e) {
           error_log($e);
           return;
        }
        echo "Seed END\n";
    }
}
