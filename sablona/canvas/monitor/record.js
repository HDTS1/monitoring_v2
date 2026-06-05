(function () {

    let chart = function (el, d) {

        const ctx = el.getContext('2d');

        const data = {
            labels: ['2023-11-01', '2023-11-02', '2023-11-03', '2023-11-04', '2023-11-05', '2023-11-06'],
            datasets: [{
                    label: 'Belt runs',
                    data: [12, 19, 3, 5, 2, 3],
                    backgroundColor: 'rgba(75, 192, 192, 0.2)',
                    borderColor: 'rgba(75, 192, 192, 1)',
                    borderWidth: 1
                }]
        };


        data.labels = $.map(d.data, function (item) {
            return item.den;
        });

        data.datasets[0].data = $.map(d.data, function (item) {
            if (!item.cycle_count)
                item.cycle_count = 0;
            return item.cycle_count;
        });


        const config = {
            type: 'bar',
            data: data,
            
            interaction: {
                intersect: false
            },
            options: {
                animation: true,
                responsive: true,
                maintainAspectRatio: true,
                scales: {
                    x: {
                        type: 'time',
                        time: {
                            unit: 'day',
                            tooltipFormat: 'dd.MM.yyyy',
                            displayFormats: {
                                day: 'dd.MM'
                            }
                        }
                    },
                    y: {
                        beginAtZero: true
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



        const myChart = new Chart(ctx, config);

    };




    /*
    let container = $("div#graf");
    let plc = $(container).attr("kluc");
    zapis("/rest/monitor/getPLCTrenning", {data: {plc: plc}, json: true}, function (odpoved) {
        let c = new chart($("#v")[0], odpoved.data);
    });
     * 
     */
})();
