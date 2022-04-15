'use strict';

var csso = require('gulp-csso');
var del = require('del');
var gulp = require('gulp');
const sass = require('gulp-sass')(require('sass'));
var uglify = require('gulp-uglify');

gulp.task('styles', done => {
  gulp.src('assets/sass/**/*.scss')
    .pipe(sass({outputStyle: 'compressed'}).on('error', sass.logError))
    .pipe(csso())
    .pipe(gulp.dest('./assets/css'));

    done();
});

gulp.task('scripts', done => {
  return gulp.src('./assets/scripts/**/*.js')
  // Minify the file
    .pipe(uglify())
    // Output
    .pipe(gulp.dest('./assets/js'));

    done();
});

gulp.task('clean:scripts', done => {
  return del(['assets/js/*']);

  done();
});

gulp.task('clean:styles', done => {
  return del(['assets/css/*']);

  done();
});
