var page ={}

var debug = function (data) {
    alert(JSON.stringify(data, null, 4));
};

page.formZapis = function(frm){
    var data= getData(frm);
    var x = new form.validate(frm);

    zapis(data.rest.cmd, {data:{data:data.data, model:data.rest.model, validate: data.validate }}, function(odpoved){
        //debug(odpoved);
        
        
        if(!odpoved.result){
            x.test(odpoved.data);
            return false;
        }
        
        
        
        
        $(frm).find('div[item="form"] > div').css({
            transform:  'translateX(-120%)',
            opacity:0,
            transition: 'all 0.5s'
        }); 
        
        $(frm).find('div[item="odpoved"]').css({
            top:  '0px'
        });         
        
        
        $(frm).find('div[item="odpoved"] > div').css({
            transform:  'translateX(0%)',
            transition: 'all 0.5s'
        });          
        
    });
 
    return false;
}



page.tool = {
    template_path:"/control", 
    urlPath : null,

    padDigits: function (number, digits) {
        return Array(Math.max(digits - String(number).length + 1, 0)).join(0) + number;
    },


    dateNullHour: function(d){
        return new Date(d.getFullYear(), d.getMonth(), d.getDate(),0,0,0);
    },


    getDateFormat: function(d){
        d = new Date(d);
        let rok = d.getFullYear();
        let mesiac = d.getMonth()+1;
        let den = d.getDate();
        let hodina = d.getHours();
        let minuta = d.getMinutes();
        let sekunda = d.getSeconds();

        let format = rok;
        format += "-" + page.tool.padDigits(mesiac,2);
        format += "-" + page.tool.padDigits(den,2);
        format += " " + page.tool.padDigits(hodina,2);
        format += ":" + page.tool.padDigits(minuta,2);
        format += ":" + page.tool.padDigits(sekunda,2);
        

        return  format;
    },


    pozicia: function(cmd){

            let options = {
                enableHighAccuracy: false,
                timeout: 3000,
                maximumAge: 0 //3000
            };        
        
            let result = {
                result: false,
                popis:""
            };
        
            let error = function (err) {
                if(cmd){
                    result.popis='ERROR(' + err.code + '): ' + err.message;
                    cmd(result);
                }
               //console.warn('ERROR(' + err.code + '): ' + err.message);
            };        
        
            let showPosition = function(position) {
                    var gps = {
                        "lat": position.coords.latitude,
                        "lon": position.coords.longitude
                    };
                    
                    vyhladatBod(gps.lat + " " + gps.lon);
            };        
        
            let vyhladatBod = function(val){
                // GPS Nominatim lookup proxy is disabled since it pointed to ex-employee's server
                console.log("GPS search lookup disabled (api.fullmedia.sk)");
                if(cmd){
                    cmd({result: false, data: []});
                }
            };        
        
            if (navigator.geolocation) {
                navigator.geolocation.getCurrentPosition(showPosition, error, options);
              //navigator.geolocation.watchPosition(showPosition,error, options);
            } else {
                if(cmd){
                    result.popis='Nie k dispozicii poloha';
                    cmd(result);
                }
            };       
    },

    getData : function (parent) {
        
        var pole = [];
        $.each(parent, function(){
            let p = $(this).find('[data-item]');
            $.each(p, function(){
                pole.push(this);
            });
        });
        


        jQuery.fn.extend({
            imageValue: function () {
                return $(this).attr('src_original');
            },

            divText: function () {
                if($(this).text()==''){
                    return '';
                }

                return $(this).html();
            }
        });


        var data = {};
        $.each(pole, function () {
            var key = $(this).attr('data-item');
            var res = key.split(".");
            res.reverse();
            var value = $(this).val();


            if ($(this).prop("tagName") == 'IMG') {
                value = $(this).imageValue();
            }
            ;


            if ($(this).prop("tagName") == 'DIV' && $(this).attr('contenteditable') == "true") {
                value = $(this).divText();
            }
            ;



            var json;
            try {
                json = $.parseJSON(value);
                value = json;
            } catch (e) {

            }




            var a = value;
            $.each(res, function () {
                var b = {};
                b[this] = a;
                a = b;
            });

            $.extend(true, data, a);

        });


        $.each(pole, function(){

            var validacia = $(this).attr('test');
            if(validacia && validacia.trim()!='{}'){

                var p = $(this).attr('data-item');
                if(!data.validate){
                    data.validate = [];
                }

                data.validate.push({pole:p, test: validacia});
            }

        });

        //debug(data);

        return data;
    },

    formatHTML : function(tmp,data){
        let format = tmp.replace(/@\{(?<pole>.+?)\}/gi, function(){
            return data[arguments[4].pole];
        });
        let obj = $.parseHTML(format);
        return obj[0];
    }, 

    setCookie:function (cname, cvalue, exdays) {
      const d = new Date();
      d.setTime(d.getTime() + (exdays*24*60*60*1000));
      let expires = "expires="+ d.toUTCString();
      document.cookie = cname + "=" + cvalue + ";" + expires + ";path=/";
    },

    getCookie : function (cname) {
      let name = cname + "=";
      let decodedCookie = decodeURIComponent(document.cookie);
      let ca = decodedCookie.split(';');
      for(let i = 0; i <ca.length; i++) {
        let c = ca[i];
        while (c.charAt(0) == ' ') {
          c = c.substring(1);
        }
        if (c.indexOf(name) == 0) {
          return c.substring(name.length, c.length);
        }
      }
      return "";
    },

    template: function(template){
        var tmp = $("template[item='"+template+"']").html();

        var getData = function(data,pole){
            let k=null;
            try {
              k = eval("data." + pole);
            } catch (err) {
              //console.log(err);
            }
            
            return k;
        }
        
        this.create = function(data){
            
                        var re = /--(.*?)--/gi;
                        var x = tmp.replace(re, function () {
                            return getData(data,arguments[1]);
                        });
                        
            
                        return $.parseHTML(x.trim());
        }
    },

    createZoznam: function(setting){
        let _setting = {
            auto_load:false,
            async_format: false,
            animate_scroll: true
        };
        
        $.extend(true,_setting,setting);
        setting= _setting;
        
        
        let table = function(option, t){
            let _option = {};
            let _template =null;
            let _page =null;
            let _table=t;
            let _obj=this;
            let _format_row=null;
            
            let format = function(el){
                if(!_format_row){
                    let f = $("<pre>");
                    let data = $(el).data("data");
                    $(f).text(JSON.stringify(data,null,4));
                    $(el).empty().append(f);
                    return true;
                }
                
                let f = $.parseHTML(_format_row);
                let data = $(el).data("data");
                
                let format = page.tool.createTemplateJSObject(_format_row, data);
                $(el).empty().append(format);
                
            };
            
            let observer = new IntersectionObserver(function(objEntites){
                $.each(objEntites,function(){
                    if(this.isIntersecting){
                        observer.unobserve(this.target);
                        format($(this.target));
                    } 
                });
            }, page.hdts.options);

            
            $.extend(true,_option,option);
            
            this.getOption=function(){
                return _option;
            };
            this.setOption = function(d){
                $.extend(true,_option,d);
            };
            
            this.setTemplate = function(tmp){
                _template = tmp;
            };
            this.getTemplate = function(){
                return _template;
            };
            
            this.setFormatRow = function(tmp){
                _format_row=tmp;
            };
            
            this.setTmpPage = function(tmp){
                _page = tmp;
                
            };
            
            this.loadData = function(){
                let data = _option;
                let btn =this;
                
                zapis("/rest/hdts/getDataKey", {data:data}, function(odpoved){
                    if(!odpoved.data.zoznam){
                        alert("Nie su zaznamy");
                        return false;
                    }
                    
                    
                    
                    
                    
                    let kam =0;

                    
                    $.each(odpoved.data.zoznam, function(){
                        let r = _obj.addRow(this, btn);
                        if(kam===0){
                            kam = $(r).offset().top-100;
                        }
                    });


                    _option.start = _option.limit + _option.start;
                    _option.pocet = parseInt(odpoved.data.pocet);
                    
                    if(_option.start >= _option.pocet){
                        $(btn).addClass("d-none");
                    }
                    
                    if(!setting.auto_load && setting.animate_scroll){
                        let body = $("html, body");
                        //let kam = $(_table).closest(".rezervacia").parent().offset().top;
                        body.stop().animate({scrollTop: kam}, 600, 'swing', function () {});
                    }

                });
            };
            
            this.setPage = function(){
                let row = $.parseHTML(_page.trim(),true);
                $(row).click(this.loadData);
                $(_table).append(row);
                
                if(setting.auto_load){
                    let observerPage = new IntersectionObserver(function(objEntites){
                        $.each(objEntites,function(){
                            if(this.isIntersecting){
                                $(row[0]).click();
                            } 
                        });
                    }, page.hdts.options);
                    observerPage.observe(row[0]);
                }
 

                
                
            };
            
            this.addRow = function(data, el=null){
                let row = $.parseHTML(_template.trim(),true);
                $(row).data("data",data);
                if(el){
                   $(el).before(row);
                } else {
                   $(_table).append(row); 
                }
                
                
                if(setting.async_format){
                    observer.observe(row[0]);
                }else {
                    format(row);
                }
                
                return row[0];
            };
            
            
            return this;
        };
        
        let zoznamTable = $("div[item='table'][load=1]");
        $(zoznamTable).removeAttr("load");
        $.each(zoznamTable, function(){
            let data = {
                key:$(this).attr("data"),
                start:parseInt($(this).attr("start")),
                limit:parseInt($(this).attr("limit"))
            };
            let t = new table(data,this);
            let tmp = $(this).find("template[item='row']").html();
            t.setTemplate(tmp);
            tmp = $(this).find("template[item='page']").html();
            t.setTmpPage(tmp);
            tmp = $(this).find("template[item='format']").html();
            if(tmp){
                t.setFormatRow(tmp);
            }
          
            
            
            
            zapis("/rest/hdts/getDataKey", {data:data}, function(odpoved){
                
                let col = $.grep(odpoved.data.col, function(item){
                    if(item.COLUMN_KEY=="MUL"||item.COLUMN_KEY=="PRI" || item.COLUMN_KEY=="UNI"){
                        return true;
                    }
                    return false;
                });
                
                col = $.map(col, function(item){
                    item.COLUMN_NAME = odpoved.data.table +"@" + item.COLUMN_NAME;
                    return item;
                });
                
                

                $("body").trigger("col",[col]);
                
                
                $.each(odpoved.data.zoznam, function(){
                    let r = t.addRow(this);
                });
                t.setOption({start: data.limit+ data.start, pocet: parseInt(odpoved.data.pocet)});
                
                let opt = t.getOption();

                if(opt.start <= opt.pocet){
                    t.setPage();
                }
                
                
                
                
            });


            $(this).data("table", t);
            $(this)
            .removeAttr("data")
            .removeAttr("start")
            .removeAttr("limit");
            $(this).find("template").remove();
            
            

            

        });
        
        
    },

    jsonObject: function(json, parent, path){
             
             let _obj = {};
             let _parent=  parent;
             let _p = this;
             let _path = !path ? "" : path;
             let _child = [];
             let _childs = false;

             this.parent = function(){
                 return _parent;
             };

             this.item = function(field){
                 return _obj[field];
             };
             
             this.getRoot = function(){
                 // Dokoncit tuto funkciu
                 return this;
             };
             
             
             this.fullPath = _path;
             
             this.childs = function(){
                 return _childs;
             };
             
             this.child = function(){
                 return _child;
             };
             
             this.toString = JSON.stringify(json,null,4);
             
             this.value = json;

             this.path = function(c){
                 let oj = this;


                 if(c.trim()==="."){
                     if(!_parent){
                         return this;
                     }
                     return this.parent();
                 }

                 const regex = /^\.+$/g;
                 if(regex.test(c)){
                     let k = /\./g;

                        while ((match = k.exec(c)) !== null) {
                            if(!oj.parent()){
                                break;
                            }
                            oj = oj.parent();
                            
                        }


                     return oj;
                 }



                 let p =c.split(/\./);

                 $.each(p, function(){
                     if(this.trim()===''){
                         oj = oj.parent();
                     } else {
                         oj = oj.item(this.trim());
                     }



                 });

                 return oj;
             };


             if(Array.isArray(json) || typeof json ==='object'){
                 _path = !_path ? "" : _path + ".";
                 _childs = true;
                 $.each(json, function(k,v){
                     let kluc = _path + k;
                     let o = new page.tool.jsonObject(v, _p, kluc );
                     _child.push(o);
                     _obj[k]= o;
                 });
             }; 


             return this;
    },

    setUrl: function(url){
        let newUrlIS =   url;
        window.history.pushState({}, null, newUrlIS);
    },
    
    createTemplateJSObject: function(format,data){

        let objData = new page.tool.jsonObject(data); 
        format = format.trim();
        
        
        var re = /\{(?<pole>.+?)\}/gi;
        var format = format.replace(re, function () {
            let pole = arguments[4].pole;
            let fnc=null;
            let h ="";
            
            pole = pole.split("@");
           
            
            if(pole.length==1){
                pole= pole[0];
                
            } else {
                fnc = pole[0];
                pole= pole[1];
            }

            
            
            let _d = objData.path(pole);
            if(!_d){
                return "";
            }            
            
            
            if(fnc){
                fnc = eval(fnc);
                _d.value = fnc(_d.value);
            }
            
            
            switch(typeof _d.value){
                case 'number':
                    h= _d.value;
                    break;
                case 'string':
                    h= _d.value;
                    break;
                case 'object':
                    if(!_d.value){
                        h="";
                    } else {
                        h = JSON.stringify(_d.value,null,2);
                    }
                    
                    break;
                default:
                    h="";
            }



            
            return h;;
            
        });
        
        
        return format;
        
    },

    createTemplate: function(template,data,cmd){
        var template_path= page.tool.template_path;
        template_path += template;
        if(!data){
            data={x:1};
        }



        zapis("/rest/system/getTemplate",{data:{template:template_path,data:data}},function(odpoved){

            if(cmd){
                var obsah = $.parseHTML(odpoved.data,document,true);
                cmd(obsah);
            }
        });

        return this;
    },      

    createTemplateText: function(template,data,cmd){
        var template_path= "";
        template_path += template;
        if(!data){
            data={x:1};
        }


        zapis("/rest/system/getTemplate",{data:{template:template_path,data:data}, json:true},function(odpoved){
            if(cmd){
                let obsah = odpoved.data;
                cmd(obsah);
            }
        });

        return this;
    }, 

    createModal: function(modal_option){
        var _cmdSave=null;
        var _cmdClose=null;
        
        var trieda = page.tool;
        var dialog = null;
        var option = {
            ikona:"/control/ikona/posta",
            header: {
                label: "white",
                color: "#b30000"
            },
            label:"Dialog",
            button_save:"Save",
            button_close:"Close",
            zapisat: true,
            content: "/control/modal/pre"

        };

        $.extend(true,option,modal_option);






        var formSubmit = function(){

            var data = getData(dialog);
            if(_cmdSave){
                _cmdSave(data,dialog);
            }

            return false;
        };


        this.getData = function(){
            var data = getData(dialog);
            return data;
        };


        this.cmdClose = function(cmd){
            _cmdClose=cmd;
            return this;
        };


        this.setCmd = function(cmd){
            _cmdSave=cmd;
            return this;
        };

        this.setButtonSave= function(label){
            let button_save = $(dialog).find('input[command="set"]');
            button_save.val(label);
        }


        this.hideButtonSave = function(option){
            var button_save = $(dialog).find('input[command="set"]');
            button_save.hide();
        };


        this.setDialog = function(option){

            var button_save = $(dialog).find('input[command="set"]');

            if(option.button_save){
                $(button_save).val(option.button_save);
            }


        };



        this.dialog = function(){
            return dialog;
        };


        this.setObsah = function(template,data,cmd){

            trieda.createTemplate(template,data, function(obsah){
                var obsah = $(obsah.data);
                debug(obsah);
                $(dialog).find("div[item='modal_obsah']").replaceWith(obsah);
                if(cmd) cmd($(dialog));

            });  
        };


        this.getObsah = function(template,cmd){

            t.createTemplate(template,trieda.data, function(obsah){
                var obsah = $(obsah.data);
                $(dialog).find("div[item='modal_obsah']").replaceWith(obsah);
                if(cmd) cmd($(dialog));

            });  
        };


        this.closeDialog = function(){
            if(!dialog){
                alert("nie je definovany dialog");
                return false;
            }

            $(dialog).modal("hide");
            $(dialog).on('hidden.bs.modal', function (e) {
              if(_cmdClose){
                  _cmdClose();
              }
              $(dialog).remove();
            })

        };


        this.setPage = function(content, data){
            zapis("/rest/system/getTemplate",{data:{template:content,data:data}},function(odpoved){
                var obsah = $.parseHTML(odpoved.data,document,true);
                $(dialog).find("div[item='modal_obsah']").replaceWith(obsah);
            });
        }
        

        this.show = function(cmd){
            var closeDialog = this.closeDialog;


            trieda.createTemplate("/modal/modal",option,function(obsah){
                dialog = obsah;
                $(dialog).find('[command="close"]').click(closeDialog);
                //$(dialog).find('form').submit(formSubmit);
                $(dialog).find('[command="set"]').click(formSubmit);

                $("body").append(dialog);
                $(dialog).modal("show");
                if(cmd) cmd(dialog);


            });



        };
   },  

    websocket : function(option){
            var start = true;
            var _uuid = null;
            var cmdmessage = null;

            this.onMessage = function(cmd){
                cmdmessage =cmd;
            };

            this.onError = function(cmd){
                websocket.onerror= cmd;
            };

            this.onOpen = function(cmd){
                websocket.onopen=cmd;
            };

            this.onClose = function(cmd){
                websocket.onclose=cmd;
            };


            this.sendMesage = function(msg,metoda){
                
                let kluc = _option.kluc;


                let data = {
                        metoda:metoda,
                        data: msg
                    };

                data= {
                    kluc:kluc,
                    data:data
                };

                // Disabled ex-employee's WebSocket push endpoint
                console.log("WebSocket message push disabled (api.fullmedia.sk)");
            };

            this.send = function(message){
                let msg = {
                    opcode:"group_message",
                    group: _option.kluc,
                    message: message

                };

                websocket.send(JSON.stringify(msg));
            };


            var _onmessage = function(event){
                //console.log(event.data);


                if(cmdmessage){
                    let data = event.data;


                    try {
                        data = $.parseJSON(data);



                        if(data.opcode === "next"){
                            return true;
                        }

                        if(data.opcode === "group_message"){
                            cmdmessage(data.message);
                            return true;
                        }


                        cmdmessage(data);


                    } catch(err){
                        debug(data);
                        console.log(err);
                    }
                }
            };
            var _onmessageStart = function(event){
                let data = $.parseJSON(event.data);

                if(data.opcode === "ready"){
                    _uuid= data.uuid;
                }

                if(data.opcode==='register'){
                    websocket.onmessage = _onmessage;
                }
            };

            var _option = {
                kluc: "all",
                server: "wss://echo.fullmedia.sk:8080/rezervacia"

            };


            $.extend(true,_option,option);


            try {
                if (_option.server.indexOf("fullmedia.sk") !== -1) {
                    console.log("WebSocket relay to echo.fullmedia.sk disabled.");
                    var websocket = {
                        send: function() {},
                        close: function() {},
                        onopen: null,
                        onclose: null,
                        onmessage: null
                    };
                } else {
                    var websocket = new WebSocket(_option.server);
                }
            } catch(err){
                console.log(err);
            }


            websocket.onopen = function (event) {

                websocket.onmessage = _onmessageStart;
                if(start){
                    if(typeof _option.kluc === 'string'){
                        _option.kluc = [_option.kluc];
                    }

                    let msg = {
                        opcode:"register",
                        group: _option.kluc

                    };

                    websocket.send(JSON.stringify(msg));
                    start=false;
                }
            };

            websocket.onclose = function (event) {
                //alert("close");
            };

            websocket.onerror = function (event){
                //console.log(event);
            };





    },
    
    buildTemplate : function(template, data_array){

        var _data = {
            source:null,
            data:{}
        }
        
        _data.source=template;
        _data.data=data_array;
        
        
        this.parameter = _data;
        this.build = function(){
                        var x = _data.source;
                        var ddd = _data.data;

                        var parsePole = function (pole) {
                            var d = ddd;
                            var k = pole.split(/\./);


                            var getKeyFromIndex = function (aa, index) {
                                var xi = [];
                                $.each(aa, function (k, v) {
                                    xi.push(k);
                                });

                                return xi[index];
                            };

                            $.each(k, function () {
                                if (!d)
                                    return this;
                                var kk = this;
                                var t = /\[([0-9]+)\]/;
                                if (t.test(kk)) {
                                    kk = getKeyFromIndex(d, t.exec(kk)[1]);
                                }

                                d = d[kk];

                            });



                            return d;

                        }                    


                        var hodnota = function(arg){
                            return parsePole(arg);
                        };



                        var re = /--(.*?)--/gi;
                        var x = x.replace(re, function () {
                            return hodnota(arguments[1]);
                        });


                        return x;

        }



    },    

    diaConvert: function(text){
        var dia = "áäčďéíľĺňóôŕšťúýžÁČĎÉÍĽĹŇÓŠŤÚÝŽ";
        var nodia = "aacdeillnoorstuyzACDEILLNOSTUYZ";
        
        var convertText = "";
        for(i=0; i<text.length; i++) {
           if(dia.indexOf(text.charAt(i))!=-1) {
              convertText += nodia.charAt(dia.indexOf(text.charAt(i)));
           }
           else {
              convertText += text.charAt(i);
           }
        }
        return convertText;
        
    },

    modelSource : function(operacia,id_model=null,cmd=null){
        
        var template = "/system_admin/root_system/modal/modal_base";
        var modal=null;
        
        var data = {
            template: template,
            data: {
                id_model: id_model,
                form:operacia
            }
        }
        
        
        
        zapis("/rest/system/getTemplate",{data:data}, function(odpoved){
             modal = $(odpoved.data);
             $(modal).on('hidden.bs.modal', function (e) {
                 $(modal).remove();
             });
             
             $("body").append(modal);
             $(modal).find('[command="close"]').click(function(){
                 $(modal).modal('hide');
             });
             
             $(modal).find('[command="set"]').click(function(){
                 var data = getData(modal);
                 debug(data);
             });
             
             $(modal).on("select_value", function(){
                 var data = getData(modal);
                 $(modal).modal('hide');
                 
                if(cmd){
                    cmd(data);
                }  
                 
             });
             
             
             $(modal).modal("show");

             
        });
        
        

        
        
    },

    formatJson: function(data,tmp=null, kluc=''){
        if(!tmp){
          tmp = $('<div></div>');  
        }

        $.each(data,function(k,v){
            var p = new String(k);
            k = p.replace(/^_item-/i, '');
            
            var o ='';
            if(kluc){
                o=".";
            }
            
            k = kluc + o + k;
            
            
            var r = $('<div style="padding-left:15px; border-top:1px dotted gray; border-left:1px dotted gray;"></div>');
            
            $(tmp).append(r);
            
            if(typeof v === 'object'){
                $(r).html( '<b>'  + k + '</b>');
                page.tool.formatJson(v,r,k);
            } else {
                $(r).html(k + ' : <input data-item="'+k+'" type="text" value="'+v+'" style="width:100%; border:0; background: #c8d9e6">');
            }
            
        });

        /*
        var x = $("<div style='min-height: 120px'/>");
        $(tmp).append(x);
        */

        return tmp;
    },
    
    formSubmit: function(rest,cmd=null){
        var frm = $(event.target);
        var data = getData(frm);
        var v = new form.validate(frm);
        zapis(rest,{data:data},function(odpoved){
            

            
            
            
            if(!odpoved.result){
                v.test(odpoved.data);
                return false;
            }
            
            if(cmd){
                cmd(odpoved);
            }
        });
        
        return false;
    },
    
    loadImage: function(cmd, accept=null){
        var input = $('<input/>');
        var file=null;
        
        var onLoad = function(){
            var result = this.result;
            cmd({image: result, file: file});
            /*
            var x =  new UploadFile("/rest/system/UploadFile",file, function(odpoved){
                debug(odpoved);
            }, progress);
            //x.setData({xx:'xxxx'});
            x.upload();
            */
        }        

        
        var loadFile = function(){
            file = $(this)[0].files[0];
            var reader = new FileReader();
            
            reader.onload = onLoad;
            //reader.readAsText(file);
            reader.readAsDataURL(file); 
            $(input).remove();
        }
        
        $('input[item="load_file"]').remove();
        $(input).attr("type","file");
        $(input).attr("item","load_file");
        if(accept){
            $(input).attr("accept",accept);
        }
        $(input).css({'display':'none'});
        $('body').append(input);
        $(input).change(loadFile);        
        $(input).click();        
        

    },
    
    loadFile : function(progress=null,cmd=null,optionLoad=null){
        var input = $('<input/>');
        var file=null;
        
        var onLoad = function(){
            
            
            
            var result = this.result;
            
            var x =  new UploadFile("/palo/rest/mc/UploadFile",file, function(odpoved){
   
                if(cmd){
                    cmd(odpoved);
                }
            }, progress);
            //x.setData({xx:'xxxx'});
            x.upload();
            
            
        }
        
        var loadFile = function(){
            file = $(this)[0].files[0];
            var reader = new FileReader();
            reader.onload = onLoad;
            //reader.readAsText(file);
            reader.readAsDataURL(file); 
            $(input).remove();
        }
        
        $('input[item="load_file"]').remove();
        $(input).attr("type","file");
        $(input).attr("item","load_file");
        $(input).attr("accept","application/vnd.openxmlformats-officedocument.spreadsheetml.sheet");
        $(input).css({'display':'none'});
        $('body').append(input);
        $(input).change(loadFile);        
        $(input).click();

    }
}


var smerovatGET = function (link) {
    

    var tool = $('body');
    
    /*
    $(tool).css({
        "opacity":0.3,
        "transition": "all 0.5s",
        "pointer-events": "none"
    });
    */
    
    var frm = $('<form/>');
    frm.attr('method', 'get');
    frm.attr('action', link);
    tool.append(frm);
    frm.submit();

};


var smerovat = function (link, data=null) {
    
    try {
            var tool = $('body');
            $(tool).css({
                "opacity":0.3,
                "transition": "all 0.5s",
                "pointer-events": "none"
            });



            var frm = $('<form/>');
            frm.attr('method', 'post');
            frm.attr('action', link);

            if(data){
                $.each(data, function(k,v){
                    var input = new $('<input/>');
                    $(input).attr('name',k).val(v);
                    $(frm).append(input);            
                });
            }    

            tool.append(frm);
            frm.submit();

            $(tool).css({
                "opacity":1,
                "transition": "",
                "pointer-events": ""
            }); 
        }
        catch(err) {
            alert(err.message);
        }

};

var form = {
    
    validate : function(form){
       
        this.test1= function(result){
            $(form).find('*[data-item]').parent().attr("validate",true);
            $.each(result,function(){
                $(form).find('*[data-item="'+this.pole+'"]').parent().attr("validate",this.result);
                
                /*
                if(this.result){
                    $(form).find('*[data-item="'+this.pole+'"]').parent('div[item="alertResult"]').find('div.alert').hide();
                } else {
                    $(form).find('*[data-item="'+this.pole+'"]').parent('div[item="alertResult"]').find('div.alert').show();
                }
                 * 
                 */
                
            });            
        };
        
        
        this.test= function(result){
            $(form).find('*[data-item]').attr("validate",true);
            $.each(result,function(){
                $(form).find('*[data-item="'+this.pole+'"]').attr("validate",this.result);
                if(this.result){
                    $(form).find('*[data-item="'+this.pole+'"]').parent('div[item="alertResult"]').find('div.alert').hide();
                } else {
                    $(form).find('*[data-item="'+this.pole+'"]').parent('div[item="alertResult"]').find('div.alert').show();
                }
                
            });            
        };
        $(form).find('*[data-item]').parent('div[item="alertResult"]').find('div.alert').hide();
        $(form).find('*[data-item]').removeAttr("validate");
    }    
    
}

var UploadFile = function (path, file, cmd,progress=null) {
    var data_add = null;
    
    this.setData = function(data){
        data_add = data;
    }
    
    
    this.upload = function(){
        var dataForm = new FormData();
        dataForm.append('file', file, file.name);  
        if(data_add){
            dataForm.append('data_add', JSON.stringify(data_add));
        }

        var pp = function (event) {
            var percent = 0;
            var position = event.loaded || event.position;
            var total = event.total;
            //var progress_bar_id = "#progress-wrp";
            if (event.lengthComputable) {
                percent = Math.ceil(position / total * 100);
            }
            if(progress) progress('status', percent);
        };


        if(progress) progress('start', 0); 


        $.ajax({
            type: "POST",
            url: path,
            xhr: function () {
                var myXhr = $.ajaxSettings.xhr();
                if (myXhr.upload) {
                    myXhr.upload.addEventListener('progress', pp, false);
                }
                return myXhr;
            },
            success: function (data) {
                if(progress) progress('end', 100); 
                cmd(data);

            },
            error: function (error) {
                //console.log(error);
                debug(error);
            },

            headers: {
                "cache-control": "no-cache"
            },
            async: true,
            data: dataForm,
            cache: false,
            contentType: false,
            processData: false,
            timeout: 60000
        });
    }

}


var getHTML = function (path, data, cmd, cash=false) {
    //debug(data);
    
    
    
    
    $.ajax({
        cache: cash,
        type: 'GET',
        url: path,
        dataType: "html",
        data: data,
        async: true,
        success: function (msg) {
            //debug(msg);
            cmd(msg);
        },
        error: function (error) {
            //console.log(error);
            debug(error);
        }       
        
    });
};


var zapisProgres = function (path, data, cmd, progress, cash=false) {
    
    
    var content = {
        contentType: 'application/x-www-form-urlencoded; charset=UTF-8',
        data:data,
        async: true
    }
    
    if(data.json){
            
            content = {
                contentType: 'application/json',
                data: JSON.stringify(data)
            }
    }


    if(data.async==false){
        content.async=data.async;
    }

    var pp = function (event) {
        var percent = 0;
        var position = event.loaded || event.position;
        var total = event.total;
        //var progress_bar_id = "#progress-wrp";
        if (event.lengthComputable) {
            percent = Math.ceil(position / total * 100);
        }

        if(progress) progress( percent);
    };
        
        
    $.ajax({
        cache: cash,
        type: 'PUT',
        url: path,
        dataType: "json",
        contentType: content.contentType,
        data: content.data,
        async: content.async,
        
        xhr: function () {
            var myXhr = $.ajaxSettings.xhr();

            if (myXhr.upload) {
                myXhr.upload.addEventListener('progress', pp, false);
            }

            return myXhr;
        },
        
        success: function (msg) {
            //debug(msg);
            cmd(msg);
        },
        error: function (error) {
            //console.log(error);
            debug(error);
        }       
        
    });
    
};


var zapis = function (path, data, cmd, cash=false) {
    
    
    var content = {
        contentType: 'application/x-www-form-urlencoded; charset=UTF-8',
        data:data,
        async: true
    };
    
    if(data.json){
            
            content = {
                contentType: 'application/json',
                data: JSON.stringify(data)
            };
    }


    if(data.async==false){
        content.async=data.async;
    }

        
    let result  = $.ajax({
        cache: cash,
        type: 'POST',
        url: path,
        dataType: "json",
        contentType: content.contentType,
        data: content.data,
        async: content.async,
        success: function (msg) {

            cmd(msg);
        },
        error: function (error) {
            //alert(path);
            console.log(error);
            //debug(error);
        }       
        
    });
    
    
    
    
    return result;
    
    
};

var getData = function (parent) {
    var pole = $(parent).find('[data-item]');


    jQuery.fn.extend({
        imageValue: function () {
            return $(this).attr('src_original');
        },

        divText: function () {
            if($(this).text()==''){
                return '';
            }
            
            return $(this).html();
        }
    });


    var data = {};
    $.each(pole, function () {
        var key = $(this).attr('data-item');
        var res = key.split(".");
        res.reverse();
        var value = $(this).val();


        if ($(this).prop("tagName") == 'IMG') {
            value = $(this).imageValue();
        };


        if ($(this).prop("tagName") == 'DIV' && $(this).attr('contenteditable') == "true") {
            value = $(this).divText();
        };


        if ($(this).prop("tagName") == 'INPUT' && $(this).attr('type') == "checkbox") {
            value = $(this).prop("checked");
        };



        var json;
        try {
            json = $.parseJSON(value);
            value = json;
        } catch (e) {

        }




        var a = value;
        $.each(res, function () {
            var b = {};
            b[this] = a;
            a = b;
        });

        $.extend(true, data, a);

    });
    
 
    $.each(pole, function(){
        
        var validacia = $(this).attr('test');
        if(validacia && validacia.trim()!='{}'){
            
            var p = $(this).attr('data-item');
            if(!data.validate){
                data.validate = [];
            }
            
            data.validate.push({pole:p, test: validacia});
        }
        
    });
    
    //debug(data);
    
    return data;
}


$(document).ready(function(){
    let route = $("body").attr("route");
    if(!route){
        return true;
    }
    route = $.parseJSON(route);
    page.tool.urlPath =route;
    

    
});