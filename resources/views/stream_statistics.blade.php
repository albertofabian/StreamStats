<!doctype html>
<html lang="{{ app()->getLocale() }}">
    <head>
        <meta charset="utf-8">
        <meta http-equiv="X-UA-Compatible" content="IE=edge">
        <meta name="viewport" content="width=device-width, initial-scale=1">

        <title>StreamStats</title>

        <!-- Fonts -->
        <!--link href="https://fonts.googleapis.com/css?family=Raleway:100,600" rel="stylesheet" type="text/css"-->

        <!-- Styles -->
        <style>
            html, body {
                background-color: #fff;
                color: #636b6f;
                font-family: 'Raleway', sans-serif;
                font-weight: 100;
                height: 100vh;
                margin: 0;
            }

            .full-height {
                height: 100vh;
            }

            .flex-center {
                align-items: center;
                display: flex;
                justify-content: center;
            }

            .position-ref {
                position: relative;
            }

            .top-right {
                position: absolute;
                right: 10px;
                top: 18px;
            }

            .content {
                text-align: center;
            }

            .title {
                font-size: 84px;
            }

            .links > a {
                /*color: #636b6f;*/
                padding: 0 25px;
                font-size: 12px;
                font-weight: 600;
                letter-spacing: .1rem;
                text-decoration: none;
                text-transform: uppercase;
            }

            .m-b-md {
                margin-bottom: 30px;
            }
            .table1 {
                overflow:scroll;
                height:200px;
                width:500px;
           }
        </style>
    </head>
    <body>
        
        <table class="table1" border="1"> 
            <thead>
                <th colspan="2">
                    Total number of streams for each game
                </th>
            </thead>
            <thead>
                <th>
                    Game
                </th>
                <th>
                    Streams
                </th>
            </thead>
            @foreach($gamesTotalStreams as $gameTotalStreams)
            <tr>
                <td>{{ $gameTotalStreams->game_name }}</td>
                <td>{{ $gameTotalStreams->num_of_streams }}</td>
            </tr>
            @endforeach
        </table>
        
        <table>
            <thead>
                <th colspan="2">
                    Top games by viewer count for each game
                </th>
            </thead>
            <thead>
            <th>
                Game
            </th>
            <th>
                Viewers
            </th>
            </thead>
            @foreach($gamesTotalViewers as $gameTotalViewers)
            <tr>
                <td>{{ $gameTotalViewers->game_name }}</td>
                <td>{{ $gameTotalViewers->number_of_viewers }}</td>
            </tr>
            @endforeach
        </table>
        
        <h3>Median number of viewers for all streams: {{ $avg_viewers }}</h3>
        
        <table>
            <thead>
                <th colspan="2">
                    List of top 100 streams by viewer count that can be sorted asc & desc
                </th>
            </thead>
            <thead>
            <th>
                Top 100 Streams
            </th>
            <th>
                Viewers
            </th>
            </thead>
            @foreach($topStreamsViewers as $topStreamViewers)
            <tr>
                <td>{{ $topStreamViewers->stream_title }}</td>
                <td>{{ $topStreamViewers->number_of_viewers }}</td>
            </tr>
            @endforeach
        </table>
 
        <table>
            <thead>
                <th colspan="2">
                    Total number of streams by their start time (rounded to the nearest hour)
                </th>
            </thead>
            <thead>
                <th>
                    Started Round Hour Time
                </th>
                <th>
                    Streams
                </th>
            </thead>
            @foreach($started_times as $time => $streams)
            <tr>
                <td>{{ $time }}</td>
                <td>{{ $streams }}</td>
            </tr>
            @endforeach
        </table>
        
        <table>
            <thead>
                <th colspan="2">
                    Which of the top 1000 streams is the logged in user following
                </th>
            </thead>
            <thead>
            <th>
                Streams Followed by user from Top 1000
            </th>            
            </thead>
            @foreach($userFollowedTopStreams as $stream)
            <tr>
                <td>{{ $stream->stream_title }}</td>
            </tr>
            @endforeach
            @if (!sizeof($userFollowedTopStreams))
            <tr>
                <td>-- None --</td>
            </tr>            
            @endif
        </table>
        
        <h3>Viewers needed to gain in order to make it into the top 1000 from lowest viewer count stream that the logged in user is following: {{ $viewers_needed_to_top_list }}</h3>
        
        <table>
            <thead>
                <th colspan="2">
                    Tags shared between the user followed streams and the top 1000 streams
                </th>
            </thead>            
            @foreach($tagsShared as $tagShared)
            <tr>
                <td>{{ $tagShared->name }}</td>
            </tr>
            @endforeach
            @if (!sizeof($tagsShared))
            <tr>
                <td>-- None --</td>
            </tr>            
            @endif
        </table>
        
    </body>