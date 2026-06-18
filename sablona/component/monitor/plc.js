(function(){
    let plc = function(data){
        
        let active_plc_name = null;
        
        let history = function(){
            page.start.closeCanvas("info_plc");
            
            page.start.setCanvas("history_plc",{
                    title:"History ",
                    data: {plc: active_plc_name},
                    template: "/canvas/monitor/history"
            });
        };
        
        let click = function(){


                let button = $(this).find("button");
                let kluc = $(button).attr("kluc");
                active_plc_name = $(button).attr("name");
                
                
                page.start.setCanvas("info_plc",{
                    title:"Info PLC",
                    template: "/canvas/monitor/record",
                    data: {
                        id_model: kluc
                    },
                    cmd: function(c){
                        $(c.el).find("button#history_event").click(history);
                    }

                });
        };
        
        let popis = `<div class="btn-info">{popis}</div>`;
        
        let button = `<div class="col-12 col-sm-6 col-md-4 col-lg-3 p-0 view-container" view='{setting.favorite}'>
            <div  class="plc-container">
                <button class="btn btn-secondary w-100 event-plc" state="0" kluc="{kluc}" name="{label}" event="{event}">
                    <div >
                        <div>{event}</div>
                        <div>
                            <div><b>{label}</b></div>
                            <div style="font-size:75%"><span>{cas} &nbsp;</span><span item="minute">(0)</span></div>
                        </div>
                    </div>
                    <div class='notify-state'>
                        <div>
                            <div class="on_off" state="{state.on_off}"></div>
                            <div class="alarm"  state="{state.alarm}"></div>
                            <div class="servis" state="{state.servis}"></div>
                            <div class="setting" state="{state.setting}"></div>
                        </div>
                    </div>
                </button>
            </div>
            </div>`;
        
        let container = $("#plc");
        $(container).empty();
        //console.log(data);
        
        
        data.sort(function(a,b){
             return a.label.localeCompare(b.label, 'en', {sensitivity: 'base'});
        });
        
        
        let vyratatCas = function(){
            let akt = new Date();
            let zoznam = $(container).find("> div");
            let opakovanie = 5;
            let tolerancia = 3;
            
            $.each(zoznam, function(){
                let btn = $(this).find("button");
                let cas = $(this).data("cas");
                let rozdiel = akt.getTime() - cas.getTime();
                let dp = $(this).data("data");
                
                
                let porovnanie = dp.interval;
                rozdiel = (rozdiel/1000)/60;
                
                
                
                if(rozdiel<=porovnanie+tolerancia){
                    $(btn).attr("state",60);
                }
                
                if(rozdiel>porovnanie+tolerancia){
                    $(btn).attr("state",0);
                }
                
                if(rozdiel>((porovnanie * opakovanie)+tolerancia)){
                    $(btn).attr("state",360);
                }
                

                rozdiel = rozdiel.toFixed(1) + "'";
                
                
                $(btn).find("span[item='minute']").html(" (" + rozdiel + ")");
            });
            
            
            setTimeout(function() {

                vyratatCas();
            }, 5000);
            
        };
        
        
        $.each(data, function(){
            
            let cas = this.cas_create;
            cas = cas.replaceAll("-","/");
            cas = new Date(cas);
            let casFormat = cas.toLocaleTimeString();
            
           this.interval = parseInt(this.interval);
            
            
            let tmp = page.tool.createTemplateJSObject(button, {label: this.label, kluc: this.id_model, event: this.event, cas:casFormat, state: this.icon, setting: this.setting });
            tmp = $.parseHTML(tmp);
            $(tmp).data("data", this);
            $(tmp).data("cas", cas);
            
            $(container).append(tmp);


            
            $(tmp).click(click);
            
        });
        
        vyratatCas();

        
        
        
        
        
    };
    let container = null;
    let loadDataComplete = false;
    
    let ajaxZapis = null;

    let loadData = function(){
        loadDataComplete=false;
        ajaxZapis = zapis("/rest/monitor/plc",{data:null,json:true},function(odpoved){
            container = new plc(odpoved.data);
            //loadDataComplete=true;
            ajaxZapis=null;
        });
    };

    
    page.start.registerBind("socket", function(data){
        if(data.metoda=="plc" ){
            if(ajaxZapis) {
                ajaxZapis.abort();
                console.log("Zabity request ...");
            }
            loadData();
            console.log(data);
        } 
    });
    
    loadData();
    
    
})();
