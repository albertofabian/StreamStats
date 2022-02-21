<!doctype html>
<html lang="{{ app()->getLocale() }}">
    <head>
        <meta charset="utf-8">
        <meta http-equiv="X-UA-Compatible" content="IE=edge">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta http-equiv="refresh" content="3600;url=https://www.twitch.tv/login?client_id=<?=getenv('TWITCH_CLIENT_ID')?>&redirect_params=client_id%3D<?=getenv('TWITCH_CLIENT_ID')?>%26force_verify%3Dtrue%26redirect_uri%3Dhttp%253A%252F%252Flocalhost%253A8000%252Fusertoken%26response_type%3Dtoken%26scope%3Duser%253Aread%253Aemail"/>
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

            .m-b-md {
                margin-bottom: 30px;
            }
            table {
                width: 50%;
                border: 1px solid black;
                border-collapse: collapse;
                margin: auto;
                padding: 10px;
            }           
            td {
                padding: 5px;
                /*font-size: medium;*/
                font-weight: normal;
            }            
            .num {
                text-align: right;
            }
            .center {
                text-align: center;
            }
            th {
                padding: 5px                
            }
            
        </style>
    </head>
    <body>
        <h1 style="text-align: center">Top StreamStats App</h1>
        <h3 style="text-align: center">Logged user: Alberto Bohbouth<h3>     
        <table style="cursor: pointer; background-color: #C7CEEA">
            <thead>
                <th colspan="2" onclick="swapVisible('streams_number_game')">
                    Total number of streams for each game<br>(from top 1000 streams)
                </th>
            </thead>
        </table>
        <div id="streams_number_game" style="display: none">
            <table border="1" style="background-color: #C7CEEA">             
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
                    <td class="num">{{ $gameTotalStreams->num_of_streams }}</td>
                </tr>
                @endforeach
                <tr>
                    <td colspan="2" style="cursor: pointer; font-size: 20; font-weight: bolder; text-align: center" onclick="swapVisible('streams_number_game')">^</td>
                </tr>
            </table>
        </div>
        
        <br/>
        
        <table border="1" style="cursor: pointer; background-color: #B6EAD7">
            <thead>
                <th colspan="2" onclick="swapVisible('game_viewers')">
                    Top games by viewer count for each game<br>(from top 1000 streams)
                </th>
            </thead>        
        </table>
        <div id="game_viewers" style="display: none">
            <table border="1" style="background-color: #B6EAD7">
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
                    <td class="num">{{ $gameTotalViewers->number_of_viewers }}</td>
                </tr>
                @endforeach
                <tr>
                    <td colspan="2" style="cursor: pointer; font-size: 20; font-weight: bolder; text-align: center" onclick="swapVisible('game_viewers')">^</td>
                </tr>
            </table>
        </div>
        <br/>
        
        <table border="1" style="background-color: #E2F0CB" >
            <thead>
                <th colspan="2">
                    Median number of viewers for all streams:  <span style="background-color: black; color: white">&nbsp;{{ $avg_viewers }}&nbsp;</span>
                </th>
            </thead>
        </table>    
        
        <br/>
        
        <table style="cursor: pointer; background-color: #FFDAC1">
            <thead>
                <th colspan="2" onclick="swapVisible('top100')">
                    List of top 100 streams by viewer count (from top 1000 streams)
                </th>
            </thead>
        </table>
        <div id="top100" style="display: none">
            <table border="1" class="sortable" style="cursor: pointer; background-color: #FFDAC1">
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
                    <td class="num">{{ $topStreamViewers->number_of_viewers }}</td>
                </tr>
                @endforeach
            </table>
            <table border="1" style="cursor: pointer; background-color: #FFDAC1">                                
                <tr>
                    <td colspan="2" style="cursor: pointer; font-size: 20; font-weight: bolder; text-align: center" onclick="swapVisible('top100')">^</td>
                </tr>
            </table>
        </div>    
        
        <br/>
        
        <table style="cursor: pointer; background-color: #FFB7B2">
            <thead>
                <th colspan="2" onclick="swapVisible('start_time')">
                    Total number of streams by their start time<br>(rounded to the nearest hour)
                </th>
            </thead>
        </table>
        <div id="start_time" style="display: none">
            <table border="1" style="background-color: #FFB7B2">
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
                    <td style="text-align: center">{{ $time }}</td>
                    <td style="text-align: center" class="num">{{ $streams }}</td>
                </tr>
                @endforeach
                <tr>
                    <td colspan="2" style="cursor: pointer; font-size: 20; font-weight: bolder; text-align: center" onclick="swapVisible('start_time')">^</td>
                </tr>
            </table>
        </div>  
            
        <br/>
        
        <table style="cursor: pointer; background-color: #FF9AA2">
            <thead>
                <th colspan="2" onclick="swapVisible('logged_follows')">
                    Which of the top 1000 streams is the logged in user following
                </th>       
            </thead>
        </table>
        <div id="logged_follows" style="display: none">
            <table border="1" style="background-color: #FF9AA2">
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
                    <td class="center">-- None --</td>
                </tr>            
                @endif
                <tr>
                    <td colspan="2" style="cursor: pointer; font-size: 20; font-weight: bolder; text-align: center" onclick="swapVisible('logged_follows')">^</td>
                </tr>
            </table>
        </div> 
            
        <br/>
        
        <table style="background-color: #C7CEEA">
            <thead>
                <th colspan="2">
                    <?php 
                    if($viewers_needed_to_top_list < 0) {
                        $viewers_needed_to_top_list .= ", i.e. NONE";
                    }
                    ?>
                    Viewers needed to gain in order to make it into the top 1000 from lowest
                    viewer count stream that the logged in user is following: <span style="background-color: black; color: white">&nbsp;{{ $viewers_needed_to_top_list }}&nbsp;</span>
                </th>
            </thead>
        </table>
        
        <br/>
        
        <table style="cursor: pointer; background-color: #B6EAD7">
            <thead>
                <th colspan="2" onclick="swapVisible('tag_shared')">
                    Tags shared between the user followed streams and the top 1000 streams
                </th>
            </thead>
        </table>
        <div id="tag_shared" style="display: none">
            <table border="1" style="background-color: #B6EAD7">
                @foreach($tagsShared as $tagShared)
                <tr>
                    <td>{{ $tagShared }}</td>
                </tr>
                @endforeach
                @if (!sizeof($tagsShared))
                <tr>
                    <td class="center">-- None --</td>
                </tr>            
                @endif
                <tr>
                    <td colspan="2" style="cursor: pointer; font-size: 20; font-weight: bolder; text-align: center" onclick="swapVisible('tag_shared')">^</td>
                </tr>
            </table>
        </div>    
        
        <br/>
    </body>
</html>    
<script src="https://www.kryogenix.org/code/browser/sorttable/sorttable.js"></script>
<script>
    function swapVisible(id) {
        if (document.getElementById(id).style.display == 'block') {
            document.getElementById(id).style.display='none';
        } else {
            document.getElementById(id).style.display='block';            
        }
    }
    
    let params = location.hash;
    location.hash = "";
    let search1 = "access_token=";
    let pos1 = params.search(search1);
    pos1 += search1.length;     
    let pos2 = params.search("&");
    let token = params.substring(pos1, pos2);
    //console.log(token);
</script>
