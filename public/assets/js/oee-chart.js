function initChart(chartData) {
    const chartElement = document.getElementById('oeeChart');
    if (!chartElement) return;

    const options = {
        series: [{
            name: 'Availability',
            data: chartData.availability || []
        }, {
            name: 'Performance',
            data: chartData.performance || []
        }, {
            name: 'Quality',
            data: chartData.quality || []
        }, {
            name: 'OEE Score',
            data: chartData.oee || []
        }],
        chart: {
            height: 350,
            type: 'line',
            zoom: {
                enabled: false
            }
        },
        dataLabels: {
            enabled: false
        },
        stroke: {
            curve: 'straight',
            width: 2
        },
        grid: {
            row: {
                colors: ['#f3f3f3', 'transparent'], // mengambil warna dari NiceAdmin
                opacity: 0.5
            }
        },
        colors: ['#2eca6a', '#4154f1', '#ff771d', '#7928ca'], // warna yang sesuai dengan tema
        xaxis: {
            categories: chartData.labels || [],
            labels: {
                style: {
                    fontSize: '12px'
                }
            }
        },
        yaxis: {
            max: 100,
            min: 0,
            labels: {
                formatter: function(val) {
                    return val.toFixed(1) + "%"
                }
            }
        },
        legend: {
            position: 'top',
            horizontalAlign: 'center',
            floating: true,
            offsetY: -25,
            offsetX: -5
        }
    };

    const chart = new ApexCharts(chartElement, options);
    chart.render();
    return chart;
}

function updateChart(chart, data) {
    if (!chart) return;

    chart.updateSeries([{
        data: data.availability
    }, {
        data: data.performance
    }, {
        data: data.quality
    }, {
        data: data.oee
    }]);

    chart.updateOptions({
        xaxis: {
            categories: data.labels
        }
    });
}