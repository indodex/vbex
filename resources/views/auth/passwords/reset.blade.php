@include('includes.login_header')
    <canvas id="crain" style="position:absolute; top:0; left:0; right:0; bottom:0; height:100%; width:100%; display:block;"></canvas>
    <div class="container" style="position:relative; top:0; left:0; z-index:10;">
        <div class="title text-center">{{ __('passwords.reset_title') }}</div>
        <div role="tabpanel" >
            <div class="tab-content">
                <div role="tabpanel" class="tab-pane active" id="step1">
                    <form class="form-horizontal" method="POST" action="{{ route('password.request') }}">
                        {{ csrf_field() }}

                        <input type="hidden" name="token" value="{{ $token }}">
                        <div class="panel col-xs-12 col-sm-6 col-md-5">
                            <div class="input-grunp">
                                <input id="email" class="form-control input-lg" placeholder="{{ __('passwords.email') }}" autocomplete="off" spellcheck="false" autocorrect="off" tabindex="1" type="email" name="email" value="{{ $email or old('email') }}" required>
                                @if ($errors->has('email'))
                                    <span class="help-block">
                                        <strong>{{ $errors->first('email') }}</strong>
                                    </span>
                                @endif
                            </div>
                            <div class="input-grunp">
                                <input id="password" type="password" class="form-control input-lg" placeholder="{{ __('passwords.password_new') }}" autocomplete="off" spellcheck="false" autocorrect="off" tabindex="1" name="password" required>
                                @if ($errors->has('password'))
                                    <span class="help-block">
                                        <strong>{{ $errors->first('password') }}</strong>
                                    </span>
                                @endif
                            </div>

                            <div class="input-grunp">
                                <input id="repwd" type="password" class="form-control input-lg" placeholder="{{ __('passwords.password_confirmation') }}" autocomplete="off" spellcheck="false" autocorrect="off" tabindex="1" name="password_confirmation" required>
                                @if ($errors->has('password_confirmation'))
                                    <span class="help-block">
                                        <strong>{{ $errors->first('password_confirmation') }}</strong>
                                    </span>
                                @endif
                            </div>
                            <div class="clearfix">
                                <button type="submit" class="btn btn-primary form-control">{{ __('passwords.reset_button') }}</button>
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

