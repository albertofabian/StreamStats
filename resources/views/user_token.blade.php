Redirecting...
<script>
    let params = location.hash;
    location.hash = "";
    let search1 = "access_token=";
    let pos1 = params.search(search1);
    pos1 += search1.length;     
    let pos2 = params.search("&");
    let token = params.substring(pos1, pos2);
    location.href = '/dashboard?user_token=' + token;       
</script>

