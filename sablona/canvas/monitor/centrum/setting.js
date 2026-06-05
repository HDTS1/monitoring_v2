page.setting_centrum = {
   data: {},
    
   view: function(){
       let d = page.setting_centrum.data;
       //debug(page.setting_centrum.data);
       $("div#centrum_setting").find("input[data-item=\"label\"]").val(d.label);
       
       let t = d.papago;
       t = !t ? 'Teplomer nie je nastaveny' : t;
       $("div#centrum_setting").find("div.nastavenie-value[item='teplomer']").html(t);
       
       
       
       let plc = d.plc;
       
       let x = $.map(plc, function(item){
           return `<div class="list_plc">${item.plc}</div>`;
       });
       
       if(!plc || plc.length==0){
           x = [];
           x.push(`<div class="list_plc">Nemame nastavene PLC</div>`);
       }
       
       
       $("div#centrum_setting").find("div.nastavenie-value[item='plc']").html(x.join(""));
       

       let c =[];
       if(d.camera && d.camera.server){
       
            c = [
                `<div class="camera"><div>Server:</div><div>${d.camera.server}</div></div>`,
                `<div class="camera"><div>Stream:</div><div>${d.camera.stream}</div></div>`,
                `<div class="camera"><div>ID:</div><div>${d.camera.id}</div></div>`
            ];
        } else {
            c = [
                `<div class="camera"><div>Kamera nie je nastavena</div></div>`
            ];  
        }
       
       $("div#centrum_setting").find("div.nastavenie-value[item='camera']").html(c.join(""));
       

       
   },
    
    
   load: function(){
       let data = $("div#centrum_setting").attr("data");
       data = $.parseJSON(data);
       page.setting_centrum.data =data.kluc;
       $("div#centrum_setting").removeAttr("data");
       page.setting_centrum.view();
   } 
};

page.setting_centrum.load();