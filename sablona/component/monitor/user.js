(function(){
    
    //$("#container_active_user").hide();
    
    let container = $("#user_active");
    let tmp = `<div class="col-12 p-0"><div class="user_log"><div>{time}</div><div>{username}</div><div>{url}</div></div></div>`;
    
    page.start.registerBind("socket", function(data){
        if(data.metoda=="user"){
            let row = page.tool.createTemplateJSObject(tmp, data.data);
            $(container).append(row);
            $("#container_active_user").show();
        }    
    });
})();
