 requirejs.config({
        baseUrl: "/js",
        paths: {
            'jquery': 'jquery-1.11.3.min',
            'jquery.bootstrap': 'bootstrap.min',
            'jquery.validate': 'jquery.validate.min',
            'jquery.form': 'jquery.form.min',
            'jquery.placeholder': 'jquery.placeholder.min',
            'jquery.mCustomScrollbar': 'jquery.mCustomScrollbar.min',
            'jquery.mousewheel': 'jquery.mousewheel.min',
            'jquery.SuperSlide': 'jquery.SuperSlide.2.1.1',
            'jquery.scrollTo':'jquery.scrollTo',
            'jquery.lazyload':'jquery.lazyload',
            'swiper':'swiper.jquery',
            'exp_topNavMenu':'exp_topNavMenu',
            'exp_app':'exp_app',
            'param':'param'
        },
        shim: {
            'bootstrap-maxlength': ['jquery'],
            'jquery.SuperSlide': ['jquery'],
            'jquery.scrollTo':['jquery','jquery.bootstrap'],
            'jquery.bootstrap': ['jquery'],
            'jquery.placeholder': ['jquery'],
            'jquery.mCustomScrollbar': ['jquery', 'jquery.mousewheel'],
            'jquery.lazyload':['jquery'],
            'swiper':['jquery']
        }
});




