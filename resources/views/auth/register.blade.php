@include('includes.login_header')
	<canvas id="crain" style="position:absolute; top:0; left:0; right:0; bottom:0; height:100%; width:100%; display:block;"></canvas>
    <div class="container" style="position:relative; top:0; left:0; z-index:10;">
        <div class="title text-center"><a href="javascript:history.go(-1);" class="pull-left"><i class="fa fa-angle-left fa-2x" aria-hidden="true"></i> </a>{{ __('auth.register.register') }}</div>
        <div role="tabpanel" >
            <ul class="nav nav-tabs hidden" role="tablist" id="step">
                <li role="presentation" class="active"><a href="#step1" aria-controls="home" role="tab" data-toggle="tab"></a></li>
                <li role="presentation"><a href="#step2" aria-controls="profile" role="tab" data-toggle="tab"></a></li>
                <li role="presentation"><a href="#step3" aria-controls="messages" role="tab" data-toggle="tab"></a></li>
            </ul>
            <!-- Tab panes -->
            <div class="tab-content">
                <div role="tabpanel" class="tab-pane active" id="step1">
                    <div class="panel col-xs-12 col-sm-6 col-md-5">
                        <div class="fs-14 text-muted mb20">
                            @php
                                echo __('auth.register.top_message');
                            @endphp
                        </div>
                        <div class="input-grunp">
                            <input id="verify_mail" class="form-control input-lg" placeholder="{{ __('auth.register.email') }}" autocomplete="off" spellcheck="false" autocorrect="off" tabindex="1">
                        </div>
                        <div class="clearfix">
                            <button type="button" class="btn btn-primary form-control" href="javascript:;" id="submit-verify-mail">{{ __('auth.register.continue') }}</button>
                            <div class="col-md-12">
                                <a class="pull-right text-white" href="/login">{{ __('auth.login.login') }}</a>
                            </div>
                        </div>
                    </div>
                </div>
                <div role="tabpanel" class="tab-pane" id="step2">
                    <div class="panel col-xs-12 col-sm-6 col-md-5">
                        <div class="fs-14 text-muted mb20">
                            @php
                                echo __('auth.register.input_email_msg', ['email' => '<span id="send-verify-email"></span>']);
                            @endphp
                        </div>
                        <div class="clearfix"></div>
                        <div class="input-grunp mt20">
                            <input id="code" class="form-control input-lg" placeholder="_ _ _ _ _ _" autocomplete="off" spellcheck="false" autocorrect="off" tabindex="1">
                        </div>
                        <div class="clearfix">
                            <button type="button" class="btn btn-primary form-control" href="javascript:;" id="verify-code">{{ __('auth.register.continue') }}</button>
                            <div class="col-md-12">
                                <a class="pull-left text-white" href="javascript:$('#step li:eq(0) a').tab('show')">{{ __('auth.register.edit_email') }}</a>
                                <a class="pull-right text-white" href="javascript:$('#step li:eq(0) a').tab('show')">{{ __('auth.register.reset_send') }}</a>
                            </div>
                        </div>
                    </div>
                </div>
                <div role="tabpanel" class="tab-pane" id="step3">
                    <div class="panel col-xs-12 col-sm-6 col-md-5">
                        <div class="fs-14 text-muted mb20">
                            {{ __('auth.register.pl_input_password') }}
                        </div>
                        <div class="input-grunp">
                            <input id="name" class="form-control input-lg" placeholder="{{ __('auth.register.username') }}" autocomplete="off" spellcheck="false" autocorrect="off" tabindex="1">
                        </div>
                        <div class="input-grunp">
                            <input id="password" type="password" class="form-control input-lg" placeholder="{{ __('auth.register.password') }}" autocomplete="off" spellcheck="false" autocorrect="off" tabindex="1">
                        </div>
                        <div class="input-grunp">
                            <input id="comfim-password" type="password" class="form-control input-lg" placeholder="{{ __('auth.register.password_confirmation') }}" autocomplete="off" spellcheck="false" autocorrect="off" tabindex="1">
                        </div>
                        <div class="clearfix">
                            <button type="button" class="btn btn-primary form-control" id="do-register">{{ __('auth.register.continue') }}</button>
                            <div class="col-md-12">
                                <a class="pull-left text-white" href="javascript:$('#step li:eq(0) a').tab('show')">{{ __('auth.register.edit_email') }}</a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="logo-icon text-center"><img src="{{ asset('hac/img') }}/logo-icon.png"></div>
    </div>
    {{ csrf_field() }}
    <input type="hidden" name="invite_uid" id="invite_uid" value="{{ $inviteUid }}" />
@include('includes.login_footer')