/*jslint node: true, esversion: 6 */

"use strict";

const gulp = require('gulp');
const runSequence = require('run-sequence');

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
