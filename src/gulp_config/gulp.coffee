path    = require "path"


module.exports =
    sourceDir   : path.join __dirname, "../src/static/"
    publishDir  : path.join __dirname, "../../static/"

    js          :
        vendorJsDir : "js/"
        uglify      : true
