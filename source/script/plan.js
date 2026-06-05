/*
const pole1 = [1, 2, 3, 4];
const pole2 = [3, 4, 5, 6];

const pole2Set = new Set(pole2);
const intersect = pole1.filter(value => pole2Set.has(value));

console.log(intersect);  
*/



page.planEdit = {
        editUnit_calendar: function(id_model_unit, el, cmd_end){
            
            let zamok = $(el).attr("done");
            
            if(zamok==99){
                
                let dialog_termin = page.start.setCanvas("Notifyxx", {
                    title: "Info",
                    template: "/canvas/IIS/noEdit",
                    data:{
                        text:"No edit unit, already done"
                    }
                });

                return true;
            }
            
            
            
            let akcia = {
                open_contract: function(){

                    let contract = $(el).attr("contract");
                    smerovatGET(`/iis/contract/${contract}`);
                },
                done: function(){
                    alert("Nutne zapracovat zmenu");
                    return true;
                    
                    
                    let markDone = function (d) {

                        let data = {
                            person: [],
                            id_model_unit: id_model_unit
                        };


                        let dataValues = [];
                        let findSelectedInParent = $(d.el).find("div[item='listPlayers'] div.list-item[select=1]");

                        $.each(findSelectedInParent, function () {
                            let dataAttr = $(this).attr("data");
                            dataAttr = $.parseJSON( dataAttr);
                            dataValues.push(dataAttr);

                        });

                        data.person = dataValues;

                        if(data.person.length==0){
                            alert("Set players");
                            return false;
                        }

                        zapis("/rest/iis/setUnitDone", {data: data, json:true}, function(odpoved){

                            if(!odpoved.result){
                                debug(odpoved);
                                return false;
                            }

                            $(el).attr("state_done",99);
                            $(el).find('div.row-personal-data').addClass("notify-change");
                            
                            markDoneCanvas.hide();

                        });

                    };
                    
                    let source_unit= $(el).find("div[source_unit]").attr("source_unit");
                    source_unit = $.parseJSON(source_unit);
                    
                    if(source_unit.length>0){
                        
                        let testSource = $.grep(source_unit, function(item){
                            return !item.source;
                        });

                        if(testSource.length>0){
                            alert("Not complete setup source !!!!!!!!");
                            return false;
                        }
                        
                    }
                    
                    
                    let markDoneCanvas = page.start.setCanvas("markDoneCanvas",{
                        title: "Mark done",
                        template: "/canvas/IIS/markDone",
                        data: {
                            id_model_unit: id_model_unit
                        },
                        cmd: function(c){
                            
                            let k = $(el).clone(false);
                            let button = $(c.el).find("button[cmdButton]");
                            let unit = $(c.el).find("div[item='unit']");

                            $(unit).append(k);
                            
                            
                            
                            $(button).click(function(){
                                let cmd = $(this).attr("cmdButton");
                                markDone(c);
                                //markDoneCanvas.hide();
                            });
                        
                        }
                    });
                },
                plan: function(){
                    page.planEdit.editTime(id_model_unit,el);
                }
            };
            
            
            
            
            let editUnit = page.start.setCanvas("editUnit",{
                title: "Edit activity unit",
                template: "/canvas/IIS/editUnit",
                data: {
                    id_model_unit: id_model_unit
                },
                cmd: function(c){
                    
                    //let k = $(el).clone(false);
                    let k = "<div>Tento prvok na trennigovu jednotku kvoli calendaru prepracovat !!!!</div>";
                    
                    
                    let button = $(c.el).find("button[cmdButton]");
                    
                    let unit = $(c.el).find("div[item='unit']");
                    $(unit).append(k);
                    
                    
                    
                    $(button).click(function(){
                        let cmd = $(this).attr("cmdButton");
                        akcia[cmd]();
                        editUnit.hide();
                    });
                 
                }
            });
           

    },
    editUnit: function(id_model_unit, el, cmd_end){
            
            let zamok = $(el).attr("state_done");
            
            if(zamok==99){
                
                let dialog_termin = page.start.setCanvas("Notifyxx", {
                    title: "Info",
                    template: "/canvas/IIS/noEdit",
                    data:{
                        text:"No edit unit, already done"
                    }
                });

                return true;
            }
            
            
            
            let akcia = {
                open_contract: function(){
                    let contract = $(el).attr("contract");
                    smerovatGET(`/iis/contract/${contract}`);
                },
                done: function(){
                    
                    let markDone = function (d) {

                        let data = {
                            person: [],
                            id_model_unit: id_model_unit
                        };


                        let dataValues = [];
                        let findSelectedInParent = $(d.el).find("div[item='listPlayers'] div.list-item[select=1]");

                        $.each(findSelectedInParent, function () {
                            let dataAttr = $(this).attr("data");
                            dataAttr = $.parseJSON( dataAttr);
                            dataValues.push(dataAttr);

                        });

                        data.person = dataValues;

                        if(data.person.length==0){
                            alert("Set players");
                            return false;
                        }

                        zapis("/rest/iis/setUnitDone", {data: data, json:true}, function(odpoved){

                            if(!odpoved.result){
                                debug(odpoved);
                                return false;
                            }

                            $(el).attr("state_done",99);
                            $(el).find('div.row-personal-data').addClass("notify-change");
                            
                            markDoneCanvas.hide();

                        });

                    };
                    
                    let source_unit= $(el).find("div[source_unit]").attr("source_unit");
                    source_unit = $.parseJSON(source_unit);
                    
                    if(source_unit.length>0){
                        
                        let testSource = $.grep(source_unit, function(item){
                            return !item.source;
                        });

                        if(testSource.length>0){
                            alert("Not complete setup source !!!!!!!!");
                            return false;
                        }
                        
                    }
                    
                    
                    let markDoneCanvas = page.start.setCanvas("markDoneCanvas",{
                        title: "Mark done",
                        template: "/canvas/IIS/markDone",
                        data: {
                            id_model_unit: id_model_unit
                        },
                        cmd: function(c){
                            
                            let k = $(el).clone(false);
                            let button = $(c.el).find("button[cmdButton]");
                            let unit = $(c.el).find("div[item='unit']");

                            $(unit).append(k);
                            
                            
                            
                            $(button).click(function(){
                                let cmd = $(this).attr("cmdButton");
                                markDone(c);
                                //markDoneCanvas.hide();
                            });
                        
                        }
                    });
                },
                plan: function(){
                    page.planEdit.editTime(id_model_unit,el);
                }
            };
            
            
            
            
            let editUnit = page.start.setCanvas("editUnit",{
                title: "Edit activity unit",
                template: "/canvas/IIS/editUnit",
                data: {
                    id_model_unit: id_model_unit
                },
                cmd: function(c){
                    
                    let k = $(el).clone(false);
                    
                    
                    
                    let button = $(c.el).find("button[cmdButton]");
                    
                    let unit = $(c.el).find("div[item='unit']");
                    $(unit).append(k);
                    
                    
                    
                    $(button).click(function(){
                        let cmd = $(this).attr("cmdButton");
                        akcia[cmd]();
                        editUnit.hide();
                    });
                 
                }
            });
           

    },
    editTime: function(id_model_unit, el, cmd_end){
        
        
        let cmd = "contract_editTime";
        let dialog_termin = page.start.setCanvas(cmd, {
            title: "Select Date",
            template: "/canvas/IIS/client/" + cmd,
            data: {
                kluc:id_model_unit
            },
            cmd: function (c) {
                
                
                page.start.registerBind("control_range", function (data) {
                    
                    let x  = data.minute/(24*60);
                    x = x*100;
                    
                    $(c.el).find("div.viewTime").css({left: x+"%"});




                });
                
                
                
                let init = $(c.el).find("div.init");
                let initDate = $(init).attr("date");
                let initTime = $(init).attr("time");
                

                
                let _selectDate = new Date();
                
                if(!initTime){
                    initTime= 7*60;
                }
                
                
                if(initDate && initTime){
                    _selectDate.setTime((parseFloat(initDate)*60)*1000);
                    
                }
                

                
                
                
                
                
                let alokaciaSource=function(den){
                    
                    let keySource = [];
                    
                    let a = $(c.el).find("div.plan.source");
                    $.each(a, function(){
                        keySource.push($(this).attr("kluc"));
                    });
                    
                    let request = {
                        keySource: keySource,
                        day: page.tool.getDateFormat(den)
                    };
                    
                    
                    
                    
                    zapis("/rest/iis/alokacia_zdrojov", {data:request}, function(odpoved){
                        
                        
                        
                        $.each(a, function(){
                           let kluc =  $(this).attr("kluc");
                           let time_line = $(this).find("div.time-line");
                           $(time_line).empty();
                           
                           $.each(odpoved.data[kluc],function(){
                                
                                let div = $.parseHTML('<div class="time-zaznam" style=" left: 10%; width: 30%; " start="300" end="800" />');
                                let den = 24 * 60;

                                let g = ((this.end-this.start) / den) * 100;
                                let s = (this.start / den) * 100;

                                $(div).attr("start", this.start);
                                $(div).attr("end", this.end);


                                $(div).css({width: g + "%"});
                                $(div).css({left: s + "%"});
                                
                                $(time_line).append(div);
                           });


                       });
                       
                       let t = parseInt($("#time > div").controlValue());
                       //vyhodnotenie(t);
                       
                       
                    });
                    

                    
                    
                };
                
                let getSourceSetting = function(){
                    
                    let d = [];
                    
                    let plan_container = $(c.el).find("div.plan-container");
                    $.each(plan_container, function(){
                        let kluc_cinnost = $(this).attr("data");
                        kluc_cinnost = $.parseJSON(kluc_cinnost);
                        
                        let x = {
                            kluc_cinnost:kluc_cinnost.zdroje_zaznam_parent,
                            type:[]
                        };
                        
                        let types = $(this).find("div.plan.type");
                        
                        $.each(types, function(){
                            let klucType = $(this).attr("kluc");
                            let source = $(this).parent().find("div.plan.source[select='1']");
                            
                            let s = {
                                type: klucType,
                                source: $(source).length === 0  ? null : $(source).attr("kluc")
                            };
                            
                            x.type.push(s);
                            
                        });
                        
                        d.push(x);
                    });
                    
                    return d;
                    
                };
                

                let s = {
                    cmd_click: function (d) {
                        _selectDate = d;
                        alokaciaSource(_selectDate);
                    },
                    range: {
                        start:null,
                        end: null
                    },
                    value:_selectDate
                };


                let time_optional= {
                    value: initTime,
                    range: {
                        start: 360,
                        end:1200
                    }
                };
                
                
                
                
                let calendar = $(c.el).find("#calendar").setCalendar(s);
                let time = $(c.el).find("#time").setTime(time_optional);
                
                
                $.each($(c.el).find("div.plan.source"), function () {
                    $(this).click(function () {
                        $(this).parent().find("div.plan.source[select='1']").attr("select", 0);
                        $(this).attr("select", 1);
                    });
                });
                
                
                $.each($(c.el).find("div[zdroje_zaznam]"), function(){
                    let src = $(this).attr("zdroje_zaznam");
                    if(!src){
                        src = [];
                    } else {
                        src = $.parseJSON(src);
                    }
                    
                   
                    
                    
                    $.each(src, function(){
                        let type = $(c.el).find("div.plan.type[kluc='"+this.data.type+"']");
                        let source = $(type).parent().find("div.plan.source[kluc='"+this.data.source+"']");
                        $(source).attr("select",1);
                    });
                    
                    
                    
                    

                    
                });
                
                
                
                
                
                $(c.el).find("button#setButton").click(function () {

                    let d = _selectDate;
                    d = page.tool.dateNullHour(d);
                    d= d.getTime();
                    d = d / 1000;
                    d = d / 60;
                    d = {
                        date: d,
                        time: parseInt($("#time > div").controlValue())
                    };

                    let kluc_unit = $(c.el).find("div[kluc_init]").attr("kluc_init");
                    d.kluc_unit= kluc_unit;


                    d.source = getSourceSetting();



                    d.error = "Chybny zapis modelu, zlyhali parametre validacie";
                    
                    
                    zapis("/rest/iis/setEditUnit",{data:d,json:true}, function(odpoved){
                        dialog_termin.hide();
                        
                        $(el).find('div.row-personal-data').addClass("notify-change");
                        page.start.cmdEvent("time_change_canvas", {odpoved});
                    });
                    
                    
                    
                    
                    return true;

                    let vypocetDate = new Date(((d.date + d.time) * 60) * 1000);
                    vypocetDate = page.tool.getDateFormat(vypocetDate);

                    d.datum = vypocetDate;

                    

                    let listCinnost = $(c.el).find("div.plan-container");
                    listCinnost = $.map(listCinnost, function (item) {
                        let casovanie = $(item).attr("data");
                        casovanie = $.parseJSON(casovanie);
                        //debug(casovanie)
                        casovanie = {
                            time_posun: casovanie.item.time_posun,
                            duration: casovanie.def_cinnost.time
                        };
                        
                        
                        let kluc = $(item).attr("kluc");
                        let typeZdroj = $(item).find("div.type-container > div.plan.type");

                        typeZdroj = $.map(typeZdroj, function (itemZdroj) {

                            let source = $(itemZdroj).closest("div.type-container").find("div.plan.source[select='1']");
                            if (source.length > 0) {
                                source = $(source[0]).attr("kluc");
                            } else {
                                source = false;
                            }

                            

                            let h = {
                                kluc: $(itemZdroj).attr("kluc"),
                                source: source
                            };

                            return h;
                        });



                        let x = {
                            kluc: kluc,
                            casovanie: casovanie,
                            type: typeZdroj
                        };
                        

                        return x;
                    });


                    
                    d.cinnost = listCinnost;
                    page.contract.rebuildContainer(el, d);
                    dialog_termin.hide();

                });
                
                alokaciaSource(_selectDate);
                
                
            }
        });
    }
};

