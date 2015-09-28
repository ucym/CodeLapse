<?php
return array(
    'templateDir'   => null,

    'drivers'       => array(
        'jade'      => 'CodeLapse\\View\\Driver\\Jade',
        'tpl'       => 'CodeLapse\\View\\Driver\\Smarty'
    ),

    'smarty'        => array(
        // string: テンプレートが保存されているディレクトリ
        // 'template_dir' => null,

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
    )
);
