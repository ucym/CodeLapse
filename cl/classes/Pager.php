<?php
/**
 * ページネーションクラス
 *
 *
 */
class CL_Pager
{
    private static $instances = array();


    public static function instance($name)
    {
        if (! isset(self::$instances[$name])) {
            throw new Exception("Pager: $nameというインスタンスは初期化されていません。");
        }

        return self::$instances[$name];
    }

    /**
     * ページャーを初期化します。
     *
     * @param int       $currentPage    現在のページ番号（１〜）。
     *      ０が渡された時、強制的に１に変更されます。
     * @param int       $allCount       表示できるアイテム全体の件数
     * @param int       $perPage        １ページに表示する件数
     */
    public static function init($currentPage, $allCount, $perPage, $instance = 'default')
    {
        $currentPage === 0 and $currentPage = 1;

        if ($perPage === 0) {
            throw new Exception('Pager: １ページあたりの件数を0件にすることはできません。');
        }

        self::$instances[$instance] = new self($currentPage, $allCount, $perPage);
    }


    /**
     * 前のページがあるか調べます。
     *
     * @param string    $content        前のページがあるときに表示するコンテンツ
     * @return boolean
     */
    public static function hasPrev($content = null, $instance = 'default')
    {
        return self::instance($instance)->_hasPrev($content);
    }


    /**
     * 次のページがあるか調べます。
     *
     * @param string    $content        次のページがあるときに表示するコンテンツ
     * @return boolean
     */
    public static function hasNext($content = null, $instance = 'default')
    {
        return self::instance($instance)->_hasNext($content);
    }


    /**
     * 全ページ数を取得します。
     *
     * @return int      全ページ数
     */
    public static function pages($instance = 'default')
    {
        return self::instance($instance)->_pages();
    }


    /**
     * 現在のページ番号を中心に、$beforeAfterに指定されたぜんご
     *
     * @param int       $beforeAfter    表示するページ数
     * @param string    $content        次のページがあるときに表示するコンテンツ
     * @return boolean
     */
    public static function relateRange($beforeAfter, $content = null, $instance = 'default')
    {
        return self::instance($instance)->_relateRange($beforeAfter, $content);
    }



    /* instance members */


    private $perPage;
    private $allCount;

    /**
     * @var int １から始まる現在のページ番号
     */
    private $currentPage;


    public function __construct($currentPage, $allCount, $perPage)
    {
        $this->currentPage = $currentPage;
        $this->allCount = $allCount;
        $this->perPage = $perPage;
    }


    private function render($page, $content = null)
    {
        echo preg_replace('/:page/', $page, $content);
    }


    public function _hasPrev($content = null)
    {
        $currentPage = $this->currentPage;
        $hasPrev = $currentPage > 1;

        if ($content !== null and $hasPrev) {
            $this->render($currentPage - 1, $content);
        }

        return $hasPrev;
    }


    public function _hasNext($content = null)
    {
        $currentPage = $this->currentPage;
        $perPage = $this->perPage;
        $allCount = $this->allCount;

        $hasNext = (($currentPage + 1) * $perPage) <= $allCount;

        if ($content !== null and $hasNext) {
            $this->render($currentPage + 1, $content);
        }

        return $hasNext;
    }


    public function _pages()
    {
        return ceil($this->allCount / $this->perPage);
    }


    public function _relateRange($beforeAfter, $content = null)
    {
        $minPage = max(1, $this->currentPage - $beforeAfter) | 0;
        $maxPage = min($this->_pages(), $this->currentPage + $beforeAfter) | 0;
        $range = range($minPage, $maxPage);

        if ($content !== null) {
            foreach ($range as $p) {
                $this->render($p, $content);
            }
        }

        return $range;
    }
}
