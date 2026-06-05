(function(){

    
    let container = $("#list_service");
    let tmp = `<div class="col-12 p-0"><div class="user_log"><div>{time}</div><div>{username}</div><div>{url}</div></div></div>`;
    
    page.dostupnost = {
        overit: function(el){
            let kluc = $(el).closest("div[kluc]").attr("kluc");
            zapis("/rest/service/testDevice",{data:{kluc:kluc}, json:true}, function(odpoved){
                if(!odpoved.result){
                    alert("Nie je dostupny server monitor !!!!");
                }
            });

        }
    };
    
    
    page.start.registerBind("socket", function(data){
        if(data.metoda=="createService"){
            
            let row = $("div.service-component[kluc='"+data.data+"']")[0];
            if(!row){
                page.tool.createTemplateText("/component/monitor/service/service", {id_model:data.data}, function(obsah){
                    obsah = $.parseHTML(obsah);
                    $(container).append(obsah);
                });
            }

        }   
        
        //testDevice
        if(data.metoda=="testDevice"){
            
            //debug(data.data);
            
            let row = $("div.service-component[kluc='"+data.data.id_model+"']")[0];
            if(!row){
                page.tool.createTemplateText("/component/monitor/service/service", {id_model:data.data.id_model}, function(obsah){
                    obsah = $.parseHTML(obsah);
                    $(container).append(obsah);
                });
            } else {
                page.tool.createTemplateText("/component/monitor/service/service", {id_model:data.data.id_model}, function(obsah){
                    obsah = $.parseHTML(obsah);
                    $(row).replaceWith(obsah);
                });
            }
            
            
        }
        
        
    });
})();