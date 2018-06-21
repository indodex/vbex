<template>
<div class="ucenter">
    <div class="container">
        <div>
            <left-menu></left-menu>
            <div class="col-xs-10 panel">
                <div class="">
                    <div class="panel-header">
                        我的哈希码 
                        <a class="fs-16 text-primary" href="javascript:void(0)" data-toggle="modal" data-target="#charge-use-desc">
                            <i class="icon-question"></i>
                            充值码功能使用说明
                        </a>
                        <span class="pull-right">
                            <a class="btn btn-default copy-btn btn-br4" role="button">检测哈希码是否可用</a>
                            <a class="btn btn-primary copy-btn btn-br4" role="button">检测</a>
                        </span>
                    </div>
                    <div class="panel-body ucenter-panel myhash">
                        <div class="row">
                            <div class="col-xs-12">
                                <div class="row mt20">
                                    <div class="col-xs-6 col-sm-3">
                                        累计金额：0元
                                    </div>
                                    <div class="col-xs-6 col-sm-3">
                                        累计金额：0元
                                    </div>
                                    <div class="col-xs-6 col-sm-3">
                                        累计金额：0元
                                    </div>
                                </div>
                                <div class="row mt20">
                                    <div class="col-xs-6 col-sm-3">
                                        累计数量：0元
                                    </div>
                                    <div class="col-xs-6 col-sm-3">
                                        累计数量：0元
                                    </div>
                                    <div class="col-xs-6 col-sm-3">
                                        累计数量：0元
                                    </div>
                                </div>
                            </div>
                            <div class="col-xs-12 help-form mt20">
                                <div>为您邀请的好友生成充值码，帮助他们快速充值吧</div>
                                    <form class="form-horizontal mt10" role="form">
                                      <div class="form-group">
                                        <label for="firstname" class="col-sm-1 control-label">金额：</label>
                                        <div class="col-sm-10">
                                            <label class="radio-inline">
                                                <input type="radio" name="optionsRadiosinline" id="optionsRadios1" value="option1" checked> 500元 
                                            </label>                              
                                            <label class="radio-inline">
                                                <input type="radio" name="optionsRadiosinline" id="optionsRadios2"  value="option2"> 1000元 
                                            </label>
                                            <label class="radio-inline">
                                                <input type="radio" name="optionsRadiosinline" id="optionsRadios3" value="option1"> 5000元 
                                            </label>
                                            <label class="radio-inline">
                                                <input type="radio" name="optionsRadiosinline" id="optionsRadios4"  value="option2"> 10000USD
                                            </label>
                                        </div>
                                      </div>
                                      <div class="form-group">
                                        <label for="count" class="col-sm-1 control-label">数量：</label>
                                        <div class="col-sm-1">
                                          <input type="text" class="form-control" id="count" placeholder="">

                                        </div>
                                        <div class="col-sm-8"><span class="text-muted lh-30">最多生成20个</span></div>
                                      </div>
                                      <div class="form-group">
                                        <label for="pwd" class="col-sm-1 control-label">密码：</label>
                                        <div class="col-sm-3">
                                          <input type="text" class="form-control" id="pwd" placeholder="请输入交易密码">
                                        </div>
                                      </div>
                                      <div class="form-group">
                                        <div class="col-sm-10 col-sm-offset-1">
                                          <a class="btn btn-default myhash" role="button">获取短信验证码</a>
                                          <a class="btn btn-default myhash" role="button">获取语音验证码</a>
                                        </div>
                                      </div>
                                      <div class="form-group">
                                        <label for="code" class="col-sm-1 control-label">验证码：</label>
                                        <div class="col-sm-3">
                                          <input type="text" class="form-control" id="code" placeholder="请输入验证码">
                                        </div>
                                      </div>
                                      <div class="form-group">
                                        <div class="col-sm-offset-1 col-sm-3">
                                          <button type="submit" class="btn btn-primary form-control copy-btn btn-br4">确认生成</button>
                                        </div>
                                      </div>
                                    </form>
                            </div>
                            <div class="clearfix"></div>
                            <div class="panel-header mt20">哈希码生成使用明细</div>
                            <table class="table">
                                <thead>                                                                                  
                                    <tr>
                                        <th>ID</th>
                                        <th>充值码</th>
                                        <th>金额</th>
                                        <th>生成时间</th>
                                        <th>使用时间</th>
                                        <th>使用者</th>
                                    </tr>
                                </thead>
                                <tbody class="bottom-border">
                                    <tr>                                                                                     
                                        <td>
                                            lanminghai@qq.com
                                        </td>
                                        <td>
                                            lanminghai
                                        </td>
                                        <td>
                                            100 
                                        </td>
                                        <td>
                                            2017-10-09 02:05:55 
                                        </td>
                                        <td>2019-09-28</td>
                                        <td>yingying</td>
                                    </tr>
                                    <tr>                                                                                     
                                        <td>
                                            lanminghai@qq.com
                                        </td>
                                        <td>
                                            lanminghai
                                        </td>
                                        <td>
                                            100 
                                        </td>
                                        <td>
                                            2017-10-09 02:05:55 
                                        </td>
                                        <td>2019-09-28</td>
                                        <td>yingying</td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

</template>

<script>
    import LeftMenu from './LeftMenu'
    export default {
        components: {
            LeftMenu
        }
    }

</script>



<style>
  @import url('/css/hac-content.css');
</style>



