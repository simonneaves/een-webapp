var gulp = require('gulp'),
    sass = require('gulp-sass'),
    minify = require('gulp-minify');

gulp.task('js', function () {
    gulp.src([
        'node_modules/jquery/dist/jquery.js',
        'node_modules/govuk_frontend_toolkit/javascripts/govuk/selection-buttons.js',
        'node_modules/govuk_template_mustache/assets/javascripts/govuk-template.js',
        'drupal/themes/custom/een/js/*.js'
    ])
        .pipe(minify({
            ext: {
                src: '.js',
                min: '-min.js'
            },
            ignoreFiles: ['*-min.js']
        }))
        .pipe(gulp.dest('drupal/themes/custom/een/js'))
});

gulp.task('css', function () {
    gulp.src('drupal/themes/custom/een/scss/een.scss')
        .pipe(sass({
            outputStyle: 'compressed',
            sourceComments: 'map',
            includePaths: [
                'drupal/themes/custom/een/scss/',
                'node_modules/govuk_frontend_toolkit/stylesheets',
                'node_modules/govuk_template_mustache/assets/stylesheets',
                'node_modules/govuk-elements-sass/public/sass'
            ]
        }))
        .pipe(gulp.dest('drupal/themes/custom/een/css'));
});

gulp.task('default', ['css', 'js']);
