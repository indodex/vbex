@include('includes.login_header')
    <div class="container">
        <div class="title text-center">{{ __('auth.login.login') }}</div>
        <form method="POST" action="javascript:;" id="login-form">
            {{ csrf_field() }}
            <div class="panel col-xs-12 col-sm-6 col-md-5">
                <div class="input-grunp">
                    <label for="email"><i class="icon-envelope" aria-hidden="true"></i><span class="sr-only">Search icons</span></label>
                    <input id="email" class="form-control input-lg" type="email" placeholder="{{ __('auth.login.email') }}" autofocus autocomplete="off" spellcheck="false" autocorrect="off" tabindex="1" name="email" required>
                </div>
                <div class="input-grunp">
                    <label for="password"><i class="icon-lock" aria-hidden="true"></i><span class="sr-only">Search icons</span></label>
                    <input id="password" type="password" class="form-control input-lg" placeholder="{{ __('auth.login.password') }}" autocomplete="off" spellcheck="false" autocorrect="off" tabindex="1" name="password" required>
                </div>
                @if ($errors->has('email'))
                    <span class="help-block">
                        <strong>{{ $errors->first('email') }}</strong>
                    </span>
                @endif
                @if ($errors->has('password'))
                    <span class="help-block">
                        <strong>{{ $errors->first('password') }}</strong>
                    </span>
                @endif
                <div class="clearfix">
                    <button type="button" class="btn btn-primary form-control" id="do-login">{{ __('auth.login.login') }}</button>
                    <div class="col-md-12">
                        <a class="pull-left text-white" href="/password/reset">{{ __('auth.login.forget_password') }}</a>
                        <a class="pull-right text-white" href="/register">{{ __('auth.register.register') }}</a>
                    </div>
                </div>
            </div>
        </form>
        <div class="logo-icon text-center"><img src="{{ asset('hac/img') }}/logo-icon.png"></div>
    </div>
@include('includes.login_footer')
