(function () {
    let chart;


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
        
        let getRandomColor = function() {
            let p = colorGenerator.getRandomBoldColor();
            p= p.join(",");
            return `rgba(${p}, 0.8)`;
        };
        
        




    let createChart = function(d) {
        
        let rozhranie = {
            min: 10,
            max: 25
        };
        
        
        
        let line_max = {

                    label: 'max',
                    fill: true,
                    borderColor: 'rgba(200,0,0,0.6)',
                    borderWidth: 2,
                    tension: 0.3,
                    backgroundColor: 'rgba(0,200,0,0.1)',
                    borderDash: [5, 5],
                    
                    data: [{
                            x: d.date.min,
                            y: rozhranie.max
                        },
                        {
                            x: d.date.max,
                            y: rozhranie.max
                        }


                    ]
                };
        let line_min = {

                label: 'min',
                fill: true,
                borderColor: 'rgba(0,0,200,0.6)',
                borderWidth: 2,
                tension: 0.3,
                backgroundColor: 'rgba(0,0,200,0.2)',
                borderDash: [5, 5],

                data: [{
                        x: d.date.min,
                        y: rozhranie.min
                    },
                    {
                        x: d.date.max,
                        y: rozhranie.min
                    }


                ]
            };   
        let dataSet = {

                label: 'T.CZ.PR.1',
                fill: false,
                borderColor: getRandomColor(),
                borderWidth: 4,
                tension: 0.3,

                data: [{
                        x: '2024-10-24 02:00:00',
                        y: 5
                    },
                    {
                        x: '2024-10-24 03:00:00',
                        y: 8
                    },

                    {
                        x: '2024-10-24 06:00:00',
                        y: 10.5
                    },

                    {
                        x: '2024-10-24 16:00:00',
                        y: 14.3
                    }


                ]
            };
        
        
    const ctx = document.getElementById('myChart');
    let data = {
        
        datasets: [

            

        ]
    };
    
    let minDate = d.date.min ;
    let maxDate = d.date.max;

    $.each(d.list, function(){
        let dataList = $.map(this.graf, function(item){
            let x = {
                x:item.date_time,
                y: parseFloat(item.value)
            };
            return x;
        });
        
        let d_set = JSON.stringify(dataSet);
        d_set = $.parseJSON(d_set);
        d_set.label = this.teplomer;
        d_set.data = dataList;
        d_set.borderColor = getRandomColor();
        
        
        data.datasets.push(d_set);
       
        
    });

    
    data.datasets.push(line_max);
    data.datasets.push(line_min);
    
    
    

    $.each(data.datasets, function () {
        this.data.sort(function (a, b) {
            let d = function (value) {
                value = value.replace(/-/g, "/");
                let dd = new Date(value);
                return dd;
            };
            return d(a.x) < d(b.x);
        });
    });


    Chart.defaults.backgroundColor = 'red';
    Chart.defaults.borderColor = 'rgba(0,0,0,0.2)';
    Chart.defaults.color = 'white';

    let chart = new Chart(ctx, {
        type: 'line',
        data: data,
        labels: ['2024-10-24 00:00', '2024-10-24 03:00', '2024-10-24 06:00', '2024-10-24 09:00', '2024-10-24 12:00', '2024-10-24 15:00', '2024-10-24 18:00', '2024-10-24 21:00', '2024-10-24 24:00'],
        interaction: {
            intersect: false
        },

        options: {
            animation: false,
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

            plugins: {
                
                tooltip: {
                    callbacks: {
                        // Custom callback to display time in 24-hour format
                        title: function(context) {
                            
                                //const label = context.label;  
                                //const parsedValueY = context.parsed.y;
                                
                                //console.log(label);
                                
                            /*
                            const date = new Date(context.label); // Convert the label (timestamp) to Date object
                            const hours = date.getHours().toString().padStart(2, '0'); // Get hours in 24-hour format
                            const minutes = date.getMinutes().toString().padStart(2, '0'); // Get minutes
                            return `${hours}:${minutes} - ${context.formattedValue}`; // Return formatted time and value
                             * 
                             */
                        }
                    }
                },
                
                
                legend: {
                    labels: {
                        usePointStyle: true
                    }
                },

                customCanvasBackgroundColor: {
                    color: 'lightGreen'
                }

            },

            scales: {
                x: {
                    type: 'time',
                    time: {
                        unit: 'hour',
                        stepSize: 3,
                        displayFormats: {
                                hour: 'HH'  // 24-hour format
                        }
                    },
                    min: minDate,
                    max: maxDate
                },

                y: {
                    min1: -10
                }
            }
        }
    });

    };
    
    
    


    let containerEl = $("div.chart-container");
    let canvasEl = $("#myChart");
    canvasEl.hide();
    
    containerEl.append('<div class="chart-loading" style="text-align: center; padding: 100px 20px; color: rgba(255,255,255,0.5);"><i class="fa fa-spinner fa-spin fa-2x"></i><br><br>Načítavam graf teplôt...</div>');

    zapis("/rest/monitor/dataGrafTeplotaAdmin", {data:null, json:true}, function(odpoved){
        containerEl.find(".chart-loading").remove();
        if (odpoved.result && odpoved.data) {
            canvasEl.show();
            createChart(odpoved.data);
        } else {
            containerEl.append('<div style="text-align: center; padding: 100px 20px; color: #ff5b5b;">Nepodarilo sa načítať graf teplôt.</div>');
        }
    });
    

    
    
})();


