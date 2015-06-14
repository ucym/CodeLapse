path    = require "path"


module.exports =
    sourceDir   : path.join __dirname, "../src/"
    publishDir  : path.join __dirname, "../../"

    js          :
        vendorJsDir : "js/"
        uglify      : true
