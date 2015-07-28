#!/usr/bin/env php
<?php
// if [ ! -e "release" ]; then
//      mkdir release
// fi
//
// zip -rqX ./release/CodeLapse.zip cl/ -x cl/test/\*
// zip -rqX ./release/CodeLapse_document.zip api_documents/

include dirname(__FILE__).'/../cl/bs.php';

$excludePatterns = array(
    '/\\/\\.gitkeep$/',
    '/\\/.DS_Store$/',
    '/\\/(test|tests)\\/.*$/',
    '/phpunit.*\\.xml$/',
    '/composer\\..*$/',
    '/\\/\\..*$/'
);


// Make `release` dir
!file_exists('./release') and mkdir('release');

// Make zip
$zip = new ZipArchive();
$result = $zip->open('./release/_CodeLapse.zip', ZipArchive::CREATE);
$result !== true and processError($result);

// List up package files
$rdi = new RecursiveDirectoryIterator('cl/',
    FilesystemIterator::SKIP_DOTS
    | FilesystemIterator::KEY_AS_PATHNAME
    | FilesystemIterator::CURRENT_AS_FILEINFO);
$itr = new RecursiveIteratorIterator($rdi);

// Exclude exclusion files
$entries = Arr::wrapWithKey(iterator_to_array($itr))
    ->exclude(function ($info, $path) use ($excludePatterns) {
        return Arr::wrap($excludePatterns)
            ->reduce(function ($nomatch, $pattern) use ($path) {
                return $nomatch or preg_match($pattern, $path);
            }, false)
            ->get();
    })
    ->keys()
    ->each(function ($entry) use ($zip){
        $zip->addFile($entry);
    })
    ->get();

// End
echo "End.\n";
$zip->close();


// Error processor
function processError($code) {
    switch($code){
        case true:
            break;

        case ZipArchive::ER_EXISTS:
            die("File already exists.\n");
            break;

        case ZipArchive::ER_INCONS:
            die("Zip archive inconsistent.\n");
            break;

        case ZipArchive::ER_MEMORY:
            die("Malloc failure.\n");
            break;

        case ZipArchive::ER_NOENT:
            die("No such file.\n");
            break;

        case ZipArchive::ER_NOZIP:
            die("Not a zip archive.\n");
            break;

        case ZipArchive::ER_OPEN:
            die("Can't open file.\n");
            break;

        case ZipArchive::ER_READ:
            die("Read error.\n");
            break;

        case ZipArchive::ER_SEEK:
            die("Seek error.\n");
            break;

        default:
            die("Unknow (Code $code)\n");
            break;
    }
}
