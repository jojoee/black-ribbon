var gulp = require('gulp'),
  connect = require('gulp-connect-php'),
  zip = require('gulp-zip'),
  clean = require('gulp-clean'),
  fs = require('fs'),
  browserSync = require('browser-sync');

const mainFile = 'black-ribbon.php';
const mainFileContent = fs.readFileSync(mainFile, 'utf8');
const pluginVersion = /^Version.*$/igm.exec(mainFileContent)[0].substring(9).trim();
const pluginName = /^Plugin Name.*$/igm.exec(mainFileContent)[0].substring(13).trim().toLowerCase();
const packageFolderName = pluginName + '-' + pluginVersion;

gulp.task('connect-sync', function() {
  connect.server({}, function() {
    browserSync({
      proxy: 'wp12.dev'
    });
  });

  gulp.watch([
    '**/*.php',
    'css/*.css',
  ]).on('change', function() {
    browserSync.reload();
  });
});

gulp.task('clean', function () {
  return gulp.src(pluginName + '-*', {read: false})
    .pipe(clean());
});

gulp.task('pack', ['clean'], function () {
  return gulp.src([
    'css/**',
    'image/**',
    'black-ribbon.php',
    'readme.txt',
    'screenshot-1.jpg',
    'screenshot-2.jpg'
  ], {base: '.'})
    .pipe(gulp.dest(packageFolderName));
});

gulp.task('pack.zip', ['pack'], function () {
  return gulp.src(packageFolderName + '/**')
    .pipe(zip(packageFolderName + '.zip'))
    .pipe(gulp.dest('./'));
});

gulp.task('default', ['connect-sync']);
gulp.task('watch', ['connect-sync']);
