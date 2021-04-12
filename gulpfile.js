const gulp         = require('gulp');
const concat       = require('gulp-concat');
const csso         = require('gulp-csso');
const autoprefixer = require('gulp-autoprefixer');
const sourcemaps   = require('gulp-sourcemaps');
const sass         = require('gulp-sass');
const size         = require("gulp-size");
const gzip         = require("gulp-gzip");
const rename       = require("gulp-rename")
sass.compiler      = require('node-sass');

/**
 * Task: gulp css
 */
 gulp.task("css", function () {
    return gulp
        .src([

            // Swal2
            'node_modules/sweetalert2/dist/sweetalert2.min.css',

            // Select2
            'node_modules/select2/dist/css/select2.min.css',

            // Flatpickr
            'node_modules/flatpickr/dist/flatpickr.min.css',

            // Trumbowyg
            'node_modules/trumbowyg/dist/ui/trumbowyg.min.css',
            'node_modules/trumbowyg/dist/plugins/table/ui/trumbowyg.table.css',

            // Flextype UI CSS
            'assets/src/flextype-ui.scss'
        ])
        .pipe(sass().on('error', sass.logError))
        .pipe(autoprefixer({
            overrideBrowserslist: [
                "last 1 version"
            ],
            cascade: false
        }))
        .pipe(csso())
        .pipe(concat('flextype-ui.min.css'))
        .pipe(gulp.dest("assets/dist/css/"))
        .pipe(size({ showFiles: true }))
        .pipe(gzip())
        .pipe(rename("flextype-ui.min.css.gz"))
        .pipe(gulp.dest("assets/dist/css/"))
        .pipe(size({ showFiles: true, gzip: true }));
});

/**
 * Task: gulp js
 */
 gulp.task('js', function () {
    return gulp
        .src([

            // jQuery Form Validatator
            'node_modules/jquery/dist/jquery.min.js',

            // ParsleyJS Form Validatator
            'node_modules/parsleyjs/dist/parsley.min.js',

            // Speakingurl
            'node_modules/speakingurl/speakingurl.min.js',

            // Select2
            'node_modules/select2/dist/js/select2.min.js',

            // Flatpickr
            'node_modules/flatpickr/dist/flatpickr.min.js',

            // Trumbowyg
            'node_modules/trumbowyg/dist/trumbowyg.min.js',
            'node_modules/trumbowyg/dist/plugins/noembed/trumbowyg.noembed.min.js',
            'node_modules/trumbowyg/dist/plugins/table/trumbowyg.table.min.js',

            // Bootstrap
            'node_modules/bootstrap/dist/js/bootstrap.bundle.min.js',

            // Flextype UI JS
            'fieldsets/fields/InputDateTimePicker/field.js',
            'fieldsets/fields/InputSelect/field.js',
            'fieldsets/fields/InputSelectMedia/field.js',
            'fieldsets/fields/InputSelectRoutable/field.js',
            'fieldsets/fields/InputTags/field.js',
            'fieldsets/fields/InputSelectTemplate/field.js',
            'fieldsets/fields/InputSelectVisibility/field.js',
            'fieldsets/fields/InputEditor/field.js',
        ])
        .pipe(concat('flextype-ui.min.js'))
        .pipe(size({ showFiles: true }))
        .pipe(gulp.dest('assets/dist/js/'))
        .pipe(gzip())
        .pipe(rename("flextype-ui.min.js.gz"))
        .pipe(gulp.dest("assets/dist/js/"))
        .pipe(size({ showFiles: true, gzip: true }));
 });

/**
 * Task: gulp trumbowyg-fonts
 */
 gulp.task('trumbowyg-fonts', function () {
    return gulp
        .src(['node_modules/trumbowyg/dist/ui/icons.svg'])
        .pipe(gulp.dest('assets/dist/fonts/trumbowyg'));
 });

/**
 * Task: gulp trumbowyg-langs
 */
 gulp.task('trumbowyg-langs', function () {
    return gulp
        .src(['node_modules/trumbowyg/dist/*langs/**/*'])
        .pipe(gulp.dest('assets/dist/lang/trumbowyg'));
 });

/**
 * Task: gulp flatpickr-langs
 */
 gulp.task('flatpickr-langs', function () {
    return gulp
        .src(['node_modules/flatpickr/dist/*l10n/**/*'])
        .pipe(gulp.dest('assets/dist/lang/flatpickr'));
 });

/**
 * Task: gulp default
 */
 gulp.task(
     'default',
     gulp.series(
         'trumbowyg-fonts',
         'trumbowyg-langs',
         'flatpickr-langs',
         'css',
         'js'
     )
 );

/**
 * Task: gulp watch
 */
 gulp.task('watch', function () {
    gulp.watch(
        ["fieldsets/**/*.html", "assets/src/"],
        gulp.series('css')
    );
 });
