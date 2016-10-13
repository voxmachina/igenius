/*jslint node: true, esversion: 6 */

"use strict";

const gulp = require('gulp');
const tar = require('gulp-tar');
const gzip = require('gulp-gzip');
const htmlReplace = require('gulp-html-replace');
const sass = require('gulp-sass');
const htmlMin = require('gulp-html-minifier');
const cleanCSS = require('gulp-clean-css');
const GulpSSH = require('gulp-ssh');
const config = require('./../../config/gulp.json');
const rename = require('gulp-rename');
const exec = require('child_process').exec;
const del = require('del');

let currentDateTimeStamp = new Date().getTime();

let gulpSSH = new GulpSSH({
    ignoreErrors: false,
    sshConfig: config.ssh
});

let rootDir = config.rootDir;

gulp.task('symlink:root', function(done) {
   let taskName = process.argv[process.argv.length-1];

   if (taskName === 'stage') {
       rootDir = config.stageDir;
   } 

   done();
});

gulp.task('symlink:index', ['symlink:root'], function() {
    gulp.src('release/index.html')
        .pipe(htmlReplace({
            'js': {
                src: [['app/main.'+currentDateTimeStamp+'.js']],
                tpl: '<script>var currentDateTimeStamp = '+currentDateTimeStamp+';</script><script src="%s" async></script>'
            },
            'analytics': {
                src: [['/api/www/services/content/public/analytics.'+currentDateTimeStamp+'.js']],
                tpl: '<script src="%s" async defer></script>'
            },
            'css': {
                src: [['/app/main.'+currentDateTimeStamp+'.css']],
                tpl: '<link rel="stylesheet" type="text/css" href="%s"/>'
            },
            'criticalCss': {
                src: gulp.src('./config/critical.scss').pipe(sass()).pipe(cleanCSS({compatibility: 'ie10'})),
                tpl: '<style>%s</style>'
            },
            'criticalHtml': {
                src: gulp.src('./config/critical.html').pipe(htmlMin()),
                tpl: '<?php $rootDir = "'+rootDir+'"; ?>%s'
            }
        }))
        .pipe(gulp.dest('release/'));
});

gulp.task('symlink:helpers', ['symlink:index'], function() {
    return gulp.src(["./release/app/*.css", "./release/app/*.js", "./release/lib/*.js"])
      .pipe(rename(function (path) {
          if (path.basename === 'main' || path.basename === 'config') {
              path.dirname = 'app'
          } else {
              path.dirname = 'lib'
          }
        path.basename += "." + currentDateTimeStamp;
        return path;
    })).pipe(gulp.dest("./release/"));
});

gulp.task('symlink:clean', ['symlink:helpers'], function() {
    return del([
            'release/app/**/*.ts',
            'release/app/**/*.scss',
            'release/maps',
            'release/lib',
            'release/app/main.js',
            'release/app/main.css',
            'release/app/services',
            'release/app/config.*',
            'release/config',
            'release/app/models',
            'release/app/services',
            'release/app/components',
            'release/lib/shim.*',
            'release/lib/helpers.min.js',
            'release/lib/zone.*',
            'release/lib/Reflect.*',
            'release/lib/system.*',
            'release/lib/core.*',
            'release/lib/common.*',
            'release/lib/compiler.*',
            'release/lib/platform-browser.*',
            'release/lib/platform-browser-dynamic.*',
            'release/lib/http.*',
            'release/lib/router.*',
            'release/lib/forms.*',
            'release/lib/rxjs'
        ]);
});

gulp.task('symlink:prepare', ['symlink:clean'], function() {
    return gulp.src(['release/**/*'], {
            dot: true
        })
        .pipe(tar('release-' + currentDateTimeStamp + '.tar'))
        .pipe(gzip())
        .pipe(gulp.dest('dist'))
        .pipe(gulpSSH.sftp('write', rootDir + 'release-' + currentDateTimeStamp + '.tar.gz'));
});

gulp.task('symlink:create', ['symlink:prepare'], function() {
    return gulpSSH
        .exec([
            'mkdir ' + rootDir + currentDateTimeStamp,
            'tar -xvf ' + rootDir + 'release-' + currentDateTimeStamp + '.tar.gz -C ' + rootDir + currentDateTimeStamp,
            'rm -rf ' + rootDir + 'release-' + currentDateTimeStamp + '.tar.gz',
            'rm -rf ' + rootDir + 'current',
            'ln -s ' + rootDir + currentDateTimeStamp + ' ' + rootDir + 'current',
            'mv ' + rootDir + currentDateTimeStamp + '/index.html ' + rootDir + currentDateTimeStamp + '/api/www/services/content/resources/views/index.php',
            'ln -s ' + rootDir + currentDateTimeStamp + '/api/www/services/content/resources/views/index.php ' + rootDir + currentDateTimeStamp + '/index.php'
        ]);
});

gulp.task('symlink:stage', ['symlink:create']);

gulp.task('symlink', ['symlink:create'], function() {
    let zoneId = config.cloudflare.zoneId;
    let apiKey = config.cloudflare.apiKey;
    let email = config.cloudflare.email;

    let cachePurge = 'curl -X DELETE "https://api.cloudflare.com/client/v4/zones/'+zoneId+'/purge_cache" -H "X-Auth-Email: '+email+'" -H "X-Auth-Key: '+apiKey+'" -H "Content-Type: application/json" --data \'{"purge_everything":true}\'';

     exec(cachePurge, function(err, stdout, stderr) {
         console.log(stdout);
         console.log(stderr);
     });
});
