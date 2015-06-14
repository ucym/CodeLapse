g       = require "gulp"
$       = do require "gulp-load-plugins"

bs      = require "browser-sync"
spawn   = require("child_process").spawn
path    = require "path"

option  = require "./gulp_config/gulp.coffee"

genPaths = (dir, ext, withinDirs = []) ->
    if (ext isnt null or ext isnt "") and ext[0] isnt "."
        ext = ".#{ext}"

    if dir isnt ""
        dir = "#{dir}/"

    return [
        "#{option.sourceDir}/#{dir}**/*#{ext}"
        "!#{option.sourceDir}/#{dir}**/_*#{ext}"
        "!#{option.sourceDir}/#{dir}_*/**"
    ].concat withinDirs

#
# Webpack Task
#
g.task "webpack", (cb) ->
    g.src genPaths("coffee", ".coffee").concat(genPaths("js", ".js"))
        .pipe $.plumber()
        .pipe $.webpack(require("./gulp_config/webpack.coffee"))
        # .pipe $.if(option.js.uglify, $.uglify())
        .pipe g.dest("#{option.publishDir}/js/")

#
# JavaScript copy Task
#
g.task "vendor_js", ->
    g.src genPaths("vendor_js", ".js")
        .pipe $.plumber()
        .pipe g.dest("#{option.publishDir}/#{option.js.vendorJsDir}/")

g.task "fonts_copy", ->
    g.src genPaths("fonts", ".{ttf,otf,eot,woff,svg}")
        .pipe $.plumber()
        .pipe g.dest("#{option.publishDir}/fonts/")

#
# CSS copy task
#
g.task "css_copy", ->
    g.src genPaths("css", ".css")
        .pipe $.plumber()
        .pipe g.dest("#{option.publishDir}/css/")

#
# Sass Task
#
g.task "sass", ->
    g.src genPaths("sass", ".{sass,scss}")
        .pipe $.plumber()
        .pipe $.sass(require("./gulp_config/sass.coffee")).on('error', $.sass.logError)
        .pipe g.dest("#{option.publishDir}/css/")

#
# Stylus Task
#
g.task "stylus", ->
    g.src genPaths("styl", ".styl")
        .pipe $.plumber()
        .pipe $.stylus(require("./gulp_config/stylus.coffee"))
        .pipe g.dest("#{option.publishDir}/css/")

#
# Jade Task
#
g.task "jade", ->
    g.src [
        "#{option.sourceDir}/../**/*.jade"
        "!#{option.sourceDir}/../**/_*.jade"
        "!#{option.sourceDir}/../_*/**"
        "!#{option.sourceDir}/coffee/**/*.jade"
    ]
        .pipe $.plumber()
        .pipe $.jade()
        .pipe $.prettify()
        .pipe g.dest("#{option.publishDir}/../")

#
# Image minify Task
#
g.task "images", ->
    g.src genPaths("img", ".{png,jpg,jpeg,gif}")
        .pipe $.imagemin(require("./gulp_config/imagemin.coffee"))
        .pipe g.dest("#{option.publishDir}/img/")

#
# File watch Task
#
g.task "watch", ->
    $.watch [
        "#{option.sourceDir}/coffee/**/*.{coffee,jade,cson}"
        "#{option.sourceDir}/js/**/*.{js,jade,cson}"
    ], ->
        g.start ["webpack"]

    $.watch ["#{option.sourceDir}/vendor_js/**/*.js"], ->
        g.start ["vendor_js"]

    $.watch ["#{option.sourceDir}/fonts/**/*.{ttf,otf,eot,woff,svg}"], ->
        g.start ["fonts_copy"]

    $.watch ["#{option.sourceDir}/css/**/*.css"], ->
        g.start ["css_copy"]

    $.watch ["#{option.sourceDir}/sass/**/*.{sass,scss}"], ->
        g.start ["sass"]

    $.watch ["#{option.sourceDir}/styl/**/*.styl"], ->
        g.start ["stylus"]

    $.watch [
        path.join(option.sourceDir, "../**/*.jade")
        "!#{option.sourceDir}/coffee/**/*.jade"
        "!#{option.sourceDir}/js/**/*.jade"
    ], ->
        g.start ["jade"]

    $.watch ["#{option.sourceDir}/img/**/*.{png,jpg,jpeg,gif}"], ->
        g.start ["images"]

#
# Browser-Sync task
#
g.task "bs", ->
    bs
        port    : 3000
        open    : false
        notify  : false
        index   : "index.html"
        server  :
            baseDir : option.publishDir + "/../"
            routes  :
                "/CodeLapse/"   : option.publishDir + "/../"

#
# Gulpfile watcher
#
g.task "self-watch", ["bs"], ->
    proc    = null
    command = null
    args    = null

    if /^win/.test(process.platform)
        # windows
        command = "cmd"
        args = ["/c", "gulp", "devel"]
    else
        command = "gulp"
        args = ["devel"]

    spawnChildren = ->
        proc.kill() if proc?
        proc = spawn command, args, {stdio: 'inherit'}

    $.watch ["Gulpfile.coffee", "./gulp_config/**"], ->
        spawnChildren()

    $.watch ["#{option.publishDir}/**/*"], ->
        bs.reload({stream: true})

    spawnChildren()

#
# Define default
#
g.task "devel", ["webpack", "sass", "stylus", "jade", "images", "vendor_js", "fonts_copy", "css_copy", "watch"]
g.task "default", ["self-watch"]
