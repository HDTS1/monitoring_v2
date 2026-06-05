(function(){
    let d = $("div[plc_data]").attr("plc_data");
    d = $.parseJSON(d);
    
    let zapis_cmd = function(){
        
        let result = [];
        
        
        
        $.each($("div.prikaz[select='1']"), function(){
            let el = this;
            let prikaz = $(this).attr("prikaz");
            let value = $(this).attr("value");
            
            let val = {
                x: function(){
                    return null;
                },
                pocet: function(){
                    let s = $(el).find("div.pocet").attr("value");
                    return parseInt(s);
                },
                word: function(){
                    let p = [];
                    let s = $(el).find("div.check-select");
                    $.each(s, function(){
                        let h = $(this).attr("select");
                        p.push(h);
                    });
                    
                    return p.join('');
                },
                server: function(){
                    let s = $(el).find("select").val();
                    return s;
                },
                
                interval: function(){
                    let s = $(el).find("select").val();
                    return parseInt(s);
                }
                
            };
            
            let a = val[value]();
            
            
            
            let x= {
                cmd: prikaz,
                value: a,
                type:value
            };

            result.push(x);
        });
        
        if(result.length==0){
            alert("Nemas nic na zapis !!!");
            return false;
        }
        
        let vystup = {
            plc: d.data.label,
            list_cmd: result
        };
        
        // Osetrene zmeny
        /*
        if(vystup.plc != "PLC.SK.STU1." && vystup.plc != "PLC.CZ.PRG1."){
            vystup.plc = "PLC.SK.STU1.";
        }
        */
        //alert(vystup.plc);
        

        zapis("/rest/monitor/set_setting_plc",{data: vystup, json:true}, function(odpoved){
            //debug(odpoved);
            smerovat("/");
        });

        
    };
    
    
    let switchSelect = function(el){
        
        if($(el).attr('access')=='R'){
            return false;
        }
        
        $(el).click(function(){
            let v = $(this).attr("select");
            v = v == 1 ? 0 : 1;
            $(this).attr("select",v);  
        });

    };
    
    
    $.each(d.data.status_word_data, function(){
        $("div[prikaz='NSW'] div.check-select[kluc='"+this.kluc+"']").attr("select",this.value);
        switchSelect($("div[prikaz='NSW'] div.check-select[kluc='"+this.kluc+"']"));
        
    });
    
    $.each($("div.prikaz"), function(){
        let el = this;
        
        $(this).find("i.prikaz").click(function(){
            let v = $(el).attr("select");
            v = v == 1 ? 0 : 1;
            $(el).attr("select",v);  
        });
    });
    
    $.each($("div.pocet"), function(){
        
        let obj = this;
        
        let value = parseInt($(this).attr("value"));
        let min = parseInt($(this).attr("min"));
        let max = parseInt($(this).attr("max"));
        
        let setValue = function(v){
            value=v;
            $(obj).attr("value",v);
            $(obj).find(">div:nth-child(2)").html(v);
        };
        
        $(this).find(">div:nth-child(1)").click(function(){
            if(value == min){
                return true;
            }
            let x = value-1;
            setValue(x);
        });
        
        $(this).find(">div:nth-child(3)").click(function(){
            if(value == max){
                return true;
            }
            let x = value+1;
            setValue(x);
        });
    });
    
    
    $("button[item='zapis']").click(zapis_cmd);
    
    
    page.plc_setting= {
        load: function(el){
            let data_setting = $(el).attr("data");
            data_setting = $.parseJSON(data_setting);
            
            $.each(data_setting.data.list_cmd, function(){
                let p = $("div.prikaz[prikaz='"+ this.cmd +"']");
                $(p).attr("select",1);
                
                if(this.type=='pocet'){
                    $(p).find("div.pocet").attr("value", this.value);
                    $(p).find("div.pocet > div:nth-child(2)").html(this.value);
                }
                
                if(this.type=='server'){
                    $(p).find("select").val(this.value);
                }
                
                if(this.type=='word'){
                    let v = this.value.split("");
                    $.each(v, function(k,v){
                        $(p).find("div.check-select[kluc='"+k+"']").attr("select",v);
                    });
                }
            });
            

        }
    };
    

    
})();


