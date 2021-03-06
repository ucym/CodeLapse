<?php
return array(
    /*
    // 接続名を変えることで複数の接続先を設定できます。
    '接続名' => array(
        'user'      => 'ユーザー名',
        'password'  => 'パスワード',
        'host'      => 'ホスト名',

        // ここ以下は設定しなくてもよいですが
        // 設定されていた場合、自動的に"use"と"set name"クエリが実行されます。
        'database'  => '(任意)データベース名',
        'charset'   => ''
    ),
    */
    'default' => array(
        'user'      => 'user',
        'password'  => 'password',
        'host'      => 'hostname',

        // ここ以下は設定しなくてもよいですが
        // 設定されていた場合、自動的に"use"と"set name"クエリが実行されます。
        'database'  => '',
        'charset'   => ''
    )
);
