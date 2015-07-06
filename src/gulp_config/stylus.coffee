# gulp-stylus Examples
# https://github.com/stevelacy/gulp-stylus#examples
option      = require "./gulp.coffee"

nib         = require "nib"
bootstrap   = require "bootstrap-styl"

module.exports =
    use         : [nib(), bootstrap()]
    compress    : true
    sourcemap   :
        # inline      : true
        sourceRoot  : "css/"
