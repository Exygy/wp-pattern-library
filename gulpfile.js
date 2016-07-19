const csso = require('gulp-csso');
const gulp = require('gulp');
const gutil = require('gulp-util');
const prefix = require('gulp-autoprefixer');
const sass = require('gulp-sass');
const webpack = require('webpack');

gulp.task('styles', () => {
	gulp.src('./assets/styles/fabricator.scss')
	.pipe(sass().on('error', sass.logError))
	.pipe(prefix('last 1 version'))
	.pipe(csso())
	.pipe(gulp.dest('./assets/styles'));
});

// scripts
const webpackConfig = require('./webpack.config')();

gulp.task('scripts', (done) => {
  webpack(webpackConfig, (err, stats) => {
    if (err) {
      gutil.log(gutil.colors.red(err()));
    }
    const result = stats.toJson();
    if (result.errors.length) {
      result.errors.forEach((error) => {
        gutil.log(gutil.colors.red(error));
      });
    }
    done();
  });
});

gulp.task('default', ['styles', 'scripts']);
