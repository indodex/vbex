<div class="row"><div class="col-md-12"><div class="box box-info">
    <div class="box-header with-border">
        <h3 class="box-title">创建</h3>
        <div class="box-tools"> </div>
    </div>
    <!-- /.box-header -->
    <!-- form start -->
    <form action="withdraws" method="post" accept-charset="UTF-8" class="form-horizontal" pjax-container="">
        <div class="box-body">
            <div class="fields-group">
                <div class="form-group  ">
                    <label for="startTime" class="col-sm-2 control-label">请选择时间</label>
                    <div class="col-sm-8">
                        <div class="row" style="width: 390px">
                            <div class="col-lg-6">
                                <div class="input-group">
                                    <span class="input-group-addon"><i class="fa fa-calendar"></i></span>
                                    <input type="text" name="startTime" value="" class="form-control startTime" style="width: 160px">
                                </div>
                            </div>
                            <div class="col-lg-6">
                                <div class="input-group">
                                    <span class="input-group-addon"><i class="fa fa-calendar"></i></span>
                                    <input type="text" name="endTime" value="" class="form-control endTime" style="width: 160px">
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
                            <option value=""></option>
                            <option value="1">比特币</option>
                            <option value="2">以太币</option>
                            <option value="3">EOS</option>
                            <option value="7">HAC</option>
                        </select>
                        <span class="select2 select2-container select2-container--default" dir="ltr" style="width: 100%;">
                            <span class="selection">
                                <span class="select2-selection select2-selection--single" role="combobox" aria-haspopup="true" aria-expanded="false" tabindex="0" aria-labelledby="select2-currency-3v-container">
                                    <span class="select2-selection__rendered" id="select2-currency-3v-container" title=""></span>
                                    <span class="select2-selection__arrow" role="presentation">
                                        <b role="presentation"></b>
                                    </span>
                                </span>
                            </span>
                            <span class="dropdown-wrapper" aria-hidden="true"></span>
                        </span>  
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
},
    title: {
        text: '用户注册统计'
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
