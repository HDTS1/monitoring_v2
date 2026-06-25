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

    $(document).on("click", ".centrum-component a", function(){
        if ($("#full-page-loader").length === 0) {
            $("body").append(
                '<div id="full-page-loader" style="position: fixed; top: 0; left: 0; width: 100vw; height: 100vh; background: rgba(0,0,0,0.6); backdrop-filter: blur(4px); display: flex; flex-direction: column; align-items: center; justify-content: center; z-index: 99999; color: white; font-family: sans-serif;">' +
                '    <div class="spinner-border text-light mb-3" role="status" style="width: 3.5rem; height: 3.5rem; border-width: 0.3em;">' +
                '        <span class="visually-hidden">Loading...</span>' +
                '    </div>' +
                '    <div style="font-size: 1.2rem; font-weight: 500; letter-spacing: 0.05em; text-transform: uppercase;">Loading Center...</div>' +
                '</div>'
            );
        }
    });
})();
