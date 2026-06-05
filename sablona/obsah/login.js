(function(){
    
        page.login = {
            dialog:null,
            dialog_container:null,
            tmp :{},
            kluc:0,
            kluc_overenie: null,
            
            
            form: function(){
                page.login.dialog = new page.start.setCanvas("login", {
                    title:"Login",
                    cmd: function(c){
                        let container = $(c.el).find("div.offcanvas-body");
                        $(container).empty();
                        $(container).append(page.login.tmp[page.login.kluc]);
                    }
                }); 
            },
            
            load: function(){
                let s = $("ss > div[item]");

                $.each(s, function(){
                    let kluc = $(this).attr("item");
                    page.login.tmp[kluc]=$(this);
                    $(this).detach();
                });
                
                
                
                page.login.form();
            },
            
            send: function(frm){
                
                let validateEmail = function (email) {
                    const regex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
                    return regex.test(email);
                };
                let d = getData(frm);
                if(!validateEmail(d.username)){
                    
                    let dialog_email = page.start.setCanvas("Notify_email", {
                        title: "Info",
                        template: "/canvas/IIS/noEdit",
                        data:{
                            text:"Your email address is not in the correct format."
                        }
                    });
                    
                    return false;
                }
                
                
                
                
                
                
                page.login.email = d.username;
                

                
                zapis("/rest/system/overitEmail",{data:d,json:true}, function(odpoved){
                    //debug(odpoved);
                    page.login.kluc_overenie = odpoved.data;
                });
                
                page.login.dialog_code = new page.start.setCanvas("loginCode", {
                    title:"Login",
                    cmd: function(c){
                        let container = $(c.el).find("div.offcanvas-body");
                        $(container).empty();
                        let html = $(page.login.tmp[1]).html();
                        html = $.parseHTML(page.tool.createTemplateJSObject(html,{email:page.login.email}));
                        $(container).append(html);
                    }
                }); 

                page.login.dialog.hide();
                

                return false;
            },
            
            overit: function(frm){
                let d = getData(frm);
                d.kluc = page.login.kluc_overenie;
                zapis("/rest/system/overitKluc",{data:d,json:true}, function(odpoved){
                    //debug(odpoved);
                    
                    
                    if(!odpoved.result){
                        let dialog_email = page.start.setCanvas("Notify_kluc", {
                            title: "Info",
                            template: "/canvas/IIS/noEdit",
                            data:{
                                text:"Your key is incorrect."
                            }
                        });
                        return false;
                    }
                    
                    smerovatGET(window.location);
                });
                
                
                
                return false;
            }
            
            
            
        };
    
    
        page.login.load();
        page.menu.addMenu("Login", "javascript: page.login.form()", "/assets/svg/person.svg", "--bee-player-card");
        
})();

