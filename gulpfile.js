var elixir = require('laravel-elixir');

/*
 |--------------------------------------------------------------------------
 | Elixir Asset Management
 |--------------------------------------------------------------------------
 |
 | Elixir provides a clean, fluent API for defining some basic Gulp tasks
 | for your Laravel application. By default, we are compiling the Sass
 | file for our application, as well as publishing vendor resources.
 |
 */

var paths = {
'bootstrap_select': './node_modules/bootstrap-select/dist/'
};

elixir(function(mix) {
    mix.babel([
        'lead-form-builder.js',
    ], 'public/assets/js/generated/lead-form-builder.js')
	.copy(paths.bootstrap_select + 'css/bootstrap-select.min.css', 'public/assets/css')
	.copy(paths.bootstrap_select + 'js/bootstrap-select.min.js', 'public/assets/js')
	;
});

/*
elixir(function(mix) {
    mix.sass('app.scss');
});

var paths = {
'jquery': './vendor/bower_components/jquery/'
, 'bootstrap': './vendor/bower_components/bootstrap-sass/assets/'
, 'fontawesome': './vendor/bower_components/font-awesome/'
, 'datatables': './vendor/bower_components/datatables/media/'
, 'adminlte': './vendor/bower_components/AdminLTE/dist/'
, 'bootstrap_notify': './vendor/bower_components/remarkable-bootstrap-notify/dist/'
, 'bower_components': './vendor/bower_components/'
, 'starterkit': './resources/assets/sass/starterkit/'
}

elixir(function(mix) {
    mix.sass('app.scss', 'public/css/'
			, {includePaths: [
				paths.bootstrap + 'stylesheets'
					, paths.fontawesome + 'scss'
					, paths.datatables + 'css'
					, paths.bower_components + 'eonasdan-bootstrap-datetimepicker/src/sass'
					, paths.adminlte + 'css'
					, paths.adminlte + 'css/skins'
					, paths.starterkit
			] })
        .copy(paths.bootstrap + 'fonts/bootstrap/**', 'public/fonts/bootstrap')
        .copy(paths.fontawesome + 'fonts/**', 'public/fonts/fontawesome')
        .scripts([
			'./resources/javascripts/error/_error_handler.js',
            paths.jquery + "dist/jquery.js",
			'./resources/javascripts/error/jquery_error_handler.js',
            paths.bower_components + "moment/min/moment.min.js",
            paths.datatables + "js/jquery.dataTables.js",
            paths.bootstrap + "javascripts/bootstrap.js",
            paths.datatables + "js/dataTables.bootstrap.js",
            paths.bower_components + "eonasdan-bootstrap-datetimepicker/src/js/bootstrap-datetimepicker.js",
            paths.adminlte + "js/app.js",
            paths.bootstrap_notify + "bootstrap-notify.js",
            './resources/javascripts/common/** /*.js',
            './resources/javascripts/anon/** /*.js',
            './resources/javascripts/logged/** /*.js',
        ], 'public/js/app.js', './')
        .version([
            'css/app.css',
            'js/app.js'
        ]);
});
*/
