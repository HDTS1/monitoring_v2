(function(){
    
    
    
    
    let userClick = function(){
        let component = $(this).parent();
        let original = $(this);
        let d = $(this).attr("data");
        d = $.parseJSON(d);
        
        
        
        d.selectValue = d.data.rola;
        if(d.data.rola==100){
            d.selectValue += "|" +d.data.centrum;
        }
        
        
        
        let userCanvas = page.start.setCanvas("user_canvas", {
            title:"Nastavenie uzivatela",
             template: "/canvas/monitor/user/setting",
             data: d,
             cmd: function(c){
                 $(c.el).find("button[item='cmd']").click(function (){
                     let frm = $(this).closest("div[item='form']");
                     let d = getData(frm);
                     
                     let tt =  String(d.opravnenie);
                     let [rola, centrum = null] = tt.split("|");
                     let data_zapis ={
                        rola: rola 
                     };
                     
                     if(centrum){
                         data_zapis.centrum=centrum;
                     }
                     
                     
                     zapis("/rest/user/setUserOpravnenie", {data: {data:data_zapis, kluc:d.id_model},json:true}, function(odpoved){
                            let link = "div.user-panel[rola='" + odpoved.data.data.rola+ "'] div.row";
                            if(odpoved.data.data.rola==100){
                                link = "div.user-panel[rola='" +odpoved.data.data.rola+ "'][centrum='"+odpoved.data.data.centrum+"'] div.row";
                            }
                            let container = $(link);
                            $(original).attr("data",JSON.stringify(odpoved.data));
                            $(container).append(component);
                            userCanvas.hide();
                     });
                     
                     
                     
                 });
             }
        });
    };
    
    
    page.monitor = {
        form: {
            validate: function (form) {

                this.test1 = function (result) {
                    $(form).find('*[data-item]').parent().attr("validate", true);
                    $.each(result, function () {
                        $(form).find('*[data-item="' + this.pole + '"]').parent().attr("validate", this.result);
                    });
                };


                this.test = function (result) {
                    $(form).find('*[data-item]').attr("validate", true);
                    $.each(result, function () {
                        $(form).find('*[data-item="' + this.pole + '"]').attr("validate", this.result);
                        if (this.result) {
                            $(form).find('*[data-item="' + this.pole + '"]').removeClass("is-invalid");
                        } else {
                            $(form).find('*[data-item="' + this.pole + '"]').addClass("is-invalid");
                        }

                    });
                };

            }
        },
        create_centrum: function () {
            let canvas = page.start.setCanvas("create_centrum", {
                title: "Create centrum",
                template: "/canvas/monitor/create_centrum",
                cmd: function (m) {

                    $(m.el).find("button[item='cmd']").click(function () {

                        let fr = $(this).closest("div[item='form']");
                        let data = getData(fr);
                        let v = new page.monitor.form.validate(fr);

                        zapis("/rest/centrum/createCentrum", {data: data, json: true}, function (odpoved) {
                            if (!odpoved.result) {
                                v.test(odpoved.data);
                                return false;
                            }
                            canvas.hide();
                        });

                    });
                }
            });
        },
        create: function () {

            let canvas = page.start.setCanvas("create_service", {
                title: "Service",
                template: "/canvas/monitor/servis",
                cmd: function (m) {

                    $(m.el).find("button[item='cmd']").click(function () {

                        let fr = $(this).closest("div[item='form']");
                        let data = getData(fr);
                        let v = new page.monitor.form.validate(fr);

                        zapis("/rest/service/createService", {data: data, json: true}, function (odpoved) {
                            if (!odpoved.result) {
                                v.test(odpoved.data);
                                return false;
                            }
                            canvas.hide();
                        });
                    });
                }
            });

        },

        user: {


            zaradit: function(el){
                let dataEL = $(el).find("div.userData");
                let data = $(dataEL).attr("data");
                data = $.parseJSON(data);
                
                let link = "div.user-panel[rola='" +data.data.rola+ "'] div.row";
                if(data.data.rola==100){
                    link = "div.user-panel[rola='" +data.data.rola+ "'][centrum='"+data.data.centrum+"'] div.row";
                }
                
                
                let container = $(link);
                $(container).append(el);
                $(dataEL).click(userClick);
            },

            load: function(){
                let users = $("div.userAll > div.row >div");
                $.each(users, function(){
                    page.monitor.user.zaradit(this);
                });



            }
        }
    };
    page.monitor.user.load();
})();


