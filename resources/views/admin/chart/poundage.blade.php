<script src="/js/chart/highcharts.js"></script>
<script src="/js/chart/modules/exporting.js"></script>
<div class="row">
    <div class="col-md-12">
        <div class="box box-info">
            <div class="box-header with-border">
                <h3 class="box-title">货币卖出手续费</h3>
                <div class="box-tools"></div>
            </div>
            <!-- /.box-header -->
            <!-- form start -->
            <form action="/admin/stats/trading" method="get" accept-charset="UTF-8" class="form-horizontal" pjax-container="">
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
                <div class="form-group  ">
                    <label for="currency" class="col-sm-2 control-label">币种</label>
                    <div class="col-sm-8">
                        <input type="hidden" name="currency">
                        <select class="form-control currency select2-hidden-accessible" style="width: 100%;" name="currency" tabindex="-1" aria-hidden="true">
                            <option value="0" @if ($currency == 0) selected @endif>全部</option>
                            @foreach ($currencyList as $val)
                            <option value="{{ $val['id'] }}" @if ($currency == $val['id']) selected @endif>{{ $val['text'] }}</option>
                            @endforeach
                        </select>
                        
                    </div>
                </div>
            </div>      
        </div>
        <!-- /.box-body -->
        <div class="box-footer">
            <input type="hidden" name="_token" value="w2hjyULQgx03jvPFIm0GUo5i7w97dQ6bgcRO8VQl">
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
    $(".currency").select2({"allowClear":true,"placeholder":"全部"});
});



Highcharts.chart('containerline', {
    chart: {
        type: 'line'
    },
    title: {
        text: '手续费统计'
    },
    legend: {
        layout: 'vertical',
        align: 'right',
        verticalAlign: 'middle'
    },
    // subtitle: {
    //     text: 'Source: WorldClimate.com'
    // },
    xAxis: {
        categories: {!! $lineData['title'] !!}
    },
    plotOptions: {
        line: {
            dataLabels: {
                enabled: true
            },
            enableMouseTracking: false
        }
    },
    series: {!! $lineData['val'] !!},
    responsive: {
        rules: [{
            condition: {
                maxWidth: 500
            },
            chartOptions: {
                legend: {
                    layout: 'horizontal',
                    align: 'center',
                    verticalAlign: 'bottom'
                }
            }
        }]
    }
});
</script>

