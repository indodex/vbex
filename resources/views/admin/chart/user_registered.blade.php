<script src="/js/chart/highcharts.js"></script>
<script src="/js/chart/modules/exporting.js"></script>
<div class="row">
    <div class="col-md-12">
        <div class="box box-info">
            <div class="box-header with-border">
                <h3 class="box-title">用户注册</h3>
                <div class="box-tools"></div>
            </div>
            <!-- /.box-header -->
            <!-- form start -->
            <form action="/admin/stats/register" method="get" accept-charset="UTF-8" class="form-horizontal" pjax-container="">
                <div class="box-body">
                    <div class="fields-group">
                        <div class="form-group  ">
                            <label for="startTime" class="col-sm-2 control-label">请选择时间</label>
                            <div class="col-sm-8">
                                <div class="row" style="width: 390px">
                                    <div class="col-lg-6">
                                        <div class="input-group">
                                            <span class="input-group-addon"><i class="fa fa-calendar"></i></span>
                                            <input type="text" name="startTime" value="{{ $startTime }}" class="form-control startTime" style="width: 160px">
                                        </div>
                                    </div>
                                    <div class="col-lg-6">
                                        <div class="input-group">
                                            <span class="input-group-addon"><i class="fa fa-calendar"></i></span>
                                            <input type="text" name="endTime" value="{{ $endTime }}" class="form-control endTime" style="width: 160px">
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>              
                    </div>
                </div>
                    <!-- /.box-body -->
                <div class="box-footer">
<!--                         <input type="hidden" name="_token" value="82wgUjdlbMOprTUFW9j52Qnes37amaBFgwGr72fn"> -->
                    <div class="col-md-2"></div>
                    <div class="col-md-8">
                        <div class="btn-group pull-right">
                            <button type="submit" class="btn btn-info pull-right" data-loading-text="<i class='fa fa-spinner fa-spin '></i> 提交">提交</button>
                        </div>
                    </div>
                </div>
            <!-- /.box-footer -->
            </form>
        </div>
        <div id="containerline" style="min-width: 310px; margin-top: 20px; width: 100%; height: 400px;"></div>
    </div>
</div>
<script type="text/javascript">

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
        type: 'line'
    },
    title: {
        text: '用户注册统计'
    },
    subtitle: {
        text: '用户每日注册数'
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
        name: '注册',
        data: {{ $lineData['val'] }}
    }]
});
</script>

