@include('includes.login_header')
	<canvas id="crain" style="position:absolute; top:0; left:0; right:0; bottom:0; height:100%; width:100%; display:block;"></canvas>
    <div class="container" style="position:relative; top:0; left:0; z-index:10;">
        
        <div class="title text-center"><a href="javascript:history.go(-1);" class="pull-left"><i class="fa fa-angle-left fa-2x" aria-hidden="true"></i> </a>{{ __('passwords.recovery_password') }}</div>
        <div role="tabpanel" >
            <!-- Tab panes -->
            <div class="tab-content">
                <div role="tabpanel" class="tab-pane active" id="step1">
                    <form class="form-horizontal" method="POST" action="{{ route('password.email') }}">
                        {{ csrf_field() }}
                        <div class="panel col-xs-12 col-sm-6 col-md-5">
                            @if (session('status'))
                                <div class="fs-14 text-muted mb20">
                                    {{ session('status') }}
                                </div>
                            @else
                                <div class="fs-14 text-muted mb20">
                                    {{ __('passwords.help_message') }}
                                </div>
                            @endif
                            <div class="input-grunp">
                                <input id="email" class="form-control input-lg" placeholder="{{ __('auth.login.email') }}" autocomplete="off" spellcheck="false" autocorrect="off" tabindex="1" type="email" name="email" value="{{ old('email') }}" required>
                            </div>
                            <div class="clearfix">
                                <button type="submit" class="btn btn-primary form-control">{{ __('passwords.send_email') }}</button>
                                <div class="col-md-12">
                                    <a class="pull-right text-white" href="/login">{{ __('auth.login.login') }}</a>
                                </div>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <div class="logo-icon text-center"><img src="{{ asset('hac/img') }}/logo-icon.png"></div>
    </div>
    {{ csrf_field() }}
@include('includes.login_footer')
