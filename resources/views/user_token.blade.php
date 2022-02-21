Redirecting...
<script src="https://ajax.googleapis.com/ajax/libs/jquery/1.11.3/jquery.min.js"></script>
<script src="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.5/js/bootstrap.min.js"></script>
<script>
    let params = location.hash;
    location.hash = "";
    let search1 = "access_token=";
    let pos1 = params.search(search1);
    pos1 += search1.length;     
    let pos2 = params.search("&");
    let token = params.substring(pos1, pos2);
    console.log(token);
    $.get("/set_user", {user_token: token})
        .done (function(result){
            console.log(result);
            //alert("Set User completado!");
        })
        .fail (function(){
            //alert("Set User falló!");
        });
        
    location.href = 'http://localhost:8000';    
</script>

