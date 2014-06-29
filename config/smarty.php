<?php
/**
 * Smarty設定ファイル
 * このファイルを複製して適切な値を設定し、
 * アプリケーションの開始時に Config::load で設定の読み込みを行ってください。
 */
return array(
    // string: テンプレートが保存されているディレクトリ
    'template_dir' => null,
    
    // string: コンパイル済みテンプレートを保存するディレクトリ
    'compile_dir' => null,
    
    // string: 設定ファイルが保存されているディレクトリ
    'config_dir' => null,
    
    // string: キャッシュファイルを保存するディレクトリ
    'cache_dir' => null,
    
    // boolean: キャッシュの有効 / 無効
    'caching' => true,
    
    // string: テンプレートファイルの拡張子
    'extension' => 'tpl'
);