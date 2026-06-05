$.fn.setCalendar = function(setting){
    
    
    
    let template = '/component/calendar';
    let list = this;
    
    let calendarControl = function(el){
        let selectDateData = null;
        let selectDate = null;
        let _selectDate = 0;
        
        let option = {
            firstDay:1,
            aktDate: new Date(),
            aktRozsah: null,
            type: 'month'
        };
        
        
        if(setting && setting.value){
            setting.value = page.tool.dateNullHour(setting.value);
            option.aktDate = setting.value;
        }
         

        
        
        
        
        let tmpDay = `<div month="{aktMonth}"><div></div><div>{formatDay}</div></div>`;
        let tmpLabel = `<div><span>{rok}</span><span> / </span><span>{mesiac}</span></div>`;
        let tmpDayName = `<div>{name}</div>`;        
        let date = new Date();
        let nameDay = {
            1:["Mon","Tue", "Wed", "Thu", "Fri", "Sat", "Sun"],
            0:["Sun", "Mon","Tue", "Wed", "Thu", "Fri", "Sat"]        
        };           
        let dayViewContainer = $(el).find("div.kalendar-view");
        let dayNameContainer = $(el).find("div.kalendar-day-name");
        let monthLabelContainer = $(el).find("div.kalendar-label");
        let selectContainer = $(el).find("div[item='select_month']");
        let inputDate = $(selectContainer).find("input");
        

        let next = $(el).find('div[item="next"]');
        
        let nextView = function(val){
          if(option.type==='month'){  
            option.aktDate.setMonth(option.aktDate.getMonth()+val); 
          }

          if(option.type==='week'){
              option.aktDate = new Date(option.aktRozsah.first);
              option.aktDate.setDate(option.aktDate.getDate()+(val*7));
          }

          return listObject();   
        };        
        let select_date = function(){
            if(selectDate){
                $(selectDate).attr("select",0);
            }
            $(this).attr("select",1);
            selectDate = this;
            selectDateData = $(this).data("data");
            let _d = new Date(selectDateData.time);
            
            page.start.cmdEvent("calendar_select",{selectDate: selectDateData});
            
            $(inputDate).val(_d.toLocaleDateString());
         
            if(setting && typeof setting.cmd_click === 'function'){
                setting.cmd_click(_d);
            }
            
            
        };
        
        
        $(next[0]).click(function(){
            event.stopPropagation();
            let list = nextView(-1);
            viewMonth(list);
        });
        $(next[1]).click(function(){
            event.stopPropagation();
            let list = nextView(1);
            viewMonth(list);
        });

        let viewLabel = function(){
            let rok = option.aktDate.getFullYear();
            let mesiac = option.aktDate.getMonth()+1;
            
            let d = page.tool.createTemplateJSObject(tmpLabel, {rok:rok, mesiac: mesiac});
            $(monthLabelContainer).html(d);
            
        };

        let switch_date = function(str){
                
                let vd = null;
            
                let validateDate= function(dateString) {



                    const skRegex = /^(0?[1-9]|[12][0-9]|3[01])(\.|\.\s+|\s+\.|\s+\.\s+)(0?[1-9]|1[0-2])(\.|\.\s+|\s+\.|\s+\.\s+)(19|20)\d\d$/;
                    const usRegex = /^(0?[1-9]|1[0-2])(\/|\/\s+|\s+\/|\s+\/\s+)(0?[1-9]|[12][0-9]|3[01])(\/|\/\s+|\s+\/|\s+\/\s+)(19|20)\d\d$/;

                    if (skRegex.test(dateString)) {
                        const parts = dateString.split(".");
                        const day = parseInt(parts[0], 10);
                        const month = parseInt(parts[1], 10);
                        const year = parseInt(parts[2], 10);
                        
                        vd = new Date(year,month-1,day,0,0,0);
                        
                        return isValidDate(day, month, year);
                    }

                    
                    if (usRegex.test(dateString)) {
                        const parts = dateString.split("/");
                        const month = parseInt(parts[0], 10);
                        const day = parseInt(parts[1], 10);
                        const year = parseInt(parts[2], 10);
                        
                        vd = new Date(year,month-1,day,0,0,0);
                                                
                        return isValidDate(day, month, year);
                    }
                    return false;
                };

                let isValidDate = function (day, month, year) {
                    if (month < 1 || month > 12) {
                        return false;
                    }

                    const monthLengths = [31, (isLeapYear(year) ? 29 : 28), 31, 30, 31, 30, 31, 31, 30, 31, 30, 31];

                    if (day < 1 || day > monthLengths[month - 1]) {
                        return false;
                    }

                    return true;
                };

                let isLeapYear= function(year) {
                    return (year % 4 === 0 && year % 100 !== 0) || (year % 400 === 0);
                };

                let xx = validateDate(str);
            
                
                if(vd){
                    option.aktDate = vd;
                    selectDateData = {
                        time: vd.getTime() 
                    };
                    
                    let list = listObject();
                    viewMonth(list);
                }


        };




        let calendarArray = function(){
            let vRozhranie = {
                month: function(){
                    let v = new Date(option.aktDate.getFullYear(), option.aktDate.getMonth(), 1, 0, 0, 0);
                    let first = new Date(v.getTime());
                    let last = new Date(v.setMonth(v.getMonth()+1));
                    last.setDate(last.getDate()-1);

                    let p = 0;

                    if(option.firstDay === 1){
                        p = first.getDay()===0 ? 7 : first.getDay();
                        first.setDate(first.getDate() - (p-1));
                        let lD = last.getDay() === 0 ? 7 : last.getDay();
                        last.setDate(last.getDate() + (7-lD));

                    }
                    if(option.firstDay === 0){
                        p = first.getDay()===0 ? 1 : first.getDay() + 1;
                        first.setDate(first.getDate() - (p-1));
                        let lD = last.getDay() === 0 ? 1 : last.getDay() +1;
                        last.setDate(last.getDate() + (7-lD));
                    }                

                    option.aktRozsah = {
                        first: first,
                        last:last
                    };

                    return option.aktRozsah;
                },

                week: function(){
                    let v = new Date(option.aktDate.getTime());
                    let first = new Date(v.getTime());
                    let last = new Date(v.getTime());

                    let p = 0;

                    if(option.firstDay === 1){
                        p = first.getDay()===0 ? 7 : first.getDay();
                        first.setDate(first.getDate() - (p-1));
                        let lD = last.getDay() === 0 ? 7 : last.getDay();
                        last.setDate(last.getDate() + (7-lD));

                    }
                    if(option.firstDay === 0){
                        p = first.getDay()===0 ? 1 : first.getDay() + 1;
                        first.setDate(first.getDate() - (p-1));
                        let lD = last.getDay() === 0 ? 1 : last.getDay() +1;
                        last.setDate(last.getDate() + (7-lD));
                    }                

                    option.aktRozsah = {
                        first: first,
                        last:last
                    };

                    return option.aktRozsah;
                }

            };

            if(!vRozhranie[option.type]){
                alert("Chybne rozhranie:" + option.type);
                return {
                    first: new Date(),
                    last:new Date()
                };
            }

            let p = vRozhranie[option.type]();

            return p;

        };
        let listObject = function(){
            let rozsah = calendarArray();
            let listDay =[];
            let d  = new Date(rozsah.first);
            let formatDate = function(_d){
                return _d.toString(); 
            };

            let getRange = function(d){
                if(setting && setting.range  ){

                    if(setting.range.start && setting.range.end){
                        let range_start = new Date(setting.range.start);
                        let range_end = new Date(setting.range.end);
                        return  (d >= range_start && d <= range_end);
                    }
                    
                    if(setting.range.start){
                       let range_start = new Date(setting.range.start);
                       return  (d >= range_start);
                    }
                    
                    if(setting.range.end){
                       let range_end = new Date(setting.range.end);
                       return  (d <= range_end);
                    }
                    
                    
                    
                }
                
                
                return true;
            };


            while(d.getTime() <= rozsah.last.getTime()){
                let dw = 0;

                if(option.firstDay === 1){
                    dw = d.getDay()==0 ? 7 : d.getDay();
                }

                if(option.firstDay === 0){
                    dw = d.getDay()==0 ? 1 : d.getDay() + 1;
                }

                


                let x = {
                    date: d.toLocaleString(),
                    day: dw,
                    time: d.getTime(),
                    aktMonth: (option.aktDate.getMonth() === d.getMonth()).toString(),
                    formatDay: d.getDate(),
                    range: getRange(d)
                };
                listDay.push(x);
                d.setDate(d.getDate()+1);

            };

            return listDay;         
        };        
        let viewDayName = function(){
            let x = nameDay[option.firstDay];
            $(dayNameContainer).empty();    
            $.each(x, function(k,v){
                let n = v;
                let d = page.tool.createTemplateJSObject(tmpDayName, {name:n});
                $(dayNameContainer).append(d);
            });
        };
        
        let viewMonth = function(list){
            viewLabel();
            $(dayViewContainer).empty(); 
            $.each(list, function(){
                let d = page.tool.createTemplateJSObject(tmpDay, this);
                d = $.parseHTML(d);
                $(d).click(select_date);
                $(d).attr("range", this.range);
                
                $(d).data("data", this);
                if(selectDateData && selectDateData.time === this.time){
                    $(d).attr("select",1);
                    selectDate= d;
                    page.start.cmdEvent("calendar_select",{selectDate: this});
                }
                
                
                $(dayViewContainer).append(d);
            });

        };

        
        let load = function(){

            
            option.aktDate = new Date(option.aktDate.getFullYear(), option.aktDate.getMonth(), option.aktDate.getDate(), 0,0,0);
            //_selectDate = new Date(option.aktDate);
            

            
            selectDateData = {
                time: option.aktDate.getTime()
            };  
            $(inputDate).val(option.aktDate.toLocaleDateString());
            
            
            viewDayName();
            let list = listObject();
            viewMonth(list);
            
            $(selectContainer).find("form").submit(function(){
                switch_date($(inputDate).val());                
                return false;
            });
            
            
            
            
        };

        
        load();

        
        
        this.getValue = function(){
            let d = _selectDate.getTime();
            d = d/1000;
            d= d/60;
            return d;
        };
        
        
        
        return this;
    };
    
    
    let setObsah = function(obsah){
        let x = $.parseHTML(obsah);
        return x;
    };    
    
    let _promise = new Promise(function(resolve, reject){
        page.tool.createTemplateText(template,{x:1},function(obsah){
            resolve(obsah);
        });
    });   
    
    _promise.then(function(obsah){
        $.each(list, function(){
            let x = setObsah(obsah);
            let cc = new calendarControl(x);
            $(x).data("control", cc);
            $(this).empty().append(x);
        });
    });    
};
$.fn.setCalendar1 = function(setting){

    
    let template = '/component/calendar';
    let list = this;
    
    let calendarControl = function(el){
        let selectDateData = null;
        let selectDate = null;
        let _selectDate = 0;

        let _calendar_control = this;
        
        
        let option = {
            firstDay:1,
            aktDate: new Date(),
            aktRozsah: null,
            type: 'month',
            cmd_change_month:null,
            cmd_change_day: null
        };
        
        
        if(setting && setting.value){
            setting.value = page.tool.dateNullHour(setting.value);
            option.aktDate = setting.value;
        }
        
        if(setting && setting.aktDate){
            //setting.value = page.tool.dateNullHour(setting.value);
            //option.aktDate = setting.aktDate;
        }
        
        
        
        
        let tmpDay = `<div month="{aktMonth}"><div class="day-rezervacia" day="{dayMinute}" rezervacia="0"><div class="count">2</div></div><div>{formatDay}</div></div>`;
        let tmpLabel = `<div><span>{rok}</span><span> / </span><span>{mesiac}</span></div>`;
        let tmpDayName = `<div>{name}</div>`;        
        let date = new Date();
        let nameDay = {
            1:["Mon","Tue", "Wed", "Thu", "Fri", "Sat", "Sun"],
            0:["Sun", "Mon","Tue", "Wed", "Thu", "Fri", "Sat"]        
        };           
        let dayViewContainer = $(el).find("div.kalendar-view");
        let dayNameContainer = $(el).find("div.kalendar-day-name");
        let monthLabelContainer = $(el).find("div.kalendar-label");
        let selectContainer = $(el).find("div[item='select_month']");
        let inputDate = $(selectContainer).find("input");
        

        let next = $(el).find('div[item="next"]');
        
        let nextView = function(val){
          if(option.type==='month'){  
            option.aktDate.setMonth(option.aktDate.getMonth()+val); 
          }

          if(option.type==='week'){
              option.aktDate = new Date(option.aktRozsah.first);
              option.aktDate.setDate(option.aktDate.getDate()+(val*7));
          }

          

          return listObject();   
        };        
        let select_date = function(){
            if(selectDate){
                $(selectDate).attr("select",0);
            }
            $(this).attr("select",1);
            selectDate = this;
            selectDateData = $(this).data("data");
            let _d = new Date(selectDateData.time);
            
            page.start.cmdEvent("calendar_select",{selectDate: selectDateData});
            
            $(inputDate).val(_d.toLocaleDateString());
         
            if(setting && typeof setting.cmd_click === 'function'){
                setting.cmd_click(_d);
            }
            
            if(setting.cmd_change_day){
                option.selectDay=_d;
                setting.cmd_change_day(_calendar_control);
            }
            
            
            
        };
        
        
        $(next[0]).click(function(){
            event.stopPropagation();
            let list = nextView(-1);
            viewMonth(list);
        });
        $(next[1]).click(function(){
            event.stopPropagation();
            let list = nextView(1);
            viewMonth(list);
        });

        let viewLabel = function(){
            let rok = option.aktDate.getFullYear();
            let mesiac = option.aktDate.getMonth()+1;
            
            let d = page.tool.createTemplateJSObject(tmpLabel, {rok:rok, mesiac: mesiac});
            $(monthLabelContainer).html(d);
            
        };

        let switch_date = function(str){
                
                let vd = null;
            
                let validateDate= function(dateString) {



                    const skRegex = /^(0?[1-9]|[12][0-9]|3[01])(\.|\.\s+|\s+\.|\s+\.\s+)(0?[1-9]|1[0-2])(\.|\.\s+|\s+\.|\s+\.\s+)(19|20)\d\d$/;
                    const usRegex = /^(0?[1-9]|1[0-2])(\/|\/\s+|\s+\/|\s+\/\s+)(0?[1-9]|[12][0-9]|3[01])(\/|\/\s+|\s+\/|\s+\/\s+)(19|20)\d\d$/;

                    if (skRegex.test(dateString)) {
                        const parts = dateString.split(".");
                        const day = parseInt(parts[0], 10);
                        const month = parseInt(parts[1], 10);
                        const year = parseInt(parts[2], 10);
                        
                        vd = new Date(year,month-1,day,0,0,0);
                        
                        return isValidDate(day, month, year);
                    }

                    
                    if (usRegex.test(dateString)) {
                        const parts = dateString.split("/");
                        const month = parseInt(parts[0], 10);
                        const day = parseInt(parts[1], 10);
                        const year = parseInt(parts[2], 10);
                        
                        vd = new Date(year,month-1,day,0,0,0);
                                                
                        return isValidDate(day, month, year);
                    }
                    return false;
                };

                let isValidDate = function (day, month, year) {
                    if (month < 1 || month > 12) {
                        return false;
                    }

                    const monthLengths = [31, (isLeapYear(year) ? 29 : 28), 31, 30, 31, 30, 31, 31, 30, 31, 30, 31];

                    if (day < 1 || day > monthLengths[month - 1]) {
                        return false;
                    }

                    return true;
                };

                let isLeapYear= function(year) {
                    return (year % 4 === 0 && year % 100 !== 0) || (year % 400 === 0);
                };

                let xx = validateDate(str);
            
                
                if(vd){
                    option.aktDate = vd;
                    selectDateData = {
                        time: vd.getTime() 
                    };
                    
                    let list = listObject();
                    viewMonth(list);
                }


        };




        let calendarArray = function(){
            let vRozhranie = {
                month: function(){
                    let v = new Date(option.aktDate.getFullYear(), option.aktDate.getMonth(), 1, 0, 0, 0);
                    let first = new Date(v.getTime());
                    let last = new Date(v.setMonth(v.getMonth()+1));
                    last.setDate(last.getDate()-1);

                    let p = 0;

                    if(option.firstDay === 1){
                        p = first.getDay()===0 ? 7 : first.getDay();
                        first.setDate(first.getDate() - (p-1));
                        let lD = last.getDay() === 0 ? 7 : last.getDay();
                        last.setDate(last.getDate() + (7-lD));

                    }
                    if(option.firstDay === 0){
                        p = first.getDay()===0 ? 1 : first.getDay() + 1;
                        first.setDate(first.getDate() - (p-1));
                        let lD = last.getDay() === 0 ? 1 : last.getDay() +1;
                        last.setDate(last.getDate() + (7-lD));
                    }                

                    option.aktRozsah = {
                        first: first,
                        last:last
                    };

                    return option.aktRozsah;
                },

                week: function(){
                    let v = new Date(option.aktDate.getTime());
                    let first = new Date(v.getTime());
                    let last = new Date(v.getTime());

                    let p = 0;

                    if(option.firstDay === 1){
                        p = first.getDay()===0 ? 7 : first.getDay();
                        first.setDate(first.getDate() - (p-1));
                        let lD = last.getDay() === 0 ? 7 : last.getDay();
                        last.setDate(last.getDate() + (7-lD));

                    }
                    if(option.firstDay === 0){
                        p = first.getDay()===0 ? 1 : first.getDay() + 1;
                        first.setDate(first.getDate() - (p-1));
                        let lD = last.getDay() === 0 ? 1 : last.getDay() +1;
                        last.setDate(last.getDate() + (7-lD));
                    }                

                    option.aktRozsah = {
                        first: first,
                        last:last
                    };

                    return option.aktRozsah;
                }

            };

            if(!vRozhranie[option.type]){
                alert("Chybne rozhranie:" + option.type);
                return {
                    first: new Date(),
                    last:new Date()
                };
            }

            let p = vRozhranie[option.type]();

            return p;

        };
        let listObject = function(){
            let rozsah = calendarArray();
            let listDay =[];
            let d  = new Date(rozsah.first);
            let formatDate = function(_d){
                return _d.toString(); 
            };

            let getRange = function(d){
                if(setting && setting.range  ){

                    if(setting.range.start && setting.range.end){
                        let range_start = new Date(setting.range.start);
                        let range_end = new Date(setting.range.end);
                        return  (d >= range_start && d <= range_end);
                    }
                    
                    if(setting.range.start){
                       let range_start = new Date(setting.range.start);
                       return  (d >= range_start);
                    }
                    
                    if(setting.range.end){
                       let range_end = new Date(setting.range.end);
                       return  (d <= range_end);
                    }
                    
                    
                    
                }
                
                
                return true;
            };


            while(d.getTime() <= rozsah.last.getTime()){
                let dw = 0;

                if(option.firstDay === 1){
                    dw = d.getDay()==0 ? 7 : d.getDay();
                }

                if(option.firstDay === 0){
                    dw = d.getDay()==0 ? 1 : d.getDay() + 1;
                }

                


                let x = {
                    date: d.toLocaleString(),
                    day: dw,
                    time: d.getTime(),
                    aktMonth: (option.aktDate.getMonth() === d.getMonth()).toString(),
                    formatDay: d.getDate(),
                    range: getRange(d)
                };
                listDay.push(x);
                d.setDate(d.getDate()+1);

            };

            return listDay;         
        };        
        let viewDayName = function(){
            let x = nameDay[option.firstDay];
            $(dayNameContainer).empty();    
            $.each(x, function(k,v){
                let n = v;
                let d = page.tool.createTemplateJSObject(tmpDayName, {name:n});
                $(dayNameContainer).append(d);
            });
        };
        
        let viewMonth = function(list){
            viewLabel();
            $(dayViewContainer).empty(); 
            $.each(list, function(){
                
                this.dayMinute = (this.time/1000)/60;
                //debug(this);
                
                
                let d = page.tool.createTemplateJSObject(tmpDay, this);
                d = $.parseHTML(d);
                $(d).click(select_date);
                $(d).attr("range", this.range);
                
                $(d).data("data", this);
                if(selectDateData && selectDateData.time === this.time){
                    $(d).attr("select",1);
                    selectDate= d;
                    page.start.cmdEvent("calendar_select",{selectDate: this});
                    
                    if(setting.cmd_change_day){
                        let h = new Date(this.time);
                        
                        option.selectDay=h;
                        setting.cmd_change_day(_calendar_control);
                    }
                    
                }
                
                
                $(dayViewContainer).append(d);
            });
            
            if(setting.cmd_change_month){
                setting.cmd_change_month(_calendar_control);
            }

        };

        
        let load = function(){

            
            option.aktDate = new Date(option.aktDate.getFullYear(), option.aktDate.getMonth(), option.aktDate.getDate(), 0,0,0);
            //_selectDate = new Date(option.aktDate);
            

            
            selectDateData = {
                time: option.aktDate.getTime()
            };  
            $(inputDate).val(option.aktDate.toLocaleDateString());
            
            
            viewDayName();
            let list = listObject();
            viewMonth(list);
            
            $(selectContainer).find("form").submit(function(){
                switch_date($(inputDate).val());                
                return false;
            });
            
            
            
            
        };

        this.getSelectDate = function(){
            
            return (option.selectDay.getTime()/1000)/60;
        };


        this.setRezervacia = function(d){
            let rezervacia = $(dayViewContainer).find("div.day-rezervacia[day='"+d.date+"']");
            $(rezervacia).attr("rezervacia",1);
            $(rezervacia).find("div.count").html(d.pocet);
        };


        this.clearRezervation = function(){
            let rezervacia = $(dayViewContainer).find("div.day-rezervacia[rezervacia='1']");
            $.each(rezervacia, function(){
                $(this).attr("rezervacia",0);
            });
        };


        this.getRozsahMonth = function(){
            let _r = {
                first: (option.aktRozsah.first.getTime()/1000)/60,
                last: (option.aktRozsah.last.getTime()/1000)/60
            };
            return _r;
        };
        
        this.getValue = function(){
            let d = _selectDate.getTime();
            d = d/1000;
            d= d/60;
            return d;
        };
        
        load();
        
        return this;
    };
    
    
    let setObsah = function(obsah){
        let x = $.parseHTML(obsah);
        return x;
    };    
    
    let _promise = new Promise(function(resolve, reject){
        page.tool.createTemplateText(template,{x:1},function(obsah){
            resolve(obsah);
        });
    });   
    
    _promise.then(function(obsah){
        $.each(list, function(){
            let x = setObsah(obsah);
            let cc = new calendarControl(x);
            $(x).data("control", cc);
            $(this).empty().append(x);
        });
    });    
};
$.fn.setTime = function(setting){
    
    let template = '/component/time';
    let tmpHour = '<li value="{i}"><div class="dropdown-item select-time"  role="button">{h}</div></li>';
    let tmpMinute = '<li value="{i}"><div class="dropdown-item select-time"  role="button">{m}</div></li>';
    let _getValue = 0;
    
    
    let list = this;




    let timeControl = function(el){
        
        let prepocet= {
            minuteToDate: function(val){
                let minute = parseInt(val) % 60;
                let hour = (parseInt(val) - minute)/60;
                
                return {
                    minute: minute,
                    hour: hour
                };
                
            }
        };
        
        let padDigits = function (number, digits) {
            return Array(Math.max(digits - String(number).length + 1, 0)).join(0) + number;
        }; 
        
        let setHour = function(d){
            $(el).find("*[item='hour']").html(padDigits(d,2));
        };
        
        let setMinute = function(d){
            $(el).find("*[item='minute']").html(padDigits(d,2));
        };
        
        
        let setEvent = {
            hour: function(){
                let i = parseInt($(this).attr("value"));
                setHour(i);
                $(el).attr("hour",i);
                
                let minute = parseInt($(el).attr("minute"));
                let t = (i*60)+minute;
                $(el).find("input[item='time_range']").val(t);
                $(el).attr("timestamp",t);
                page.start.cmdEvent("control_range", {minute: t, el:el});
                _getValue= t;
                
            },
            minute: function(){
                let i = parseInt($(this).attr("value"));
                setMinute(i);
                $(el).attr("minute",i);
                let hour = parseInt($(el).attr("hour"));
                let t = (hour*60)+i;
                $(el).find("input[item='time_range']").val(t);
                $(el).attr("timestamp",t);
                page.start.cmdEvent("control_range", {minute: t, el:el});
                _getValue= t;
                
            },
            range: function(){
                let v = $(this).val();
                let m = v % 60;
                let h = (v-m)/60;
                setHour(h);
                $(el).attr("hour",h);
                setMinute(m);
                $(el).attr("minute",m);
                $(el).attr("timestamp",v);
                page.start.cmdEvent("control_range", {minute: v, el:el});
                _getValue= v;
            }
        };
        
        
        let load = function(){
          
            let setRange= function(){
                if(setting && setting.range){
                    
                    let s = (parseInt(setting.range.start) / ((24*60))*100);
                    let e = (parseInt(setting.range.end) / ((24*60))*100);        
                    
                    
                    $(el).find("div.input-range-time > div[item='start']").css({"min-width": s+"%"});
                    $(el).find("div.input-range-time > div[item='end']").css({"left": e+"%"});  
                }
            };
          
          
            if(setting && setting.value){
               _getValue =  setting.value;
               let _set = prepocet.minuteToDate(setting.value);
               setHour(_set.hour);
               setMinute(_set.minute);
               $(el).find("input[item='time_range']").val(_getValue);
            }

            
            
            //let popis = $(el).find("div.dropdown > button");
            //$(popis).html("00");
            
            let listHour = $(el).find("ul.hours");
            $(listHour).empty();
            
            let listMinute = $(el).find("ul.minutes");
            $(listMinute).empty();            
            
            
            for(let i = 0; i < 24; i++){
                let h = page.tool.createTemplateJSObject(tmpHour,{h:padDigits(i,2),i:i});
                h = $.parseHTML(h);
                $(h).click(setEvent.hour);
                $(listHour).append(h);
            }
            
            for(let i = 0; i < 60; i+=5){
                let h = page.tool.createTemplateJSObject(tmpMinute,{m:padDigits(i,2),i:i});
                h = $.parseHTML(h);
                $(h).click(setEvent.minute);
                $(listMinute).append(h);
            } 
            
            $(el).find("input[item='time_range']").on("input",setEvent.range);
            $(el).find("input[item='time_range']").trigger("input",this);
            
            
            setRange();
            
            
            
        };
        
        load();
        
        this.getValue = function(){
            //debug(_getValue);
            return _getValue;
        };
        
        return this;
    };
    
    let setObsah = function(obsah){
        let x = $.parseHTML(obsah);
        return x;
    };
    

    let _promise = new Promise(function(resolve, reject){
        page.tool.createTemplateText(template,{x:1},function(obsah){
            resolve(obsah);
        });
    });

    _promise.then(function(obsah){
        $.each(list, function(){
            let x = setObsah(obsah);
            let tc = new timeControl(x);
            $(x).data("control", tc);
            $(this).empty().append(x);
        });
    });




};
$.fn.extend({
    controlValue: function(){
        let obj = $(this[0]).data("control");
        return obj.getValue();
    }
});
$.fn.setTextEditor = function(setting){
    let tmp = null;
    let template = '/component/input/text_editor_script_template';
    let list = this;
    
    let txtEditorComponent = function(el){
            let obj = $.parseHTML(tmp);
            $(el).replaceWith(obj);
            
            
            
    };
    
    
    let loadComponent = function(){
        $.each(list, function(){
            let c = new txtEditorComponent(this);
;
        });
    };
    
    
    let nacitatTemplate = function(){
        if(!tmp){
            let _promise = new Promise(function(resolve, reject){
                page.tool.createTemplateText(template,{x:1},function(obsah){
                    resolve(obsah);
                });
            });

            _promise.then(function(obsah){
                tmp=obsah;
                loadComponent();
                
            });
            
            return true;
        }
        
        loadComponent();

    };
    
    
    nacitatTemplate();
    
    
};

page.timeline = function(option){
    
    let date_label = $.parseHTML("<div style='text-align:right; padding:3px 10px 3px 5px;color:gray; font-weight: bold'></div>");
    let tmpHour = null;
    
    let _option = {
        container: "div#content",
        range: {
            start:8*60,
            end: 18*60
        },
        data: {},
        cmd: null
    };
    
    _option = $.extend(true, _option, option);

    if(!tmpHour){
        let src = $(_option.container).html();
        $(_option.container).empty();
        tmpHour= src;
    }
    
    
    let date = new Date((_option.range.start *60)*1000);
    let hours = date.getHours()-1;
    let minutes = date.getMinutes();
    minutes = minutes.toString().padStart(2, '0');
    hours = hours.toString().padStart(2, '0');
    let time = `${hours}:${minutes}`;


    let html = page.tool.createTemplateJSObject(tmpHour, {cas:1, cas_format:`00:00 - ${time}`});
    html = $.parseHTML(html);
    $(_option.container).append(html);
    $(_option.container).find(">div").before(date_label);
    
    
    
    for (let i = _option.range.start; i <= _option.range.end; i += 60) {
        
        let date = new Date((i*60)*1000);
        let hours = date.getHours()-1;
        let minutes = date.getMinutes();
        minutes = minutes.toString().padStart(2, '0');
        hours = hours.toString().padStart(2, '0');
        let time = `${hours}:${minutes}`;
        
        
        let cas_format=time;
        let html = page.tool.createTemplateJSObject(tmpHour, {cas:i, cas_format:cas_format});
        html = $.parseHTML(html);
        $(_option.container).append(html);
    }
    
    
    date = new Date((_option.range.end  *60)*1000);
    hours = date.getHours();
    minutes = date.getMinutes();
    minutes = minutes.toString().padStart(2, '0');
    hours = hours.toString().padStart(2, '0');
    time = `${hours}:${minutes}`;


    html = page.tool.createTemplateJSObject(tmpHour, {cas:2, cas_format:`${time} - 00:00`});
    html = $.parseHTML(html);
    $(_option.container).append(html);
    
    
    
    
    
    let _addTimeLine = function(el){
        let timeMinute = $(el).attr("time");
        let cas = timeMinute*60*1000;
        cas = new Date(cas);
        cas = (cas.getHours()-1)*60;
        
        if(cas<_option.range.start){
            cas = 1;
        }
        
        if(cas>_option.range.end){
            cas = 2;
        }
        
        
        
        let p = $(_option.container).find("div[cas='"+cas+"'] div.time-line-data");
        $(p).append(el);

    };
    
    let _clearTimeline = function(){
        let p = $(_option.container).find("div[cas] div.time-line-data");
        $(p).empty();
    };
    
    
    let _show= function(){

    };
    
    this.setDate = function(d){

        $(date_label).html(d.toLocaleDateString());
    };
    this.clearTimeline = _clearTimeline;
    this.addTimeLine = _addTimeLine;
    this.show = _show;
    return this;
};
   
   
page.plan = {
    setStartDate: null,
    
    
    access: function(option){
        
        
        
                let overit = false;
        
                zapis("/service/plan/access",{data:1,json:true,async:false }, function(odpoved){
                     overit = odpoved.data;
                });

                
                if(!overit){
                    option.template ="/canvas/plan/opravnenie";
                }
       
    },
    
    
    scroll : function(){
            let data = $(this).data("data");
            let el = data.el;
            let kam = $(el).parent().offset().top - 100;
            let body = $("html, body");
            $(body).stop().animate({scrollTop: kam}, 600, 'swing', function () {});
    },
    
    
    setPause: function(kluc){
        let option = {
            title: "Zdrzanie",
            template: "/canvas/plan/setZdrzanie",
            data:{kluc:kluc},
            cmd:function(d){
                let btn = $(d.el).find("button[item='cmd']");
                $(btn).click(setTask);
            }
        }; 
        page.plan.access(option);
        
        console.log("set access");
        
        
        
        let canvas = page.start.setCanvas("set_zdrzanie",option);

        
        let setTask = function(){
  
            let frm = $(this).closest("div[item='frm']");
            let data = getData(frm); 
            let v = new form.validate(frm);
            zapis("/service/plan/setPause", {data:data, json:true}, function(odpoved){
                
                if(!odpoved.result){
                    v.test(odpoved.data);
                    return false;
                }
                
                
                //debug(odpoved);
                
                canvas.hide();
                page.start.cmdEvent("vypocet_set_new_task",{el:null});
            }); 

           

            
        };
        
    },
    
    
    setStart: function(kluc){
        let option = {
            title: "Start projektu",
            template: "/canvas/plan/setStart",
            data:{kluc:kluc},
            cmd:function(d){
                
                
                let btn = $(d.el).find("button[item='cmd']");
                $(btn).click(setTask);
                
            }
        }; 
        page.plan.access(option);
        
        
        let canvas = page.start.setCanvas("set_start",option);

        
        let setTask = function(){
            /*
            let frm = $(this).closest("div[item='frm']");
            let data = getData(frm); 
            let v = new form.validate(frm);
            zapis("/service/plan/edit_task", {data:data, json:true}, function(odpoved){
                
                if(!odpoved.result){
                    v.test(odpoved.data);
                    return false;
                }
                
                //debug(odpoved);
                
                canvas.hide();
                page.start.cmdEvent("vypocet_set_new_task",{el:null});
            }); 
            */
           
           page.start.cmdEvent("vypocet_set_new_task",{el:null});
           canvas.hide();
            
        };
        
    },
    
    
    editTask: function(el){
        let kluc = $(el).attr("kluc");
        
        let option = {
            title: "edit uloha",
            template: "/canvas/plan/edit_task",
            data:{kluc:kluc},
            cmd:function(d){
                let btn = $(d.el).find("button[item='cmd']");
                $(btn).click(setTask);
            }
        }; 
        
        page.plan.access(option);
        
        let canvas = page.start.setCanvas("edit_task",option);
        
        let setTask = function(){
            let frm = $(this).closest("div[item='frm']");
            let data = getData(frm); 
            let v = new form.validate(frm);
            zapis("/service/plan/edit_task", {data:data, json:true}, function(odpoved){
                
                if(!odpoved.result){
                    v.test(odpoved.data);
                    return false;
                }
                
                //debug(odpoved);
                
                canvas.hide();
                page.start.cmdEvent("vypocet_set_new_task",{el:null});
            }); 
            
        };
        
        
        
    },
    
    
    addTask: function(kluc){
        let option = {
            title: "Pridat novu ulohu",
            template: "/canvas/plan/create_task",
            data:{kluc:kluc},
            cmd:function(d){
                let btn = $(d.el).find("button[item='cmd']");
                $(btn).click(setTask);
            }
        }; 
        page.plan.access(option);
        
        
        let canvas = page.start.setCanvas("add_task",option);
        
        let setTask = function(){
            let frm = $(this).closest("div[item='frm']");
            let data = getData(frm); 
            let v = new form.validate(frm);
            zapis("/service/plan/add_task", {data:data, json:true}, function(odpoved){
                
                if(!odpoved.result){
                    v.test(odpoved.data);
                    return false;
                }
                
                canvas.hide();
                page.start.cmdEvent("vypocet_set_new_task",{el:null});
            }); 
            
        };
        
        
        
        
        
    },
    
    
    addProjekt: function(){
        
        let option = {
            title: "Pridat novy projekt",
            template: "/canvas/plan/create_project",
            data:{},
            cmd:function(d){
                let btn = $(d.el).find("button[item='cmd']");
                $(btn).click(zapisNewProject);
            }
        };        
        page.plan.access(option);
        
        
        let canvas = page.start.setCanvas("add_project",option);
        
        let zapisNewProject = function(){
            let frm = $(this).closest("div[item='frm']");
            let data = getData(frm);
            
            
            let v = new form.validate(frm);
            zapis("/service/plan/add_project", {data:data, json:true}, function(odpoved){

                
                if(!odpoved.result){
                    v.test(odpoved.data);
                    return false;
                }
                
                smerovat("/plan/" + odpoved.data);
                

            }); 
            
        };



    }
};    
    
page.contentData = function(option){
    
    let _option = {
        template: '/obsah/list/person/fragment',
        container: "div#content",
        data: {},
        cmd: null
    };
    
    _option = $.extend(true, _option, option);
    
    _option.container = $(_option.container);
    

    
    let _setObsah = function(template, data, cmd){
         page.tool.createTemplateText(template, data,function(obsah){
            obsah = $.parseHTML(obsah);
            $(_option.container).replaceWith(obsah);
            _option.container=obsah;
            if(cmd){
                cmd(obsah);
            }
        });
    };
    
    
    this.setObsah = _setObsah;
    
    
    return this;
};


page.contentLoader = function(option){
    let tw = `<div class="p-2"><div class="spinner-border text-primary" role="status">
                <span class="visually-hidden">Loading...</span>
              </div></div>`;
    
    let _option = {
        template: '/obsah/list/person/fragment',
        container: "div#content",
        data: {},
        cmd: null
    };
    _option = $.extend(true, _option, option);
    
    //debug(_option);

    let _promise = new Promise(function(resolve, reject){
        page.tool.createTemplateText("/component/loader",{x:1},function(obsah){
            resolve(obsah);
        });
    });   
    
    _promise.then(function(obsah){
        tw = obsah;
        
        let w = $.parseHTML(tw);
        $(_option.container).empty();
        $(_option.container).append(w);    


        page.tool.createTemplateText(_option.template, _option.data,function(obsah){
            $(_option.container).empty();
            
            
            obsah = $.parseHTML(obsah, document,true);
            $(_option.container).append(obsah);
            if (_option.cmd && typeof _option.cmd == "function") {
                _option.cmd(obsah);
            }
        });
    }); 



    return this;
    
};


page.dataLoader = function(option){
    
    let tw = `<div class="p-2"><div class="spinner-border text-primary" role="status">
                <span class="visually-hidden">Loading...</span>
              </div></div>`;    
    
    let loader = `<div class="p-1" style=" padding-top:25px"><button class="btn btn-primary w-100">Load next data ....</button></div>`;
    
    
    let _option = {
        start:0,
        limit: 100,
        template: '/obsah/list/person/fragment',
        container: "div#listZoznam",
        autoLoad: false,
        emptyContainer: false
        
    };
    

    _option = $.extend(true,_option,option);
    
    let loadData = function(){

        let w = $.parseHTML(tw);
        $(loader).hide();
        $(_option.container).append(w);
        
        
        let vstup = {
            start:_option.start,
            limit: _option.limit
        };
        
        
        let parameter = $(_option.container).attr("parameter");
        if(parameter){
            parameter = $.parseJSON(parameter);
            vstup = $.extend(true,vstup,parameter);
        }
        
        
        
        
        page.tool.createTemplateText(_option.template, vstup, function(obsah){
            obsah = $.parseHTML(obsah);
            let p = $(obsah).find(">div");
            $(_option.container).append(p);
            _option.start += _option.limit;
            $(w).remove();
            
            if(p.length === _option.limit){
                $(loader).show();
            }
        });
        
    };
    
    let defData = function(){
        loader = $.parseHTML(loader);

        $(loader).find("button").click(loadData);
        $(_option.container).after(loader);

        let opt = {
          root: null, // Use the browser viewport as the root
          rootMargin: '0px', // No margin
          threshold: 0.1 // Trigger when 50% of the element is visible
        };

        if(_option.autoLoad){
            let observer = new IntersectionObserver(function(objEntites){
                $.each(objEntites,function(){
                    if(this.isIntersecting){
                        //observer.unobserve(this.target);
                        //format($(this.target));
                        loadData();
                    } 
                });
            }, opt);
            observer.observe(loader[0]);
        }


        if(_option.emptyContainer){
            $(_option.container).empty();
        } 
        
    };
    
    let _promise1 = new Promise(function(resolve, reject){
        page.tool.createTemplateText("/component/loader",{x:1},function(obsah){
            tw = obsah;
            resolve(obsah);
        });
    });      
    
    let _promise2 = new Promise(function(resolve, reject){
        page.tool.createTemplateText("/component/dataButtonNext",{x:1},function(obsah){
            loader = obsah;
            resolve(obsah);
        });
    }); 
    
    Promise.all([_promise1, _promise2]).then(function(obsah){
        defData();
        loadData();
    });
    
    
};


page.menu = {
    values: {
        main: [],
        additional: []
    },
    addMenu: function(label, link, icon, color, section = "main"){
        let _default = {
            link: "/",
            icon: "/assets/svg/iss.svg",
            color: "--b-color-red",
            label: "home"
        };

        if(label) _default.label = label;
        if(link) _default.link = link;
        if(icon) _default.icon = icon;
        if(color) _default.color = color;

        if (section === "additional") {
            page.menu.values.additional.push(_default);
        } else {
            page.menu.values.main.push(_default);
        }
        
    },

    clear: function(){
        page.menu.values.main = [];
        page.menu.values.additional = [];
    }
};

page.start = {
    bindEvent : [],
    _canvas: [],
    
    
    createItem: {
        person: function(){
            let option = {
                header: {
                    label: "white",
                    color: "var(--color-beesport)"
                },
                label: "Add person",

                button_color: "var(--color-beesport)",
                zapisat: true,
                content: "/control/obsah/add/person" 

            };


            let modal = new page.tool.createModal(option);

            modal.setCmd(function(data,frm){
                let v = new form.validate(frm);
                zapis("/rest/person/addPerson",{data:data, json:true}, function(odpoved){
                    if(!odpoved.result){
                        v.test(odpoved.data);
                        return false;
                    }
                    //debug(odpoved);
                    modal.closeDialog();
                    smerovat("/person/"+odpoved.data);
                });

            });
            modal.show();
        },

        team: function(){
                        
            
            let option = {
                header: {
                    label: "white",
                    color: "var(--color-beesport)"
                },
                label: "Add team",

                button_color: "var(--color-beesport)",
                zapisat: true,
                content: "/control/obsah/add/team" 

            };


            let modal = new page.tool.createModal(option);

            modal.setCmd(function(data,frm){
                let v = new form.validate(frm);
                zapis("/rest/person/addTeam",{data:data, json:true}, function(odpoved){
                    if(!odpoved.result){
                        v.test(odpoved.data);
                        return false;
                    }

                    modal.closeDialog();
                    smerovat("/team/"+odpoved.data);
                });

            });
            modal.show();

        },

        test: function(){
            let option = {
                header: {
                    label: "white",
                    color: "var(--color-beesport)"
                },
                label: "Add test",

                button_color: "var(--color-beesport)",
                zapisat: true,
                content: "/control/obsah/add/test" 

            };


            let modal = new page.tool.createModal(option);

            modal.setCmd(function(data,frm){
                let v = new form.validate(frm);
                zapis("/rest/test/addTest",{data:data, json:true}, function(odpoved){
                    if(!odpoved.result){
                        v.test(odpoved.data);
                        return false;
                    }
                    debug(odpoved);
                    modal.closeDialog();
                    smerovat("/tests/"+odpoved.data);
                });

            });
            modal.show();
        },
        plan: function(){
            let option = {
                header: {
                    label: "white",
                    color: "var(--color-beesport)"
                },
                label: "Add training plan",

                button_color: "var(--color-beesport)",
                zapisat: true,
                content: "/control/obsah/add/training_plan" 

            };


            let modal = new page.tool.createModal(option);

            modal.setCmd(function(data,frm){
                let v = new form.validate(frm);
                zapis("/rest/trening/addTrainingPlan",{data:data, json:true}, function(odpoved){
                    if(!odpoved.result){
                        v.test(odpoved.data);
                        return false;
                    }
                    debug(odpoved);

                    modal.closeDialog();
                    smerovat("/training_plan/"+odpoved.data);
                });

            });
            modal.show();
        },

        client: function(){ 
            let option = { 
                header: { 
                    label: "white", 
                    color: "var(--color-beesport)" 
                }, 
                label: "Add client", 
 
                button_color: "var(--color-beesport)", 
                zapisat: true, 
                content: "/control/obsah/add/client"  
 
            }; 
 
 
            let modal = new page.tool.createModal(option); 
 
            modal.setCmd(function (data, frm) { 
                //debug(data); 
                let v = new form.validate(frm); 
                zapis("/rest/iis/addClient", { data: data, json: true }, function (odpoved) { 
                    
                    if(!odpoved.result){ 
                        v.test(odpoved.data); 
                        return false; 
                    } 
                    //debug(odpoved);
                    
                    let container = $('div[item="create_contract"]').find('.panel-contract-faktura > div.paying-client');
                    let tmp = `<div class="alert alert-pp" role="alert" kluc="{data}" onclick="page.contract_create.editDoklad(this)">
                                    <b>{vstup.client.name}</b>
                                </div>`;
                    
                    $(container).empty();  
                    let h = page.tool.createTemplateJSObject(tmp, odpoved);
                    $(container).append(h);

                    modal.closeDialog();
                    page.start.closeCanvas("contact_create_doklad");

                    /*
                    let loaderData = new page.contentLoader({
                        template: '/canvas/list/fragment_paying_client',
                        container: "div.list-container.list-client"
                    });
                    */
                    //smerovat("/iis/client/"+odpoved.data); 
                }); 
 
            }); 
            modal.show(); 
        },
        
        payment: function (contractData) {
            let option = { 
                header: { 
                    label: "white", 
                    color: "var(--color-beesport)" 
                }, 
                label: "Add payment", 
                button_color: "var(--color-beesport)", 
                zapisat: true, 
                content: "/control/obsah/add/payment",
                data: contractData
            };
 
            let modal = new page.tool.createModal(option); 
 
            modal.setCmd(function (data, frm) { 
                //debug(data);
                
                let v = new form.validate(frm); 
                zapis("/rest/iis/setPayment", { data: data, json: true }, function (odpoved) { 
                    
                    if(!odpoved.result){ 
                        v.test(odpoved.data); 
                        return false; 
                    } 
                    //debug(odpoved); 
                    modal.closeDialog();
                    let loaderContent = new page.contentLoader({
                        template: '/obsah/iis/list/payment/fragment',
                        container: "div#paymentList",
                        data: {
                            contract_key: odpoved.vstup.contract_key
                        }
                    });
                });
 
            }); 
            modal.show(); 
        }, 

        program: function () {
            let addProgramCanvas = page.start.setCanvas("program_add_new",{
                title: "Add Program",
                template: "/canvas/IIS/program_add_new_ad"
            });

            page.start.registerBind('program_add_new_ad', function (el) {
                let f = $(el).closest("div[item='form']");
                let data = getData(f);

                //debug(data);

                let v = new form.validate(f);
                zapis("/rest/iis/setNewProgram", {data:data,json:true}, function(odpoved){
                    if(!odpoved.result){
                        v.test(odpoved.data);
                        return false;
                    } 
                    
                    smerovatGET('/iis/program/'+odpoved.data);
                });
            });
        },

        team_add_person: function(key_team){
            let data = {
                person: [],
                team: key_team
            };

            let option = {
                title: "Add person to team",
                template: "/component/list/list_person_team",
                data:{},
                cmd:null
            };

            page.start.registerBind("canvas_add_person_to_team", function (d) {
                
                //console.log($(d.el).closest("div[item='zoznam']").find("div.select-item[select=1]"));
                
                let dataValues = [];
                let findSelectedInParent = $(d.el).closest("div[item='zoznam']").find("div.select-item[select=1]");
                $.each(findSelectedInParent, function(){
                    let dataAttr = $(this).attr("data");
                    dataAttr = $.parseJSON( dataAttr);
                    dataValues.push(dataAttr);

                });
                
                data.person = dataValues;

                zapis("/rest/person/setPersonTeam", {data: data, json:true}, function(odpoved){
                    //debug(odpoved);
                });
                

                
                let container = $("div[item='listPerson']");
                
                let tmp = `<div class="row-container">
                                <div class="row">
                                    <div class="col-12">
                                        <div>{surname}  {middlename} {name}</div>
                                        <div class="text-end" style="font-size: 80%; color: rgba(255,255,255,0.8);">
                                            <div><date format="d.m.Y">{birthday}</date> - {category}</div>
                                        </div>
                                    </div>
                                </div>
                            </div>`;
                
                $(container).empty();        
                $.each(dataValues, function () {

                    let h = page.tool.createTemplateJSObject(tmp, this);
                    $(container).append(h);
                });
                
                
                
                team_add_person.hide();
            }, true);

            
            let team_add_person = page.start.setCanvas("add_person_to_team", option);
            
        },
        
        test_plan : function(){
            let data = {
                time: {
                    date:null,
                    time: null
                },
                person: [],
                test: []
            };
            
            
            let option = {
                title: "Add test Plan",
                template: "/canvas/create_item/test_plan/test_plan",
                data:{},
                cmd:null
            };
            
            let select_test = function(){
                page.start.registerBind("canvas_select_test", function(d){
                    
                    //console.log($(d.el).closest("div[item='zoznam']").find("div.select-item[select=1]"));

                    let findSelectedInParent = $(d.el).closest("div[item='zoznam']").find("div.select-item[select=1]");
                    let dataValues = [];

                    $.each(findSelectedInParent, function(){
                        let dataAttr = $(this).attr("data");
                        dataAttr = $.parseJSON( dataAttr);
                        dataValues.push(dataAttr);

                    });
                    

                    let container = $("div[item='listTest']");
                    let tmp = `<div 
                                    class="selected-item"
                                    kluc="{kluc}" 
                                    group="{group.preklad}"
                                >
                                    <div>
                                        <div>{preklad}</div>
                                    </div>
                                    <div>{group.preklad}</div>
                                    <div>{code_preklad}</div>
                                </div>`;
                    
                    $.each(dataValues, function () {
                        let h = page.tool.createTemplateJSObject(tmp, this);
                        $(container).append(h);
                    });
                    
                    data.test = $.map( dataValues,function(item){
                        return item.kluc;
                    });                    
                    
                    
                    select_test_canvas.hide();
                }, true);



                let select_test_canvas = page.start.setCanvas("select_test",{
                    title: "Select tests",
                    template: "/canvas/create_item/test_plan/select_test",
                    data: data.test,
                    cmd:function (d) {
                        
                     
                        page.tool.template_path = "";
                        page.tool.createTemplateText("/component/list/list_test", {x:1}, function(obsah) {
                            let loader = $(d.el).find("div[item='load']");
                            $(loader).replaceWith(obsah);
                        });
                        
                        
                    }
                });
                
                
            };
            
            let select_person = function(){
                page.start.registerBind("canvas_select_person", function(d){
                    
                    //console.log($(d.el).closest("div[item='zoznam']").find("div.select-item[select=1]"));
                    
                    let dataValues = [];
                    let findSelectedInParent = $(d.el).closest("div[item='zoznam']").find("div.select-item[select=1]");
                    $.each(findSelectedInParent, function(){
                        let dataAttr = $(this).attr("data");
                        dataAttr = $.parseJSON( dataAttr);
                        dataValues.push(dataAttr);

                    });
                    


                    let container = $("div[item='listPerson']");
                    let tmp = `<div 
                                    class="selected-item"
                                    kluc="{kluc}"
                                >
                                    <div>
                                        <div>{surname} {name}</div>
                                    </div>
                                    <div>{category}</div>
                                    <div>{birthday}</div>
                                </div>`;
                    
                    $(container).empty();        
                    $.each(dataValues, function () {

                        let h = page.tool.createTemplateJSObject(tmp, this);
                        $(container).append(h);
                    });
                    
                    data.person = $.map( dataValues,function(item){
                        return item.kluc;
                    });
                    
                    select_person_canvas.hide();
                }, true);

                let select_person_canvas = page.start.setCanvas("select_person",{
                    title: "Select person",
                    template: "/canvas/create_item/test_plan/select_person",
                    data: data.person,
                    cmd:function (d) {
                        
                        /*
                        let g = page.dataLoader({
                                template: '/obsah/list/person/fragment',
                                limit: 50,
                                container: "div#listZoznam",
                                autoLoad: false
                        });
                        */
                        
                        
                        
                        
                        page.tool.template_path = "";
                        page.tool.createTemplateText("/component/list/list_person", {select:data.person}, function(obsah) {
                            let loader = $(d.el).find("div[item='load']");
                            $(loader).replaceWith(obsah);
                        });
                        let selectItem = $(d.el).closest("div[item='zoznam']").find("div.select-item");
                        console.log($(d.el).children("offcanvas-body"));

                        $.each(data.person, function(id, person){
                            key = person.kluc;

                            console.log(key);
                            
                        });
                        
                    }
                });
            };

            let select_team = function(){
                page.start.registerBind("canvas_select_team", function(d){
                    
                    
                    let dataAttr = $(d.el).attr("data");
                    dataAttr = $.parseJSON( dataAttr);
                    data.team = [dataAttr];

                    debug(data.team);
                    
                    select_team_canvas.hide();
                }, true);

                let select_team_canvas = page.start.setCanvas("select_team",{
                    title: "Select team",
                    template: "/canvas/create_item/test_plan/select_team",
                    data: data.team,
                    cmd:function (d) {
                        
                        /*
                        let g = page.dataLoader({
                                template: '/obsah/list/person/fragment',
                                limit: 50,
                                container: "div#listZoznam",
                                autoLoad: false
                        });
                        */
                        
                        
                        
                        
                        page.tool.template_path = "";
                        page.tool.createTemplateText("/component/list/list_team", {select:data.team}, function(obsah) {
                            let loader = $(d.el).find("div[item='load']");
                            $(loader).replaceWith(obsah);
                        });
                        let selectItem = $(d.el).closest("div[item='zoznam']").find("div.select-item");
                        console.log($(d.el).children("offcanvas-body"));

                        $.each(data.team, function(id, team){
                            key = team.kluc;

                            console.log(key);
                            
                        });
                        
                    }
                });
            };
            
            let save_test_plan = function(){

                let time = data.time.date + (data.time.time * 60 *1000);
                time = new Date(time);
                data.timeStamp = time.getTime(); 
                data.timeStamp  = data.timeStamp /1000;



                if(data.person.length==0){
                    alert("Select person !!!");
                    return false;
                }

                if(data.test.length==0){
                    alert("Select tests plan !!!");
                    return false;
                }
                
                data.name = $("#name_test").val();
                
                
                if(data.name.trim()==''){
                    alert("Plan name !");
                    return false;
                }
                
                
                zapis("/rest/test/addPlan", {data:data, json: true}, function(odpoved){
                    debug(odpoved);
                    test_plan.hide();
                });
                
                


            };
            
            
            page.start.registerBind("test_plan_select_test",select_test, true);
            page.start.registerBind("test_plan_select_person", select_person, true);
            page.start.registerBind("test_plan_select_team", select_team, true);
            page.start.registerBind("test_plan_save",save_test_plan, true);
            
            page.start.registerBind("calendar_select",function(d){
                data.time.date= d.selectDate.time;
            }, true);
          
            page.start.registerBind("control_range",function(d){
                data.time.time= d.minute;
            }, true);                
            
            
            
            let test_plan = page.start.setCanvas("add_test_plan", option);
            
            
            
        },
        
        training_plan : function(){
            let data = {
                time: {
                    date:null,
                    time: null
                },
                person: [],
                training:[],
            };
            
            
            let option = {
                title: "Add training Plan",
                template: "/canvas/create_item/training_plan/training_plan",
                data:{},
                cmd:function(){
                    console.log(arguments);
                }
            };
            
            let select_training = function(){
                
                page.start.registerBind("canvas_select_training", function(d){

                    let dataAttr = $(d.el).attr("data");
                    dataAttr = $.parseJSON( dataAttr);
                    
                    
                    
                    
                    
                    data.training = [dataAttr.kluc];

                    let container = $("div[item='listTraining']");
                    let tmp = `<div 
                                    class="selected-item"
                                    kluc="{kluc}"
                                >
                                    <div>
                                        <div>{name}</div>
                                    </div>
                                    <div>BeeSport</div>
                                    <div>{description}</div>
                                </div>`;
                    
                    $(container).empty();  
                    let h = page.tool.createTemplateJSObject(tmp, dataAttr);
                    $(container).append(h);
                    
                    select_training_canvas.hide();
                }, true);



                let select_training_canvas = page.start.setCanvas("select_training",{
                    title: "Select training plan",
                    template: "/canvas/create_item/training_plan/select_training",
                    data: data.training,
                    cmd:function (d) {
                        
                    
                        page.tool.template_path = "";
                        page.tool.createTemplateText("/component/list/list_training", {x:1}, function(obsah) {
                            let loader = $(d.el).find("div[item='load']");
                            $(loader).replaceWith(obsah);
                        });
                        
                        
                    }
                });
                
                
            };
            
            let select_person = function(){
                page.start.registerBind("canvas_select_person_training", function(d){
                    
                    //console.log($(d.el).closest("div[item='zoznam']").find("div.select-item[select=1]"));
                    
                    let dataValues = [];
                    let findSelectedInParent = $(d.el).closest("div[item='zoznam']").find("div.select-item[select=1]");
                    $.each(findSelectedInParent, function(){
                        let dataAttr = $(this).attr("data");
                        dataAttr = $.parseJSON( dataAttr);
                        dataValues.push(dataAttr);

                    });
                    
                    

                    let container = $("div[item='listPerson']");
                    let tmp = `<div 
                                    class="selected-item"
                                    kluc="{kluc}"
                                >
                                    <div>
                                        <div>{surname} {name}</div>
                                    </div>
                                    <div>{category}</div>
                                    <div>{birthday}</div>
                                </div>`;
                    
                    $(container).empty();        
                    $.each(dataValues, function () {

                        let h = page.tool.createTemplateJSObject(tmp, this);
                        $(container).append(h);
                    });
                    
                    data.person = $.map( dataValues,function(item){
                        return item.kluc;
                    });
                    
                    select_person_canvas.hide();
                }, true);


                let select_person_canvas = page.start.setCanvas("select_person_training",{
                    title: "Select person",
                    template: "/canvas/create_item/training_plan/select_person",
                    data: data.person,
                    cmd:function (d) {
                        
                        /*
                        let g = page.dataLoader({
                                template: '/obsah/list/person/fragment',
                                limit: 50,
                                container: "div#listZoznam",
                                autoLoad: false
                        });
                        */
                        
                        
                        
                        
                        page.tool.template_path = "";
                        page.tool.createTemplateText("/component/list/list_person", {select:data.person}, function(obsah) {
                            let loader = $(d.el).find("div[item='load']");
                            $(loader).replaceWith(obsah);
                        });
                        let selectItem = $(d.el).closest("div[item='zoznam']").find("div.select-item");
                        console.log($(d.el).children("offcanvas-body"));

                        $.each(data.person, function(id, person){
                            key = person.kluc;

                            console.log(key);
                            
                        });
                        
                    }
                });
            };
            
            let save_training_plan = function(){

                let time = data.time.date + (data.time.time * 60 *1000);
                time = new Date(time);
                data.timeStamp = time.getTime(); 
                data.timeStamp  = data.timeStamp /1000;

                if(data.person.length==0){
                    alert("Select person !!!");
                    return false;
                }

                if(data.training.length==0){
                    alert("Select training plan !!!");
                    return false;
                }


                data.name = $("#name_plan").val();
                
                
                if(data.name.trim()==''){
                    alert("Plan name !");
                    return false;
                }
                

                zapis("/rest/trening/addPlan", {data:data, json: true}, function(odpoved){
                    debug(odpoved);
                    training_plan.hide();
                });




            };
            
            
            page.start.registerBind("training_plan_select_training",select_training, true);
            page.start.registerBind("training_plan_select_person",select_person, true);
            page.start.registerBind("training_plan_save",save_training_plan, true);
            
            page.start.registerBind("calendar_select",function(d){
                data.time.date= d.selectDate.time;
            }, true);
          
            page.start.registerBind("control_range",function(d){
                data.time.time= d.minute;
            }, true);            
            
            
            
            
            let training_plan = page.start.setCanvas("add_training_plan", option);
            
            
            
        }
    },

    listItem: {
        person: function (link) {
            let data = {
                link: link,
                person: ""
            };
            let list_person_canvas = page.start.setCanvas("list_person", {
                title: "List person",
                template: "/canvas/list/list_person",
                cmd: function () {
                    page.start.registerBind('go_to_person', function (key) {
                        data.person = key;
                        console.log(data.person.key);
                        smerovatGET('/person/'+data.person.key+'/list/'+data.link);
                    })
                }
            });
        }
    },

    planingItem: {
        test: function (){
            let c = page.start.setCanvas("planing_calendar",{
                title: "Pridat planovanie",
                template: "/canvas/calendar/planing_calendar"
            });

            c.show();
            
            /*
            let container = $(el).closest("div[item=form]").find("div[item='listCinnostZoznam']");
            
            page.start.registerBind("cinnost_form_add", function(data){
                
                page.tool.createTemplateText("/canvas/IIS/program/row_cinnost",{kluc:data.kluc}, function(obsah){
                    $(container).append(obsah);
                    page.start.component.load();
                    c.hide();
                }); 
                
    
                
            }, true);
            */
        },
        training: function (){
            alert("training");
        }
    },
        
    
    setDate: function(cmd,date){
        if(!date){
            date = new Date();
        }
        
        let d = date;
        
        let kluc = "select_date";
        
        let c = page.start.setCanvas(kluc,{
            title: "Vyber datum",
            template: "/canvas/calendar",
            data: {
                date: "2024-05-29",
                cmd: kluc
            }
        });
        
        if(cmd && typeof cmd==='function'){
            //alert(d);
        }
                
        
        
        return c;
        

        
    },
    
    
    setSelect: function(el){
        let v = $(el).attr("select");
        v = v == 1  ? 0 : 1;
        $(el).attr("select",v);
    },
    
    
    getTemplateData: function(z){
        let src = $("template[item='"+z+"']").html();
        src = $.parseJSON(src);
        return src;
    },
    
    
    registerBind: function(event_name,cmd, clear_old=false){
        
        if(clear_old===true){
            let x = $.grep(page.start.bindEvent, function(item){
                return item.event_name === event_name;
            });
            
            $.each(x, function(){
                let i = page.start.bindEvent.indexOf(this);
                page.start.bindEvent.splice(i, 1);
            });
        }

        
        let x = {
            event_name: event_name,
            cmd:cmd
        };
        page.start.bindEvent.push(x);
    },
    
    cmdEvent: function(event_name,data){
        //console.log(event_name, data);
    
        
        let x = $.grep(page.start.bindEvent, function(item){
            return item.event_name === event_name;
        });
        
        $.each(x, function(){
            let p = this;
            if(typeof p.cmd === 'function'){
                p.cmd(data);
            }
        });
        
    },

    setCanvas: function(name, option={}){
        let _option = {
            title: "label canvas",
            template: null,
            data:{},
            cmd:null
        };
        $.extend(true,_option,option);    




        let canvas = page.start._canvas[name];
        if(!canvas){
            canvas = new page.start.createCanvas(_option);
            canvas.setCaption(_option.title);
            page.start._canvas[name] = canvas;
        }
        
        if(_option.template){
            if(!_option.data || Object.keys(_option.data).length === 0){
                _option.data={x:1};
            }
            
            canvas.setTemplateObsah(_option.template,_option.data);
        }
        
                
        canvas.show(_option.cmd);
        page.start.aktiveCanvas = canvas;

        
        
        return canvas;
        
    },
    
    
    scroll: function(elID){
        let x = $("#"+elID)[0];
        if(x){
             x.scrollIntoView({ behavior: 'smooth' });
        }
    },
    

    closeCanvas:function(name){
        if(page.start._canvas[name]){
            page.start._canvas[name].hide();
        }
        
    },



    openModal: function (section, labelDialog, headerColorVar, btnColorVar, cmd=null) {
        //console.log({ 'section': section, 'label': labelDialog });

        

        let headerColor = getComputedStyle(document.documentElement).getPropertyValue(headerColorVar);
        let btnColor = getComputedStyle(document.documentElement).getPropertyValue(btnColorVar);
        
        
        
        
        let option = {
            //ikona: "/control/ikona/posta",
            header: {
                label: "white",
                color: headerColor
            },
            label: labelDialog,
            button_save: "Zapisat",
            button_close: "Zatvorit",
            button_color: btnColor,
            zapisat: true,
            content: "/control/obsah/add/" + section

        };
        
                
        let modal = new page.tool.createModal(option);
        modal.setCmd(function(data,frm){
            let _zapis = function(){
                page.start.cmdEvent("add_form_"+ section, {data:data, frm:frm, modal:modal});
            };
            
            if(!cmd){
                _zapis();
                return true;
            }
                        
            cmd(data,frm);
            
        });
        modal.show();
    },

    openPopup: function (section, labelDialog, headerColorVar, btnColorVar, cmd=null) {

        var headerColor = getComputedStyle(document.documentElement).getPropertyValue(headerColorVar);
        var btnColor = getComputedStyle(document.documentElement).getPropertyValue(btnColorVar);
        
        var option = {
            //ikona: "/control/ikona/alert",
            header: {
                label: "white",
                color: headerColor
            },
            label: labelDialog,
            button_save: "Yes",
            button_close: "No",
            button_color: btnColor,
            zapisat: true,
            content: "/control/obsah/messages/" + section,

        };

        let modal = new page.tool.createModal(option);
        modal.setCmd(function(data,frm){
            let _redirect = function(){
                page.start.cmdEvent("redirect_to_"+ section, {data:data, frm:frm, modal:modal});
            };
            
            if(!cmd){
                _redirect();
                return true;
            }
                        
            cmd(data,frm);
            
        });
        modal.show();
    },
    /*
    checkBox: function (el) {
        let parentEl = $(el).parent();
        let val = $(parentEl).attr("select");

        if(!val) val = 0;
        val = val == 1 ? 0 : 1;

        $(parentEl).attr("select", val);
        $(parentEl).toggleClass('selected');

        let selectedParent = $(el).parent('[select="1"]');
        let targetLi = $(parentEl).find('.order-li');
        let notSelectedLi = $(parentEl).not('.selected');

        if (selectedParent.length > 0) {
            $(targetLi).attr("select", "1");
            $(targetLi).addClass('selected');
        }

        else {
            $(targetLi).attr("select", "0");
            $(targetLi).removeClass('selected');
        }

        
        if (notSelectedLi.length > 0) {
            let current = $(el).parent();
            while (current.length > 0) {
                let parentLi = current.closest('ul').parent('li');
                if (parentLi.length === 0) {
                    break;
                }
                parentLi.attr("select", "0");
                current = parentLi;
            }
        }

        else {
            let current = $(el).parent();
            while (current.length > 0) {
                let parentLi = current.closest('ul').parent('li');
                if (parentLi.length === 0) {
                    break;
                }
                parentLi.attr("select", "1");
                current = parentLi;
            }
        }
    },

    openAccord: function (event, data) {

        event.stopPropagation();

        let li = $(event.target).next().next();

        li.toggleClass('tree-show');
    },    
    */
    filteredItems: {},

    sortFilter: function (el) {
        
        let searchValue = $(el).val().toLowerCase();
        //let searchValue = $(el).find('#searchInput').val().toLowerCase();
        //console.log($('.list-item'));
        let regex = new RegExp(`\\b${searchValue}`, 'i');

        let visibleItems = $.grep($('.list-item'), function(item) {
            let itemText = $(item).text().toLowerCase();
            return regex.test(itemText);
            //return itemText.includes(searchValue);
        });

        $('.list-item').attr('view', '0');

        $(visibleItems).attr('view', '1');
        
    },

    filterSelect: function (select) {
        let selectedCategory = $(select).find('option:selected').text().toLowerCase();

        let filteredItems = $.grep($('.select-item'), function(item) {
            let groupAttr = $(item).attr('group').toLowerCase();

            return selectedCategory === 'all' || groupAttr.includes(selectedCategory);
        });

        $('.select-item').attr('view', '0');

        $(filteredItems).attr('view', '1');

        this.filteredItems = filteredItems;

        this.sortFilter();
    },

    showMenu: function(el){

        let c = page.start.setCanvas("root_menu",{
            type: "end",
            title: "Menu",
            template: "/canvas/root_menu/root",
            data: {
                menu_main: page.menu.values.main,
                menu_additional: page.menu.values.additional
            },
            cmd:function(el){
                $(el.el).click(function(){
                    let t = $(event.target).closest("a");
                    if(t.length>0){
                        let link = $(t).attr("href");
                        if(/^javascript/g.test(link)){
                            c.hide();
                        }
                    }
                });
            } 
        });

    },
    
    showMenuTop: function(template = null, data=null){

        if(template){
            page.tool.template_path="";
            page.tool.createTemplate(template,data, function(obsah){
                $("#menuCanvasTop").replaceWith(obsah);
                page.start.canvasTop = new bootstrap.Offcanvas('#menuCanvasTop'); 
                page.start.canvasTop.show();
            });

            return true;
        }
        
        page.start.canvasTop = new bootstrap.Offcanvas('#menuCanvasTop'); 
        page.start.canvasTop.show();
    },

    showMenuLeft: function(template = null, data=null){

        if(template){
            page.tool.template_path="";
            page.tool.createTemplate(template,data, function(obsah){
                $("#menuCanvasStart").replaceWith(obsah);
                page.start.canvasStart = new bootstrap.Offcanvas('#menuCanvasStart'); 
                page.start.canvasStart.show();
            });

            return true;
        }
        
        page.start.canvasStart = new bootstrap.Offcanvas('#menuCanvasStart'); 
        page.start.canvasStart.show();
        return true;
        
    },

    createCanvas: function(canvas_option){
        
        let option = {
            type:'start',
            caption:'<div item="caption">Load ....</div>',
            obsah:'<div item="obsah">Load ....</div>'
        };
        
        let promiseTemplate = null;
        let _obsahCanvas = null;
        
        $.extend(true,option,canvas_option);
        let _canvas = null;
        
        let _setCaption = function(label){
            _promise.then(function(data){
                $(data.el).find(".offcanvas-title").html(label);
            });
        };

        let _setIndex = function(number){
            _promise.then(function(data){
                $(data.el).find("div.offcanvas").css({"z-index":number});
            });
        };


        let _hide = function(){
            _canvas.boostrap.hide();
        };
        
        let _remove = function(){
            $(_canvas.el).remove();
        };
        
        
        let _show = function(cmd=null){
            
             Promise.all([_promise]).then(function(data){
                $("body").append(_canvas.el);
                _canvas.boostrap = new bootstrap.Offcanvas(_canvas.el[0]); 

                if(promiseTemplate){
                    promiseTemplate.then(function(tmp){
                        if(typeof cmd === 'function'){
                            cmd(data[0]);
                        }

                        data[0].boostrap.show();
                    });
                } else {
                    if(typeof cmd === 'function'){
                        
                        cmd(data[0]);
                    }

                    data[0].boostrap.show();
                }
             });
        };
        
        
        let _setTemplateObsah = function(template, data=null){
                _promise.then(function(obj){
                    promiseTemplate = new Promise(function(resolve, reject){
                        page.tool.createTemplateText(template,data,function(obsah){
                            

                            obsah = $.parseHTML(obsah, document, true);
                            obsah = $(obsah).html();

                            
                            let containerObsah = $(obj.el).find("div.offcanvas-body");
                            $(containerObsah).empty();
                            $(containerObsah).append(obsah);
                            
                            resolve({obj:obj,obsah:obsah});
                        });
                    });
                });
        };
        
        
        let _promise = new Promise(function(resolve, reject) {
            
            
            page.tool.createTemplateText("/canvas/template/"+option.type,null,function(obsah){
 
                let tmpHTML = page.tool.createTemplateJSObject(obsah, option);
                
                let canvas = $.parseHTML(tmpHTML,document,true);
                //$("body").append(canvas);
                let _x = new bootstrap.Offcanvas(canvas[0]); 
                _canvas =  {
                  el: canvas,
                  boostrap: _x
                };

                $(canvas).on('hidden.bs.offcanvas', function () {
                  $(_canvas.el).detach();
                });

                resolve(_canvas);
            });
        });

        
        this.setCaption = _setCaption;
        this.setTemplateObsah = _setTemplateObsah;
        this.show = _show;
        this.hide = _hide;
        this.remove = _remove;
        this.setIndex = _setIndex;
        
        return this;
    },
    
    
    component:  {
        
        type: {
           check: function(el){
               
               $(el).removeAttr("component");
               $(el).addClass("component-check");    
               $(el).click(function(){
                   let v = $(this).attr("select");
                   v = v==0 || !v ? 1 :  0;
                   $(this).attr("select",v);
               });
               
               
               /*
               let tmp = `<div select="0" value="{value}" class="component-check">
                            <div class="check-select"></div>
                            <div>{label}</div>
                        </div>`;
               
               
               let data = $.parseJSON($(el).html());
               
               
               
               let c = page.tool.createTemplateJSObject(tmp,data);
               c= $.parseHTML(c);
               if(data.select){
                   $(c).attr("select",data.select);
               }
               
               $(c).click(function(){
                   let v = $(this).attr("select");
                   v = v==0 ? 1 :  0;
                   $(this).attr("select",v);
               });
               $(el).replaceWith(c);
               return true;
                * 
                * 
                */
           } 
        },
        
        load:function(){
            let t = page.start;
            let component = $("div[component]");
            $.each(component, function(){
                let type = $(this).attr("component");
                if(t.component.type[type]){
                    t.component.type[type](this);
                }
            });
        }
    }
    
    
    
    
    
    
};
page.setCalendar = function(date=null, type=null){
    let firstDay = 1;
    if(!type){
        type="month";
    }
    
    let datum = function(date){
       if(!date){
           date = new Date();
       }
       date = new Date(date);
       date = new Date(date.getFullYear(), date.getMonth(), date.getDate(),0,0,0);
       return date;
    };
    
    let aktDate = datum(date);
    let aktRozsah = null;
    
    
    let calendarArray = function(){
        let vRozhranie = {
            month: function(){
                let v = new Date(aktDate.getFullYear(), aktDate.getMonth(), 1, 0, 0, 0);
                let first = new Date(v.getTime());
                let last = new Date(v.setMonth(v.getMonth()+1));
                last.setDate(last.getDate()-1);
                
                let p = 0;
                
                if(firstDay === 1){
                    p = first.getDay()===0 ? 7 : first.getDay();
                    first.setDate(first.getDate() - (p-1));
                    let lD = last.getDay() === 0 ? 7 : last.getDay();
                    last.setDate(last.getDate() + (7-lD));

                }
                if(firstDay === 0){
                    p = first.getDay()===0 ? 1 : first.getDay() + 1;
                    first.setDate(first.getDate() - (p-1));
                    let lD = last.getDay() === 0 ? 1 : last.getDay() +1;
                    last.setDate(last.getDate() + (7-lD));
                }                
                
                aktRozsah = {
                    first: first,
                    last:last
                };

                return aktRozsah;
            },
            
            week: function(){
                let v = new Date(aktDate.getTime());
                let first = new Date(v.getTime());
                let last = new Date(v.getTime());
                
                let p = 0;
                
                if(firstDay === 1){
                    p = first.getDay()===0 ? 7 : first.getDay();
                    first.setDate(first.getDate() - (p-1));
                    let lD = last.getDay() === 0 ? 7 : last.getDay();
                    last.setDate(last.getDate() + (7-lD));

                }
                if(firstDay === 0){
                    p = first.getDay()===0 ? 1 : first.getDay() + 1;
                    first.setDate(first.getDate() - (p-1));
                    let lD = last.getDay() === 0 ? 1 : last.getDay() +1;
                    last.setDate(last.getDate() + (7-lD));
                }                
                
                aktRozsah = {
                    first: first,
                    last:last
                };

                return aktRozsah;
            }

        };
        
        if(!vRozhranie[type]){
            alert("Chybne rozhranie:" + type);
            return {
                first: new Date(),
                last:new Date()
            };
        }
        
        let p = vRozhranie[type]();
        
        return p;

    };
    let listObject = function(){
        let rozsah = calendarArray();
        let listDay =[];
        let d  = new Date(rozsah.first);
        let formatDate = function(_d){
            return _d.toString(); 
        };
        
        while(d.getTime() <= rozsah.last.getTime()){
            let dw = 0;

            if(firstDay === 1){
                dw = d.getDay()==0 ? 7 : d.getDay();
            }

            if(firstDay === 0){
                dw = d.getDay()==0 ? 1 : d.getDay() + 1;
            }
            
            
            let x = {
                date: d.toLocaleString(),
                day: dw,
                time: d.getTime(),
                aktMonth: (aktDate.getMonth() === d.getMonth()).toString(),
                formatDay: d.getDate()
                
            };
            listDay.push(x);
            d.setDate(d.getDate()+1);

        };

        return listDay;         
    };
    
    this.next = function(val){
      if(type==='month'){  
        aktDate.setMonth(aktDate.getMonth()+val); 
      }
      
      if(type==='week'){
          aktDate = new Date(aktRozsah.first);
          aktDate.setDate(aktDate.getDate()+(val*7));
      }
      
      return listObject();   
    };
   
    this.setDate = function(date){
        aktDate = datum(date);
        return listObject();
    };
   
    this.getData = function(view='month'){
        type = view;
        return listObject();
    };
    
    this.setFirstDay = function(day){
        firstDay= day;
    };
    
    this.getDayName = function(){
        let name = {
            1:["Mon","Tue", "Wed", "Thu", "Fri", "Sat", "Sun"],
            0:["Sun", "Mon","Tue", "Wed", "Thu", "Fri", "Sat"]        
        };
        
        let x =  name[firstDay];
        x = $.map(x, function(item){
            return {name:item};
        });
        
        return x;
        
    },    
    
    
    this.getMonthView = function(){
        let x = {
            rok: aktDate.getFullYear(),
            mesiac: aktDate.getMonth()+1
        };
        return x;
    };
    
    return this;
    
};


(function(){
   
    let isNestingSupported = function() {
        try {
            const style = document.createElement('style');
            style.textContent = ` 
                @nest div & {
                    color: red;
                }
            `;
            document.head.appendChild(style);
            document.head.removeChild(style);
            return true; 
        } catch {
            return false; 
        }
    };

    if (isNestingSupported()) {
        console.log("CSS Nesting je podporované.");
    } else {
        console.log("CSS Nesting nie je podporované.");
    }    
    
    
    
    
    page.start.registerBind("clicked_on_user",function(d){
        let dialog_login = page.start.setCanvas("login",{
            title:"Login",
            template:'/login_off'
        });
    });
    
    let socket = new page.tool.websocket({
        kluc:'beeSport-monitor'
    });
    
    socket.onMessage(function(data){
        
        page.start.cmdEvent("socket", data );
        
        if(data.metoda=='login'){
           let message = data.data;
           let tmp =` <div class="alert alert-danger" style="font-size:80%" role="alert">
              Aktivny: ${message}
            </div>`;

            let m = $.parseHTML(tmp);
            

            
            $("#notifyHeader").append(m);
            setTimeout(function() {
                $(m).remove();
            }, 2500);
            
            
        }
    });
    
    
    page.start.registerBind("root_load", function(){
        page.start.registerBind("clicked_menu", page.start.showMenu);
        let userKluc = $("body").attr("user");
        let currentUrl = $(location).attr('href');
        
        let deviceInfo = {
            platform: navigator.platform,
            browserName: navigator.appName,
            browserVersion: navigator.appVersion,
            language: navigator.language,
            screenWidth: screen.width,
            screenHeight: screen.height,
            colorDepth: screen.colorDepth,
            online: navigator.onLine,
            userAgent: navigator.userAgent,
            connection: {}
        };

        if ('connection' in navigator) {
            let connection = navigator.connection || navigator.mozConnection || navigator.webkitConnection;
            deviceInfo.connection = {
                effectiveType: connection.effectiveType,
                downlink: connection.downlink,
                rtt: connection.rtt
            };
        } else {
            deviceInfo.connection = {
                message: "Network Information API není podporováno."
            };
        }


        /*
        zapis("/rest/iis/setHistory",{data:{user:userKluc, url:currentUrl, pc:deviceInfo}, json:true}, function(odpoved){
            //debug(odpoved);
        });
        */
    });
    
})();


