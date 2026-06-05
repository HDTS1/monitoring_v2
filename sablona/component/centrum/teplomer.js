
if(!page.convertDatum){
    page.convertDatum = function(time,zona='America/Edmonton'){
        return time;
    };
}


(function(){
    
    
    
    let ViewChart = function (el, d) {

        



        d.range.from = page.convertDatum(d.range.from);
        d.range.to = page.convertDatum(d.range.to);

        d.list = $.map(d.list, function(item){
            item.date_time = page.convertDatum(item.date_time);
            return item;
        });
        


        let minDate=d.range.from;
        let maxDate=d.range.to;
        
        let minValue=100;
        let maxValue=-100;

        const ctx = el.getContext('2d');

        let colors = [
            {
                backgroundColor: 'rgba(255, 255, 255, 0.6)',
                borderColor: 'rgba(75, 192, 192, 0.8)',
                borderWidth: 4,
                tension: 0.3
            },
            {
                backgroundColor: 'rgba(200, 10, 5, 0.6)',
                borderColor: 'rgba(200, 10, 5, 0.8)',
                borderWidth: 4,
                tension: 0.3
            }
        ];
        


        let data = {
            labels: [],
            datasets:[]
        };

        let tmpDataset ={
                    type: 'line',
                    label: 'HST 1',
                    data: [12, 19, 3, 5, 2, 3],
                    backgroundColor: 'rgba(75, 192, 192, 0.6)',
                    borderColor: 'rgba(75, 192, 192, 0.8)',
                    borderWidth: 1
        };

        
        
            

            let x = $.parseJSON(JSON.stringify(tmpDataset));
            x = $.extend(true, x, colors[0]);
            x.label= "Temperature"; //d.name;
            x.data=[];
            
            if(d.list.length>0){
                x.data.push({
                    x:minDate,
                    y: d.list[0].value
                });
            }
            
            $.each(d.list, function(){
                if(minValue > this.value){
                    minValue=this.value;
                }
                if(maxValue < this.value){
                    maxValue=this.value;
                }
                
                x.data.push({
                    x:this.date_time,
                    y: this.value
                });
            });
            
            if(d.list.length>0){
                x.data.push({
                    x:maxDate,
                    y: d.list[d.list.length-1].value
                });
            }
            
            data.datasets.push(x);

            minValue -= 1;
            maxValue +=1;

        
        const config = {
            type: 'line',
            data: data,

            options: {
                animation: true,
                responsive: true,
                maintainAspectRatio: false,
                
                elements: {
                    line: {
                        borderWidth: 4,
                        tension: 0.6

                    },

                    point: {
                        radius: 3,
                        backgroundColor: 'white',
                        hoverRadius: 10
                    }
                },
                
                scales: {
                    x: {
                        type: 'time',
                        time: {
                            unit: 'hour',
                            stepSize1: 3,
                            tooltipFormat: 'yyyy-MM-dd HH:mm',
                            displayFormats: {
                                    hour: 'HH'  // 24-hour format
                            }
                        },
                        grid: {
                            color: 'rgba(255,255,255,0.1)'
                        },
                        min: minDate,
                        max: maxDate
                    },
                    y: {
                        beginAtZero: true,
                        grid: {
                            color: 'rgba(255,255,255,0.1)'
                        },
                        min: minValue,
                        max: maxValue
                    }
                }
            },
            plugins: {
                title: {
                    display: false
                },
                legend: {
                    labels: {
                        usePointStyle: true,
                        pointStyle: false
                    }
                },
                tooltip: {
                    enabled: false
                }
            }
        };



        let myChart = new Chart(ctx, config);




    };




    let teplota = $("div.chart-container.teplota").attr("data");
    teplota = $.parseJSON(teplota);
    
    let c = new ViewChart($("canvas#teplomer")[0], teplota);
    
})();

