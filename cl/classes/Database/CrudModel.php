<?php
namespace CodeLapse\Database;

abstract class CrudModel implements
    ArrayAccess
{
    //----
    //-- 静的メンバ変数・メソッド
    //----

    /**
     * @var string このクラスが対応するテーブル名。
     */
    protected static $_tableName = null;


    /**
     * @var array テーブルの主キー名。
     */
    protected static $_primaryKey = array();


    /**
     * @var array テーブルに定義されているフィールド名の一覧。。
     */
    protected static $_properties = array();


    /**
     * 指定されたクラスの、指定されたプロパティを取得します。
     */
    private static function getClassVars($class, $property)
    {
        $vars = get_class_vars($class);
        return isset($vars[$property]) ? $vars[$property] : null;
    }

    /**
     *
     */
    public static function tableName()
    {
        $class = LateBinding::getCalledClass();
        return self::getClassVars($class, '_tableName');
    }


    /**
     * モデルのプライマリキーのフィールド名を取得します。
     */
    public static function primaryKey()
    {
        $class = LateBinding::getCalledClass();
        return self::getClassVars($class, '_primaryKey');
    }


    /**
     *
     */
    public static function properties()
    {
        $class = LateBinding::getCalledClass();
        return self::getClassVars($class, '_properties');
    }


    /**
     * 主キーからレコードを検索します。
     * 該当するレコードが存在しない時、nullを返します。
     *
     * @ignore
     * @param mixed $pk 主キーの値。
     */
    public static function find($pk = null)
    {
        // @TODO
        /*
        $class      = LateBinding::getCalledClass();
        $tableName  =
            DB::quoteIdentifier(
                call_user_func(array($class, 'tableName')));
        $primaryKey = call_user_func(array($class, 'primaryKey'));

        $fields         = array();
        $placeholders   = array();

        if (!)

        foreach ($primaryKey as $pk) {
            if (! isset($pk[]))
        }


        DB::query(
            'SELECT * '
             . 'FROM '.$tableName
             . 'WHERE ');
        */
        throw new Exception(__CLASS__.'::find はまだ実装されていません。');
    }


    //----
    //-- ArrayAccessの実装メソッド
    //----

    /**
     * オフセットが存在するかどうかを返します。
     *
     * @see http://php.net/manual/ja/arrayaccess.offsetexists.php ArrayAccess::offsetExists
     * @param mixed $offset 調べたいオフセット
     * @return boolean
     */
    public function offsetExists($offset)
    {
        return isset($this->_data[$offset]);
    }


    /**
     * 指定したオフセットの値を返します。
     *
     * @see http://php.net/manual/ja/arrayaccess.offsetget.php ArrayAccess::offsetGet
     * @param mixed $offset 取得したいオフセット
     * @return mixed
     */
    public function offsetGet($offset)
    {
        return $this->get($offset);
    }


    /**
     * 指定したオフセットに値を代入します。
     *
     * @param mixed $offset 値を代入したいオフセット
     * @param mixed $value 設定したい値
     */
    public function offsetSet($offset, $value)
    {
        $this->set($offset, $value);
    }


    /**
     * オフセットの設定を解除します。
     *
     * @param mixed $offset 設定解除したいオフセット。
     */
    public function offsetUnset($offset)
    {
        unset($this->_data[$offset]);
    }


    //----
    //-- オブジェクトプロパティ・メソッド
    //----

    /**
     * @var array データベースに保存されている値を保持します。
     */
    protected $_originalData = array();


    /**
     * @var array フィールドに対応する値が設定されます。
     */
    protected $_data = array();


    /**
     * @var string このインスタンスの読み書きに利用するコネクション名
     */
    protected $_connection;


    /**
     * @var boolean モデルがデータベースに保存されているか示します。
     */
    protected $_isNew = true;


    /**
     *
     *
     */
    public function __construct(array $data = array(), $isNew = true)
    {
        $this->set($data);
        $this->_isNew = $isNew;
    }


    /**
     * アクセス不能プロパティへデータを書き込む際に呼び出されます。
     *
     * @param string $name 値を設定するプロパティ
     * @param mixed $value 設定する値
     */
    public function __set($name, $value)
    {
        $this->set($name, $value);
    }


    /**
     * アクセス不能プロパティからデータを読み込む際に呼び出されます。
     *
     * @see http://php.net/manual/ja/language.oop5.overloading.php#object.get
     * @param string $name 取得したいプロパティ
     */
    public function __get($name)
    {
        return $this->get($name);
    }


    /**
     * モデルのフィールドに値を設定します。
     *
     * @param string|array $name 値を設定するフィールド名。
     *      もしくは、フィールド名と設定する値の連想配列。
     * @param mixed $value 設定する値
     * @return static 現在のインスタンス
     */
    public function set($name, $value = null)
    {
        if (func_num_args() === 1 and ! is_array($name)) {
            throw new InvalidArgumentException('setメソッドには必ず設定する値を渡す必要があります。');
        }

        // 第１引数が配列であれば、フィールドごとに分割してメソッドをコールします。
        if (is_array($name)) {
            foreach ($name as $k => $v) {
                $this->set($k, $v);
            }

            return $this;
        }

        $class = LateBinding::getCalledClass();
        $fields = call_user_func(array($class, 'properties'));

        if (!in_array($name, $fields)) {
            throw new OutOfBoundException("$class::_$properties に フィールド $name は定義されていません。");
        }

        $this->_data[$name] = $value;

        return $this;
    }


    /**
     * モデルのフィールド値を取得します。
     *
     * @param string $name フィールド名
     * @return mixed
     */
    public function get($name)
    {
        $class = LateBinding::getCalledClass();
        $fields = call_user_func(array($class, 'properties'));

        if (! in_array($name, $fields)) {
            throw new OutOfBoundException("$class::_$properties に フィールド $name は定義されていません。");
        }

        return isset($this->_data[$name]) ? $this->_data[$name] : null;
    }


    /**
     * インスタンスが新規生成されたものか調べます。
     *
     * @return boolean
     */
    public function isNew()
    {
        return $this->_isNew;
    }


    /**
     *
     */
    public function isChanged()
    {
        // @TODO
    }


    /**
     * モデルの変更点を保存します。
     *
     * @param string $connection 利用するデータベースコネクション名
     * @return self
     */
    public function save($connection = null)
    {
        $class = LateBinding::getCalledClass();
        $primaryKey = call_user_func(array($class, 'primaryKey'));
        $fields = call_user_func(array($class, 'properties'));

        // if ($connection !== null and $this->_connection === null) {
        //     $this->_connection = $connection;
        // }

        $this->isNew() ? $this->create() : $this->update();

        //$result = DB::query($sql, $this->_data, );

        if ($this->isNew()) {
            $this->_originalData = $this->_data;
        }

        return true;
    }


    /**
     *
     *
     */
    protected function create()
    {
        //-- モデル定義情報を取得
        $class      = LateBinding::getCalledClass();
        $primaryKey = call_user_func(array($class, 'primaryKey'));
        $fields     = call_user_func(array($class, 'properties'));
        $tableName  = call_user_func(array($class, 'tableName'));

        $quotedFields   = array();
        $placeholders   = array();
        $values         = array();


        //-- SQLに埋め込む値を構築
        foreach ($fields as $f) {
            $quotedFields[] = DB::quoteIdentifier($f);
            $placeholders[] = ':' . $f;
            $values[":$f"] = $this->get($f);
        }


        //-- SQLを構築
        $sql = 'INSERT INTO ' . DB::quoteIdentifier($tableName) . ' (';
        $sql .= implode(', ', $quotedFields);
        $sql .= ') VALUES (';
        $sql .= implode(', ', $placeholders);
        $sql .= ')';

        //-- 実行
        $result = DB::query($sql, $values);

        if ($result === false) {
            throw new DBException(DB::errorMessage(), DB::errorCode());
        }

        //-- 保存に成功したら主キー値を取得する。
        if (count($primaryKey) === 1 and $result !== false) {
            $id = DB::lastInsertId();

            $id !== false and $this->_data[$primaryKey[0]] = $id;
        }
    }

    protected function update()
    {
        $class = LateBinding::getCalledClass();
        $primaryKey = call_user_func(array($class, 'primaryKey'));
        $fields = call_user_func(array($class, 'properties'));

        $field_holderPair = array();
        foreach ($fields as $f) {
            if (in_array($f, $primaryKey)) continue;
            $field_holderPair[] = sprintf('%s = :%s', DB::quoteIdentifier($f), $f);
        }

        $condition = array();
        foreach ($primaryKey as $pk) {
            $condition[] = sprintf('%s = :%s', DB::quoteIdentifier($pk), $pk);
        }

        $sql = 'UPDATE ' . DB::quoteIdentifier($this->tableName()) . ' ';
        $sql .= 'SET ';
        $sql .= implode(',', $field_holderPair) . ' ';
        $sql .= 'WHERE ';
        $sql .= implode(' AND ', $condition);

        $result = DB::query($sql, $this->_data);

        if ($result === false or $result !== 1) {}
    }
}
