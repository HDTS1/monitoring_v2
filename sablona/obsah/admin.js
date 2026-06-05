page.monitor = {
    
    list_plc: function(){
        
        let tmp=`<div favorite="{setting.favorite}" class="item-plc" kluc='{plc}'>
                <div><i class="bi bi-check-square-fill"></i></div>
                <div>{plc}</div>
            </div>`;
        
        let createZaznam = function(el){
           let x = page.tool.createTemplateJSObject(tmp,el);
           return x;
        };
        
        let setFavorite = function(){
            let v = $(this).attr("favorite");
            let kluc = $(this).attr("kluc");

            v = v === '1' ? 0 : 1;
            $(this).attr("favorite",v);


            zapis("/rest/monitor/setFavoritePLC",{data:{kluc:kluc,value:v}, json:true}, function(odpoved){
                //debug(odpoved);
            });


        };
        
        
        let canvas = page.start.setCanvas("list_plc", {
            title: "List PLC",
            template: "/canvas/monitor/list_plc",
            cmd: function(m){
                zapis("/rest/monitor/list_plc", {data:null}, function(odpoved){
                    let obj = [];
                    $.each(odpoved.data, function(){
                        let l = createZaznam(this);
                        l = $.parseHTML(l);
                        $(l).click(setFavorite);
                        obj.push(l[0]);
                    });
                    
                    
                    
                    $(m.el).find("div[item='zaznam']").append(obj);
                    
                });
                
                
            }
        });

        
    },
    
    
    
    form:  {
        validate : function(form){

            this.test1= function(result){
                $(form).find('*[data-item]').parent().attr("validate",true);
                $.each(result,function(){
                    $(form).find('*[data-item="'+this.pole+'"]').parent().attr("validate",this.result);
                });            
            };


            this.test= function(result){
                $(form).find('*[data-item]').attr("validate",true);
                $.each(result,function(){
                    $(form).find('*[data-item="'+this.pole+'"]').attr("validate",this.result);
                    if(this.result){
                        $(form).find('*[data-item="'+this.pole+'"]').removeClass("is-invalid");
                    } else {
                        $(form).find('*[data-item="'+this.pole+'"]').addClass("is-invalid");
                    }

                });            
            };

        }    
    },
    
    
    create_centrum: function(){
        let canvas = page.start.setCanvas("create_centrum", {
            title: "Create centrum",
            template: "/canvas/monitor/create_centrum",
            cmd: function(m){

                $(m.el).find("button[item='cmd']").click(function(){
                    
                    let fr = $(this).closest("div[item='form']");
                    let data = getData(fr);
                    let v = new page.monitor.form.validate(fr);

                    zapis("/rest/centrum/createCentrum",{data:data, json:true}, function(odpoved){
                        if(!odpoved.result){
                            v.test(odpoved.data);
                            return false;
                        }
                        canvas.hide();
                    });

                });
            }
        });
    },
    
    
    create: function(){
        
        let canvas = page.start.setCanvas("create_service", {
            title: "Service",
            template: "/canvas/monitor/servis",
            cmd: function(m){

                $(m.el).find("button[item='cmd']").click(function(){
                    
                    let fr = $(this).closest("div[item='form']");
                    let data = getData(fr);
                    let v = new page.monitor.form.validate(fr);
                    
                    zapis("/rest/service/createService",{data:data, json:true}, function(odpoved){
                        if(!odpoved.result){
                            v.test(odpoved.data);
                            return false;
                        }
                        canvas.hide();
                    });
                });
            }
        });

    }
};

