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
        },
        edit: function(el){
            let kluc = $(el).closest("div[kluc]").attr("kluc");
            zapis("/rest/service/getService",{data:{kluc:kluc}, json:true}, function(odpoved){
                if(odpoved.result){
                    let serviceData = odpoved.data;
                    let canvas = page.start.setCanvas("edit_service", {
                        title: "Edit Service",
                        template: "/canvas/monitor/servis",
                        cmd: function(m){
                            $(m.el).find("input[data-item='label']").val(serviceData.data.label);
                            $(m.el).find("input[data-item='host']").val(serviceData.data.host);
                            $(m.el).find("input[data-item='port']").val(serviceData.data.port);
                            $(m.el).find("select[data-item='group']").val(serviceData.data.group);
                            
                            $(m.el).find("button[item='cmd']").click(function(){
                                let fr = $(this).closest("div[item='form']");
                                let data = getData(fr);
                                data.id_model = kluc;
                                let v = new page.monitor.form.validate(fr);
                                
                                zapis("/rest/service/updateService",{data:data, json:true}, function(odpoved_update){
                                    if(!odpoved_update.result){
                                        v.test(odpoved_update.data);
                                        return false;
                                    }
                                    canvas.hide();
                                });
                            });
                        }
                    });
                }
            });
        },
        delete: function(el){
            let kluc = $(el).closest("div[kluc]").attr("kluc");
            if(confirm("Naozaj chcete vymazat toto zariadenie/sluzbu?")){
                zapis("/rest/service/deleteService",{data:{id_model:kluc}, json:true}, function(odpoved){
                    if(odpoved.result){
                        let row = $("div.service-component[kluc='"+kluc+"']")[0];
                        if(row){
                            $(row).remove();
                        }
                    } else {
                        alert("Chyba pri mazani sluzby");
                    }
                });
            }
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
            
            
        if(data.metoda=="deleteService"){
            let row = $("div.service-component[kluc='"+data.data+"']")[0];
            if(row){
                $(row).remove();
            }
        }
    });
})();