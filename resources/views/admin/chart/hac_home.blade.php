<script src="/js/chart/highcharts.js"></script>
<script src="/js/chart/modules/exporting.js"></script>

<div id="container" style="min-width: 310px; width: 100%; height: 400px;"></div>
<div id="containerline" style="min-width: 310px; margin-top: 20px; width: 100%; height: 400px;"></div>


<script type="text/javascript">

Highcharts.chart('container', {
    chart: {
        plotBackgroundColor: null,
        plotBorderWidth: null,
        plotShadow: false,
        type: 'pie'
    },
    title: {
        text: 'HAC 总量以及使用统计'
    },
    subtitle: {
        text: '总量: {{ $hacTotal['total'] }}'
    },
    tooltip: {
        pointFormat: '{series.name}: <b>{point.percentage:.1f}%</b>'
    },
    plotOptions: {
        pie: {
            allowPointSelect: true,
            cursor: 'pointer',
            dataLabels: {
                enabled: true,
                format: '<b>{point.name}</b> 占比: {point.percentage:.1f} %</b>',
                style: {
                    color: (Highcharts.theme && Highcharts.theme.contrastTextColor) || 'black'
                }
            }
        }
    },
    series: [{
        name: '占比',
        colorByPoint: true,
        data: [{
            name: '未使用:{{ $hacTotal['availableTotal'] }}',
            y: {{ $hacTotal['availableTotal'] }},
            sliced: true,
            selected: true
        }, {
            name: '已使用:{!! $hacTotal['useTotal'] !!}',
            y: {{ $hacTotal['useTotal'] }}
        }]
    }]
});


var chart = Highcharts.chart('containerline', {
    chart: {
        type: 'line'
    },
    title: {
        text: 'HAC充值使用'
    },
    subtitle: {
        text: '使用总量: {{ $lineData['sumAmount'] }}'
    },
    xAxis: {
        categories: {!! $lineData['title'] !!}
    },
    plotOptions: {
        line: {
            dataLabels: {
                enabled: true          // 开启数据标签
            },
            enableMouseTracking: false // 关闭鼠标跟踪，对应的提示框、点击事件会失效
        }
    },
    series: [{
        name: '充值',
        data: {{ $lineData['val'] }}
    }]
});


        </script>