//HTML
//import htmlmin from 'gulp-htmlmin'

//CSS
import sass from 'gulp-sass'
//const sass = require('gulp-sass')(require('sass'));
const cleanCSS = require('gulp-clean-css');
import autoprefixer from 'gulp-autoprefixer'

//JS
import gulp from 'gulp'
import babel from 'gulp-babel'
import terser from 'gulp-terser'

//Common
import concat from 'gulp-concat'

//variables/constantes

//ADMIN

gulp.task('styles-admin-bundle', () => {
    return gulp.src([    
            './resources/assets/metronic/plugins/global/plugins.bundle.css',
            './resources/assets/metronic/css/style.bundle.css',
            './resources/assets/metronic/plugins/custom/datatables/datatables.bundle.css',
            './node_modules/owl.carousel/dist/assets/owl.carousel.min.css',
        ])
        .pipe(concat('bundle.min.css'))
        .pipe(gulp.dest('./public/assets/admin/css'));
});

gulp.task('scripts-admin-bundle',() => {
    return gulp
        .src([
            './resources/assets/metronic/plugins/global/plugins.bundle.js',
            './resources/assets/metronic/js/scripts.bundle.js',
            './resources/assets/metronic/js/widgets.bundle.js',
            './resources/assets/metronic/plugins/custom/datatables/datatables.bundle.js',
            './resources/assets/plugins/vue.dev.js',
            './node_modules/owl.carousel/dist/owl.carousel.min.js',
            './resources/assets/utilities/sweet2.js',
            './resources/assets/utilities/helper.js',
            './resources/assets/utilities/theme.js'
        ])
        .pipe(concat('bundle.min.js'))
        .pipe(gulp.dest('./public/assets/admin/js'))
})

gulp.task('style-admin', () => {
    return gulp
        .src('./public/assets-gulp/scss/admin/**/*.scss')
        .pipe(sass({
            outputStyle: 'expanded',
            sourceComments: true
        }))
        .pipe(autoprefixer({
            versions: ['last 2 browser']
        }))
        .pipe(gulp.dest('./public/assets/admin/css'))
})

gulp.task('scripts-admin',() => {
    return gulp
        .src([
            './resources/assets/admin/js/*.js',
        ])
        .pipe(babel())
        .pipe(terser())
        .pipe(gulp.dest('./public/assets/admin/js'))
})


gulp.task('admin',() => {
    gulp.watch('./resources/assets/admin/js/**/*.js',gulp.parallel('scripts-admin'))
    // gulp.watch('./public/assets-gulp/es6/login/**/*.js',gulp.parallel('babel-login-admin'))
    // gulp.watch('./public/assets-gulp/scss/admin/**/*.scss',gulp.parallel('style-admin'))
})
