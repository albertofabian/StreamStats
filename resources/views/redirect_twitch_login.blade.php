Redirecting...
<script>
    location.href = "https://www.twitch.tv/login?client_id=<?=getenv('TWITCH_CLIENT_ID')?>&redirect_params=client_id%3D<?=getenv('TWITCH_CLIENT_ID')?>%26force_verify%3Dtrue%26redirect_uri%3Dhttp%253A%252F%252Flocalhost%253A8000%252Fusertoken%26response_type%3Dtoken%26scope%3Duser%253Aread%253Aemail+user%253Aread%253Afollows";       
</script>

