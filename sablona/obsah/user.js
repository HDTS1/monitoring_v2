(function(){
    if(!page.centrum){
        page.centrum={};
    }
    
    let selectOne = function(){
        $(this).parent().find(">div").attr("select",0);
        
        let v = $(this).attr("select");
        v = v==0 ? 1 : 0;
        $(this).attr("select",v);
    };
    
    let select = function(){
                
        let v = $(this).attr("select");
        v = v==0 ? 1 : 0;
        $(this).attr("select",v);
    };
    
    
    page.centrum.listTeplota = function(el){
        let teplota = page.start.setCanvas("setTeplomer",{
            title: "Nastavenie teplomer",
            template:"/canvas/monitor/centrum/teplomer",
            cmd: function(c){
                $(c.el).find("div.row-teplomer").click(selectOne);
                $(c.el).find("button[item='cmd']").click(function(){
                   let t = $(c.el).find("div.row-teplomer[select='1']").attr("kluc"); 
                   page.setting_centrum.data.papago=t;
                   
                   //debug(page.setting_centrum.data);
                   
                   page.setting_centrum.view();
                   teplota.hide(); 
                });
                
            }
        });
        
        
        
    };
    
    page.centrum.listHST = function(el){
        let hst = page.start.setCanvas("setHST",{
            title: "Nastavenie HST",
            template:"/canvas/monitor/centrum/hst",
            cmd: function(c){
                $(c.el).find("div.row-hst").click(select);
                $(c.el).find("button[item='cmd']").click(function(){
                   let zoznam = [];
                   let t = $(c.el).find("div.row-hst[select='1']"); 

                   $.each(t, function(k,v){
                       let kluc = $(v).attr("kluc");
                       zoznam.push(    {
                            label: "HST " + (k+1),
                            plc: kluc
                        });
                   });

                   page.setting_centrum.data.plc=zoznam;
                   page.setting_centrum.view();
                   hst.hide(); 
                });
            }
        });
    };    
    
    
    page.centrum.listCamera = function(el){
        let dataKamera = {
            server:null,
            stream:null,
            id:null
        };
        
        let camera = page.start.setCanvas("setCamera",{
            title: "Nastavenie Kamery",
            template:"/canvas/monitor/centrum/camera",
            cmd: function(c){
                $(c.el).find("div.row-camera").click(selectOne);
                $(c.el).find("button[item='cmd']").click(function(){
                   let t = $(c.el).find("div.row-camera[select='1']").attr("kluc"); 
                   let s = $(c.el).find("div.row-camera[select='1']").attr("host"); 
                   
                   if(!t){
                       page.setting_centrum.data.camera = null;
                       page.setting_centrum.view();
                       camera.hide(); 
                       return false;
                   }
                   
                   
                   
                   let canvas_info = page.start.setCanvas("info_centrum", {
                       title:"Info",
                       template:"/canvas/IIS/noEdit",
                       data: {
                           text:"Moment overujem dostupnost centra ...."
                       }
                   });
                   
                   
                   zapis("/rest/service/testDevice",{data:{kluc:t}, json:true}, function(odpoved){
                       if(odpoved.data==false){
                           alert("Nie je dostupne centrum");
                           canvas_info.hide();
                           return false;
                       }
                       
                       dataKamera.server = s;
                       
                       let videoCentrum = page.start.setCanvas("kamera_setting", {
                           title:"Vyber kameru",
                           data: dataKamera,
                           template: "/canvas/monitor/centrum/select_camera",
                           cmd: function(c1){
                               canvas_info.hide();
                               
                               $(c1.el).find("button[item='cmd']").click(function(){
                                   let frm = $(this).closest("div[item='form']");

                                   
                                   let d = getData(frm);
                                   dataKamera.stream=d.stream;
                                   dataKamera.id= d.id;
                                   
                                   page.setting_centrum.data.camera = dataKamera;
                                    page.setting_centrum.view();
                                    videoCentrum.hide();
                                    camera.hide();
                                   
                               });
                           }
                       });
                       
                       
                   });
                  
                   
                   
                   //page.setting_centrum.data.papago=t;
                   
                   //debug(page.setting_centrum.data);
                   
                   //page.setting_centrum.view();
                   //camera.hide(); 
                });
            }
        });
    };    

    
    page.centrum.listUsers = function(el){
        let users = page.start.setCanvas("setUsers",{
            title: "Nastavenie Users",
            template:"/canvas/monitor/centrum/users"
        });
    };
    
    
    
    page.centrum.setting = function(){
        
        let centrum = $("model[data]").attr("data");
        centrum = $.parseJSON(centrum);
        
        
        let setting = page.start.setCanvas("centrum_setting",{
            title:"Nastavenie centra",
            template: "/canvas/monitor/centrum/setting",
            data: {
                kluc:centrum
            },
            cmd: function(c){
                $(c.el).find("button[item='cmd']").click(function(){
                    let setData = {
                        id_model: page.setting_centrum.data.id_model,
                        data: page.setting_centrum.data
                    };
                    
                    setData.data.label= $(c.el).find("input[data-item=\"label\"]").val();
                    
                    
                    delete setData.data.range;
                    delete setData.data.id_model;
                    
                    let zapisData = setData;
                    //debug(zapisData);
                    

                    zapis("/rest/centrum/setCentrum",{data: zapisData, json:true}, function(odpoved){
                        //debug(odpoved);
                        setting.hide();
                        smerovat("/centrum/"+ zapisData.id_model);
                    });

                    
                    
                });
            }
        });
    };
    
    let switchRange = function(){
        let canvasHistory = page.start.setCanvas("canvas_history",{
            title:"HST change range",
            template:"/canvas/monitor/user/history",
            cmd: function(c){
                let setDate = $("model[data]").attr("data");
                setDate = $.parseJSON(setDate);
                
                let from = setDate.range.from;
                $(c.el).find("input[data-item='date.from']").val(from);
                
                let to = setDate.range.to;
                $(c.el).find("input[data-item='date.to']").val(to);
                
                $(c.el).find("button[item=\"cmd\"]").click(function(){
                    let form = $(this).closest("div[item='form']");
                    let d = getData(form);
                    
                    setDate.range = {
                        from: d.date.from,
                        to: d.date.to
                    };

                    $("model[data]").attr("data", JSON.stringify(setDate));
                    
                    
                    page.start.cmdEvent("change_history_range", setDate);
                    canvasHistory.hide();
                   

                   
                   
                   
                });
                
                
                
                
                
            }
        });
    };

    if(!page.centrum){
        page.centrum={};
    }

    page.centrum.switchRange = switchRange;
    
    
    
    page.start.registerBind("change_history_range", function(data){
        data= {kluc: data.id_model, from: data.range.from, to: data.range.to};
        page.tool.createTemplateText("/component/centrum/plc",data, function(obsah){
            obsah = $.parseHTML(obsah,document,true);
            $("div#plc_view").replaceWith(obsah);
        });
    }, true);
})();