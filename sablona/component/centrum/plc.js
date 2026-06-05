(function () {
    
    
let argZaznam = {
            plugins: [{
                id: 'customPoints',
                afterDatasetDraw(chart) {
                    const { ctx } = chart;

                    chart.data.datasets.forEach((dataset, datasetIndex) => {
                        const meta = chart.getDatasetMeta(datasetIndex);
                        if(!dataset.hidden){
                            meta.data.forEach((dataPoint, index) => {
                            // Dynamický prístup k súradniciam každého dátového bodu
                            const barWidth = dataPoint.width;
                            //console.log(barWidth);


                            //const x = dataPoint.x;
                            const x = dataPoint.x - barWidth / 2;
                            
                            const yValue = dataset.data[index].k;
                            const y = chart.scales.y.getPixelForValue(yValue);
                            

                            // Vytvorte gradient
                            const grd = ctx.createLinearGradient(x , y , x+(barWidth/2), y + 10);
                            grd.addColorStop(0, dataset.borderColor);
                            grd.addColorStop(0.5, dataset.backgroundColor);
                            grd.addColorStop(1, 'rgba(255,255,255,0)');

                            ctx.save();
                            ctx.beginPath();




                            ctx.arc(x+2, y, 4, 0, 2 * Math.PI);
                            ctx.fillStyle = dataset.backgroundColor;
                            ctx.fill();
                            ctx.strokeStyle = dataset.borderColor;
                            ctx.lineWidth = 1;
                            ctx.stroke();
                            
                            ctx.fillStyle = grd;
                            ctx.fillRect(x+4, y - 1.5, barWidth-5, 3); // Výška 3px, šírka ako stĺpec
                            
                            
                            ctx.restore();


                        });
                        }
                    });
                }
            }]
        };
    
    
    
    
    
    let zdroj = [];
    let sum = [];
    let colorG =[];
    
    let vypocetZdroj = function(source){
        
        let x = JSON.stringify(source);
        x = $.parseJSON(x);
        zdroj.push(x);
        
        
        
        let src1 = JSON.stringify(source);
        src1 = $.parseJSON(src1);

        let vypocet = $.map(src1,function(item,k){
            let g = {
                distance:0
            };
            
            let total_distance = parseInt(plc.plc[k].data.last_zaznam.data.total_distance);
            let cycle_count = parseInt(plc.plc[k].data.last_zaznam.data.cycle_count);
            let run_time = parseInt(plc.plc[k].data.last_zaznam.data.run_time);

            let koeficient = total_distance / cycle_count;

            item.data = $.map(item.data, function(item1){
                item1.y = item1.y * koeficient;
                g.distance += item1.y;
                return item1;
            });
            sum.push(g);
            return item;
        });
        
        zdroj.push(vypocet);
        
        let src2 = JSON.stringify(source);
        src2 = $.parseJSON(src2);
        
        let vypocet2 = $.map(src2,function(item,k){
            sum[k].run_time=0;
            let total_distance = parseInt(plc.plc[k].data.last_zaznam.data.total_distance);
            let cycle_count = parseInt(plc.plc[k].data.last_zaznam.data.cycle_count);
            let run_time = parseInt(plc.plc[k].data.last_zaznam.data.run_time);

            let koeficient = run_time / cycle_count;

            item.data = $.map(item.data, function(item1){
                item1.y = item1.y * koeficient;
                sum[k].run_time += item1.y;
                return item1;
            });
            return item;
        });
        
        zdroj.push(vypocet2);
        
        
        
        
        
        
        $.each($("div.parameter-value.sumDistance"), function(k,v){
            let f = sum[k].distance;
            f=f.toFixed(1);
            $(v).text(f);
        });
        
        $.each($("div.parameter-value.sumRun_time"), function(k,v){
            let f = sum[k].run_time;
            f=f.toFixed(1);
            $(v).text(f);
        });
        
        
    };



    let ViewChart = function (el, d) {


        let ColorGenerator = function(minDifference = 100) {
            this.usedColors = new Set();
            this.minDifference = minDifference;


            function calculateColorDifference(color1, color2) {
              if (color1.length !== color2.length) {
                throw new Error("Obe farby musia mať rovnaký počet zložiek.");
              }

              let difference = 0;
              for (let i = 0; i < color1.length; i++) {
                difference += Math.pow(color1[i] - color2[i], 2);
              }
              return Math.sqrt(difference);
            }


            function colorDifference(color1, color2) {
                return calculateColorDifference;
            }

            this.getRandomBoldColor = function() {
                let color;

                do {
                        const r = Math.floor(Math.random() * 200) + 50; 
                        const g = Math.floor(Math.random() * 200) + 50;  
                        const b = Math.floor(Math.random() * 200) +50;      
                        color = [r, g, b];

                } while (
                    Array.from(this.usedColors).some(
                        usedColor => colorDifference(usedColor.split(',').map(Number), color) < this.minDifference
                    )
                );
                this.usedColors.add(color.toString());

                return color;
            };
        }

        const colorGenerator = new ColorGenerator(150);
        
        let getColorRandom = function(){
            let p = colorGenerator.getRandomBoldColor();
            p= p.join(",");
            return {
                backgroundColor: `rgba(${p}, 0.6)`,
                borderColor: `rgba(${p}, 0.8)`,
                borderWidth: 2
            };
        };


        const ctx = el.getContext('2d');

        let data = {
            labels: [],
            datasets:[]
        };

        let tmpDataset ={
                    type: 'bar',
                    label: 'HST 1',
                    data: [12, 19, 3, 5, 2, 3],
                    backgroundColor: 'rgba(75, 192, 192, 0.6)',
                    borderColor: 'rgba(75, 192, 192, 0.8)',
                    borderWidth: 1
        };

        
        
        $.each(d.plc, function(k,v){
            let x = $.parseJSON(JSON.stringify(tmpDataset));
            let cc = getColorRandom();
            colorG.push(cc);
            
            x = $.extend(true, x, cc);
            
            x.label= v.label;
            x.data=[];
            
            $.each(v.data.data, function(){
                x.data.push({
                    x:this.den,
                    y: this.cycle_count
                });
            });
            
            data.datasets.push(x);

        });


        data_set = data.datasets;
        vypocetZdroj(data_set);
        
        let speed = JSON.stringify(data.datasets);
        speed = $.parseJSON(speed);
        zdroj.push(speed);
        

       
        $.each(d.plc, function(k,v){
            speed[k].data=[];
            //debug(speed[k]);
            
            
            
            $.each(v.data.data, function(){
                //debug(this);
                speed[k].data.push({
                    k:this.avg_speed,
                    x:this.den,
                    y: [this.min_speed, this.max_speed]
                });
            });
           
        });
        
        
        
        
        let config = {
            type: 'bar',
            data: data,
            options: {
                animation: true,
                responsive: true,
                maintainAspectRatio: false,
                scales: {
                    x: {
                        type: 'time',
                        time: {
                            unit: 'day',
                            tooltipFormat: 'yyyy-MM-dd',
                            displayFormats: {
                                day: 'MM-dd'
                            }
                        },
                        grid: {
                            color: 'rgba(255,255,255,0.1)'
                        }
                    },
                    y: {
                        beginAtZero: true,
                        grid: {
                            color: 'rgba(255,255,255,0.1)'
                        }
                    }
                },

                plugins: {
                legend: {
                      onClick: function(event, legendItem) {
                            let index = legendItem.datasetIndex;
                            let chart = this.chart;
                            chart.data.datasets[index].hidden = !chart.data.datasets[index].hidden;
                            let v = !chart.data.datasets[index].hidden;
                            $("div.panel_statistika").eq(index).attr("v",v);
                            chart.update();
                      }  
                    
                    }
                }
            }
        };




        config.plugins = [];
        page.gg=config;
        
        let myChart = new Chart(ctx, config);
        page.myChart = myChart;

        return myChart;

    };

    let data_set = null;

    let plc = $("div#plcData").attr("data");
    plc = $.parseJSON(plc);
    
    
    
    let c = new ViewChart($("#v")[0], plc);
    

    
    let dropdownMenuGraf = $("button#dropdownMenuGraf");
    $(dropdownMenuGraf).parent().find("> ul > li> div.dropdown-item").click(function(){
        let label= $(this).text();
        $(dropdownMenuGraf).find("span").html(label);
        let index = $(this).parent().index();
        
        page.gg.plugins=[];
        if(index==3){
            page.gg.plugins = argZaznam.plugins;
        }
        
        
        
        //debug(page.gg);
        
        c.data.datasets = zdroj[index];
        c.update();
    });
    
    
    

    $("div.border-radius.onclick_history").click(function(){
        let kluc = $(this).attr("kluc");
        let label = $(this).attr("label");
        page.centrum.switchRange();

    });

    $("div.border-radius.onclick_plc_state").click(function(){
        let kluc = $(this).attr("kluc");
        let label = $(this).attr("label");
        
        let canvasInfo = page.start.setCanvas("canvas_info",{
            title:"HST Info",
            template:"/canvas/monitor/user/plc",
            data: {
                plc: kluc,
                label:label
            }
        });
    });

    $.each($("div.alarm"),function(){
        let alarm = $(this).attr("data");
        let p = alarm.split('');
        p.splice(0, 1);
        p.splice(8, 8);
        
        let i = 0;
        $.each($(this).find("img.image-alarm"), function(){
            $(this).attr("select",p[i]);
            i++;
        });
    });


    if(plc.plc.length==1){
        
        let tmp = `<div class="row"></div>`;
        let tmpCol = `<div class="col-12 col-lg-6 p-1"></div>`;
        
        let newRow= $.parseHTML(tmp);
        
        let row = $("div[item='row_plc']");
        let col1 = $.parseHTML(tmpCol);
        $(col1).append(`<div style="min-height:35px" />`);
        $(col1).append($(row[0]).find(">div:nth-child(1)"));
        $(col1).append($(row[0]).find("> div.row > div > div"));
        

        let col2 = $.parseHTML(tmpCol);
        $(col2).append(`<div style="min-height:35px" />`);
        $(col2).append($(row[1]).find(">div:nth-child(1)"));
        $(col2).append($(row[1]).find("> div.row > div > div"));
        
        $(col2).find("div.onclick_plc_state").css({"min-height":"178px"});
        
        
        $(newRow).append(col1);
        $(newRow).append(col2);
        $(row[0]).before(newRow);
        $(row).remove();
        
        
    }

    let objStatistic = $("div.word_state");
    $.each(objStatistic, function(k,v){
        let word = plc.plc[k].data.last_zaznam.data.status_word;
        let text = "0010100011100000";
        let lastSevenChars = word.slice(-7);
        let p = lastSevenChars.split('');
        
        $.each($(v).find("img.image-word_state"), function(k1,v1){
            $(this).attr("select",p[k1]);
        });
        
        let cc = $(v).parent().parent().find(">div:nth-child(3)");
        $(cc).css({'min-height':'4px','background-color':colorG[k].backgroundColor});
        
        //console.log($(v).parent().parent().find(">div:nth-child(3)"));
        //debug(colorG[k]);
        
       
    });


    page.start.registerBind("socket", function(data){
        if(data.metoda=="plc" ){
            let plc=data.data;
            
            
            
            let x = $("div.onclick_plc_state[kluc='"+plc+"']")[0];
            if(x){
                console.log(data);
            }
            
        } 
    });



})();
