<script src="/js/chart/highcharts.js"></script>
<script src="/js/chart/modules/exporting.js"></script>
<div class="row">
    <div class="col-md-12">
        <div id="containerline" style="min-width: 310px; margin-top: 20px; width: 100%; height: 400px;"></div>
    </div>
</div>

<script data-exec-on-popstate>

    $(function () {
        $('.startTime').datetimepicker({"format":"YYYY-MM-DD HH:mm:ss","locale":"zh_CN"});
        $('.endTime').datetimepicker({"format":"YYYY-MM-DD HH:mm:ss","locale":"zh_CN","useCurrent":false});
        $(".startTime").on("dp.change", function (e) {
            $('.endTime').data("DateTimePicker").minDate(e.date);
        });
        $(".endTime").on("dp.change", function (e) {
            $('.startTime').data("DateTimePicker").maxDate(e.date);
        });
    });

    var chart = Highcharts.chart('containerline', {
        chart: {
            type: 'bar'
        },
        title: {
            text: '平台用户货币统计'
        },
        xAxis: {
            categories: {!! $chart_code !!}
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
            name: '虚拟币',
            data: {!! $chart_amount !!}
        }]
    });
</script>

