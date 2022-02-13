<!doctype html>
<html lang="{{ app()->getLocale() }}">
    <head>
        <meta charset="utf-8">
        <meta http-equiv="X-UA-Compatible" content="IE=edge">
        <meta name="viewport" content="width=device-width, initial-scale=1">

        <title>Laravel</title>

        <!-- Fonts -->
        <link href="https://fonts.googleapis.com/css?family=Raleway:100,600" rel="stylesheet" type="text/css">

        <!-- Styles -->
        <!--style>
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
                color: #636b6f;
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
        </style-->
    </head>
    <body>
        
        <table>
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
        
    </body>