let mix = require('laravel-mix');

/*
 |--------------------------------------------------------------------------
 | Mix Asset Management
 |--------------------------------------------------------------------------
 |
 | Mix provides a clean, fluent API for defining some Webpack build steps
 | for your Laravel application. By default, we are compiling the Sass
 | file for the application as well as bundling up all the JS files.
 |
 */
//mix.webpackConfig({
//	module: {
//		rules:[
//			{
//				options:{
//					loaders:{
//						i18n: '@kazupon/vue-i18n-loader',
//					}
//				}
//			}
//		]
//	}
//})


mix.js('resources/assets/js/app.js', 'public/js');
//.version()
// .sass('resources/assets/sass/app.scss', 'public/css');
