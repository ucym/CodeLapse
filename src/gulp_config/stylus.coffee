# gulp-stylus Examples
# https://github.com/stevelacy/gulp-stylus#examples
option      = require "./gulp.coffee"

nib         = require "nib"

module.exports =
    use         : [nib()]
    compress    : true
    sourcemap   :
        # inline      : true
        sourceRoot  : "css/"
