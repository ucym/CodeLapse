<?php
return array(
    'useHtmlEntities' => false, // trueの場合htmlentitiesを、falseの場合htmlspecialcharsを使用する
    'flags'           => ENT_QUOTES | ENT_HTML5,
    'encoding'        => 'UTF-8',
    'noDoubleEncode'  => true, // trueの場合エスケープされた文字列が更にエスケープされないようにする
);
