Redirecting...
<?php //echo url('/dashboard');  die();?>
<html>
    <form method="post" action="{{ url('/dashboard') }}" id="all_results">  
         {{ csrf_field() }}
        <input type="hidden" name="user_token" id="user_token"/>
    </form>    
</html>

<script>
    let params = location.hash;
    location.hash = "";
    let search1 = "access_token=";
    let pos1 = params.search(search1);
    pos1 += search1.length;     
    let pos2 = params.search("&");
    let token = params.substring(pos1, pos2);
    document.getElementById('user_token').value = token
    document.getElementById("all_results").submit();
    //location.href = '/dashboard?user_token=' + token;       
</script>
