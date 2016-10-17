/*jslint node: true, esversion: 6 */

"use strict";

const gulp = require('gulp');
const runSequence = require('run-sequence');

gulp.task('stage:local:copy', function() {
    return gulp.src(['release/**/*'], {
        dot: true
    }).pipe(gulp.dest('build/'));
});

gulp.task('stage:local', ['clean'], function() {
    runSequence(
        'build',
        'release',
        'inline',
        'bundle',
        'minify',
        'symlink:clean',
        'stage:local:copy'
    );
});

gulp.task('stage', ['clean'], function() {
    runSequence(
        'build',
        'release',
        'inline',
        'bundle',
        'minify',
        'symlink:stage',
        'clean'
    );
});
