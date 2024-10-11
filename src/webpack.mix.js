const {mix} = require('laravel-mix');

const params = {
    fineuploader: 'node_modules/fine-uploader/fine-uploader',
    fastselect: 'node_modules/fastselect/dist',
    fastsearch: 'node_modules/fastsearch/dist',
    "jquery.easing": 'node_modules/jquery.easing',
};

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

mix.js('resources/assets/js/app.js', 'public/js')
    .sass('resources/assets/sass/app.scss', 'public/css')
    .styles([
        'vendor/kartik-v/bootstrap-fileinput/css/fileinput.min.css',
        'node_modules/bootstrap-timepicker/css/bootstrap-timepicker.min.css',
        'node_modules/bootstrap-datepicker/dist/css/bootstrap-datepicker.css',
        'node_modules/bootstrap-vertical-tabs/bootstrap.vertical-tabs.css',
        'resources/assets/plugins/tributejs/tribute.css',
        'resources/assets/plugins/bootstrap-multiselect/bootstrap-multiselect.css',
    ], 'public/css/plugins.css')
    .js('resources/assets/js/profile-image-uploader.js', 'public/js/profile-image-uploader.js')
    .js('resources/assets/js/doctor-provider_relationship.js', 'public/js/doctor-provider_relationship.js')
    .js('resources/assets/js/doctor-provider_profile-tridiuum.js', 'public/js/doctor-provider_profile-tridiuum.js')
    .js('resources/assets/js/doctor-profile.js', 'public/js/doctor-profile.js')
    .js('resources/assets/js/invite-btn.js', 'public/js/invite-btn.js')
    .js('resources/assets/js/exams.js', 'public/js/exams.js')
    .js('resources/assets/js/profile-jSignature.js', 'public/js/profile-jSignature.js')
    .js('resources/assets/js/Alert.js', 'public/js/Alert.js')
    .js('resources/assets/js/salary.js', 'public/js/salary.js')
    .js('resources/assets/js/doctors-sort.js', 'public/js/doctors-sort.js')
    .js('resources/assets/js/update-notifications/index.js', 'public/js/update-notifications/index.js')
    .js('resources/assets/js/update-notifications/form.js', 'public/js/update-notifications/form.js')
    .js('resources/assets/js/update-notifications/viewed-list.js', 'public/js/update-notifications/viewed-list.js')
    .js('resources/assets/js/update-notifications/history.js', 'public/js/update-notifications/history.js')
    .js('resources/assets/js/update-notifications/user-notifications.js', 'public/js/update-notifications/user-notifications.js')
    .js('resources/assets/js/update-notification-templates/index.js', 'public/js/update-notification-templates/index.js')
    .js('resources/assets/js/update-notification-templates/form.js', 'public/js/update-notification-templates/form.js')
    .js('resources/assets/js/tabs-doctor-profile.js', 'public/js/tabs-doctor-profile.js')
    .js('resources/assets/js/tabs-supervising.js', 'public/js/tabs-supervising.js')
    .scripts([
        'vendor/kartik-v/bootstrap-fileinput/js/plugins/piexif.min.js',
        'vendor/kartik-v/bootstrap-fileinput/js/plugins/sortable.min.js',
        'vendor/kartik-v/bootstrap-fileinput/js/plugins/purify.min.js',
        'vendor/kartik-v/bootstrap-fileinput/js/fileinput.min.js',
        'vendor/kartik-v/bootstrap-fileinput/themes/fa/theme.min.js',
        'vendor/kartik-v/bootstrap-fileinput/js/locales/LANG.js',
        'node_modules/jsignature/libs/flashcanvas.js',
        'node_modules/jsignature/libs/jSignature.min.js',
        'resources/assets/js/parsley.js',
        'resources/assets/js/bootstrap-timepicker.js',
        'resources/assets/js/datepicker-polyfill.js',
        'resources/assets/plugins/tributejs/tribute.js',
        'resources/assets/plugins/bootstrap-multiselect/bootstrap-multiselect.js',
    ], 'public/js/plugins.js')
    .copy([
        params.fineuploader + "/*.min.js",
        params.fineuploader + "/*.min.js.map",
        params.fineuploader + "/*.min.css.map",
        params.fineuploader + "/*.gif",
    ], 'public/plugins/fine-uploader')
    .copy(params.fineuploader + '/placeholders', 'public/plugins/fine-uploader/placeholders')
    .copy([
        params.fastselect + "/*.min.js",
        params.fastselect + "/*.min.css",
        params.fastsearch + "/*.min.js",
    ], 'public/plugins/fastselect')
    .copy([
        params["jquery.easing"] + "/jquery.easing.1.3.min.js",
    ], 'public/plugins/jquery.easing/jquery.easing.1.3.min.js')
    .copy([
        "node_modules/video.js/dist/video.min.js",
        "node_modules/video.js/dist/video-js.min.css",
        "node_modules/videojs-playlist/dist/videojs-playlist.min.js",
        "node_modules/videojs-playlist-ui/dist/videojs-playlist-ui.min.js",
        "node_modules/videojs-playlist-ui/dist/videojs-playlist-ui.css",
        "node_modules/videojs-playlist-ui/dist/videojs-playlist-ui.vertical.css",
    ], 'public/plugins/videojs');

mix.js('resources/assets/js/forms-app.js', 'public/js/forms-app.js')
    .sass('resources/assets/sass/forms-app.scss', 'public/css')
    .scripts([
        'node_modules/jsignature/libs/flashcanvas.js',
        'node_modules/jsignature/libs/jSignature.min.js',
        'vendor/kartik-v/bootstrap-fileinput/js/plugins/piexif.min.js',
        'vendor/kartik-v/bootstrap-fileinput/js/plugins/sortable.min.js',
        'vendor/kartik-v/bootstrap-fileinput/js/plugins/purify.min.js',
        'vendor/kartik-v/bootstrap-fileinput/js/fileinput.min.js',
        'vendor/kartik-v/bootstrap-fileinput/themes/fa/theme.min.js',
        'vendor/kartik-v/bootstrap-fileinput/js/locales/LANG.js'
    ], 'public/js/forms-plugins.js')
    .styles([
        'vendor/kartik-v/bootstrap-fileinput/css/fileinput.min.css'
    ], 'public/js/forms-plugins.css');

mix.scripts('resources/assets/js/document_download.js', 'public/js/document_download.js')
    .sass('resources/assets/sass/document-download.scss', 'public/css')
    .sass('resources/assets/sass/exams.scss', 'public/css');

mix.copyDirectory('vendor/tinymce/tinymce', 'public/js/tinymce');
