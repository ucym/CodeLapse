<?php
/**
 *
 */
class CL_ArrayWrapper
    implements ArrayAccess, Iterator
{
    const WITH_KEY = 1;

    protected $array = null;

    protected $que = null;

    protected $flag = null;


    public static function wrap(array $array = array())
    {
        return new self($array);
    }

    public static function wrapWithKey(array $array = array())
    {
        return new self($array, self::WITH_KEY);
    }

    //
    // Implements of Iterator
    //


    /**
     * @return mixed
     */
    public function current()
    {
        return current($this->array);
    }


    /**
     * @return scalar
     */
    public function key()
    {
        return key($this->array);
    }


    public function next()
    {
        next($this->array);
    }


    public function rewind()
    {
        reset($this->array);
    }


    /**
     * @return bool
     */
    public function valid()
    {
        return isset($this->array[key($this->array)]);
    }


    //
    // Implements of ArrayAccess
    //

    /**
     * @param mixed $offset
     * @return boolean
     */
    public function  offsetExists($offset)
    {
        return isset($this->array[$offset]);
    }


    /**
     * @param mixed $offset
     * @return mixed
     */
    public function offsetGet($offset)
    {
        return $this->array[$offset];
    }


    /**
     * @param mixed $offset
     * @param mixed $value
     */
    public function offsetSet($offset, $value)
    {
        $this->array[$offset] = $value;
    }


    /**
     * @param mixed $offset
     */
    public function offsetUnset($offset)
    {
        unset($this->array[$offset]);
    }


    //
    // Methods
    //

    public function __construct(array $array = array(), $flag = null)
    {
        $this->array = $array;
        $this->flag = $flag;
        $this->que = array();
    }

    public function changeFlag($flag = null)
    {
        $this->flag = $flag;
        return $this;
    }

    public function get()
    {
        $result = null;

        while (($task = array_shift($this->que)) !== null) {
            switch (count($task)) {
                case 2: $result = $this->{$task[0]}($task[1]); break;
                case 3: $result = $this->{$task[0]}($task[1], $task[2]); break;
                default: $result = call_user_func_array(array($this, array_shift($task)), $task);
            }

            if (! $result instanceof static) break;
        }

        return $result;
    }


    public function toArray()
    {
        $this->get();
        return $this->array;
    }



    public function each($fn)
    {
        $this->que[] = array('applyEach', $fn);
        return $this;
    }

    protected function applyEach($fn)
    {
        reset($this->array);

        if ($this->flag & self::WITH_KEY) {
            foreach ($this->array as $key => $value) {
                $fn($value, $key);
            }
        }
        else {
            foreach ($this->array as $key => $value) {
                $fn($value);
            }
        }

        return $this;
    }



    public function select($fn)
    {
        $this->que[] = array('applySelect', $fn);
        return $this;
    }

    protected function applySelect($fn)
    {
        reset($this->array);
        $newArray = array();

        if ($this->flag & self::WITH_KEY) {
            foreach ($this->array as $key => $value) {
                $fn($value, $key) === true and $newArray[$key] = $value;
            }
        }
        else {
            foreach ($this->array as $key => $value) {
                $fn($value) === true and $newArray[$key] = $value;
            }
        }

        $this->array = $newArray;
        return $this;
    }



    public function exclude($fn)
    {
        $this->que[] = array('applyExclude', $fn);
        return $this;
    }

    protected function applyExclude($fn)
    {
        reset($this->array);
        $newArray = array();

        if ($this->flag & self::WITH_KEY) {
            foreach ($this->array as $key => $value) {
                $fn($value, $key) !== true and $newArray[$key] = $value;
            }
        }
        else {
            foreach ($this->array as $key => $value) {
                $fn($value) !== true and $newArray[$key] = $value;
            }
        }

        $this->array = $newArray;
        return $this;
    }



    public function map($fn)
    {
        $this->que[] = array('applyMap', $fn);
        return $this;
    }

    protected function applyMap($fn)
    {
        reset($this->array);
        $newArray = array();

        if ($this->flag & self::WITH_KEY) {
            foreach ($this->array as $key => $value) {
                $newArray[$key] = $fn($value, $key);
            }
        }
        else {
            foreach ($this->array as $key => $value) {
                $newArray[$key] = $fn($value);
            }
        }

        $this->array = $newArray;
        return $this;
    }



    public function mapKeyVal($fn)
    {
        $this->que[] = array('applyMapKeyVal', $fn);
        return $this;
    }

    protected function applyMapKeyVal(callble $fn)
    {
        reset($this->array);
        $newArray = array();

        if ($this->flag & self::WITH_KEY) {
            foreach ($this->array as $key => $value) {
                $newArray[$key] = $fn($value, $key);
            }
        }
        else {
            foreach ($this->array as $key => $value) {
                $newArray[$key] = $fn($value);
            }
        }

        $this->array = $newArray;
        return $this;
    }



    public function reduce($fn, $memo = null)
    {
        $this->que[] = array('applyReduce', $fn, $memo);
        return $this;
    }

    protected function applyReduce($fn, $memo)
    {
        if ($this->flag & self::WITH_KEY) {
            foreach ($this->array as $key => $value) {
                $memo = $fn($memo, $value, $key);
            }
        }
        else {
            foreach ($this->array as $value) {
                $memo = $fn($memo, $value);
            }
        }

        return $memo;
    }



    protected function values()
    {
        $this->que[] = array('applyValues');
        return $this;
    }

    protected function applyValues()
    {
        $this->array = array_values($this->array);
        return $this;
    }



    public function keys()
    {
        $this->que[] = array('applyKeys');
        return $this;
    }

    protected function applyKeys()
    {
        $this->array = array_keys($this->array);
        return $this;
    }



    public function reverse()
    {
        $this->que[] = array('applyReverse');
        return $this;
    }

    protected function applyReverse($preserveKeys)
    {
        $this->array = array_reverse($this->array, $preserveKeys);
        return $this;
    }
}
