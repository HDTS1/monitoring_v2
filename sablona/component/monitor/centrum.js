(function(){

    
    let container = $("#list_centrum");

    
    page.start.registerBind("socket", function(data){
        if(data.metoda=="createCentrum"){
            
            let row = $("div.centrum-component[kluc='"+data.data+"']")[0];
            if(!row){
                page.tool.createTemplateText("/component/monitor/centrum/centrum", {id_model:data.data}, function(obsah){
                    obsah = $.parseHTML(obsah);
                    $(container).append(obsah);
                });
            }

        }   

        
    });
})();
