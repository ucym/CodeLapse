<?php
namespace CodeLapse;

use ArrayAccess;
use Iterator;

/**
 *
 */
class ArrayWrapper
    implements ArrayAccess, Iterator
{
    const WITH_KEY = 1;

    protected $array = null;

    protected $que = null;

    protected $flag = null;


    public static function wrap(array $array = array())
    {
        return new static($array);
    }

    public static function wrapWithKey(array $array = array())
    {
        return new static($array, static::WITH_KEY);
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



    public function each(callable $fn)
    {
        $this->que[] = array('applyEach', $fn);
        return $this;
    }

    protected function applyEach(callable $fn)
    {
        reset($this->array);

        if ($this->flag & static::WITH_KEY) {
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



    public function select(callable $fn)
    {
        $this->que[] = array('applySelect', $fn);
        return $this;
    }

    protected function applySelect(callable $fn)
    {
        reset($this->array);
        $newArray = array();

        if ($this->flag & static::WITH_KEY) {
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



    public function exclude(callable $fn)
    {
        $this->que[] = array('applyExclude', $fn);
        return $this;
    }

    protected function applyExclude(callable $fn)
    {
        reset($this->array);
        $newArray = array();

        if ($this->flag & static::WITH_KEY) {
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



    public function map(callable $fn)
    {
        $this->que[] = array('applyMap', $fn);
        return $this;
    }

    protected function applyMap(callable $fn)
    {
        reset($this->array);
        $newArray = array();

        if ($this->flag & static::WITH_KEY) {
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



    public function mapKeyVal(callable $fn)
    {
        $this->que[] = array('applyMapKeyVal', $fn);
        return $this;
    }

    protected function applyMapKeyVal(callble $fn)
    {
        reset($this->array);
        $newArray = array();

        if ($this->flag & static::WITH_KEY) {
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



    public function reduce(callable $fn, $memo = null)
    {
        $this->que[] = array('applyReduce', $fn, $memo);
        return $this;
    }

    public function applyReduce(callable $fn, $memo)
    {
        if ($this->flag & static::WITH_KEY) {
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



    public function values()
    {
        $this->que[] = array('applyValues');
        return $this;
    }

    public function applyValues()
    {
        $this->array = array_values($this->array);
        return $this;
    }



    public function keys()
    {
        $this->que[] = array('applyKeys');
        return $this;
    }

    public function applyKeys()
    {
        $this->array = array_keys($this->array);
        return $this;
    }



    public function reverse()
    {
        $this->que[] = array('applyReverse');
        return $this;
    }

    public function applyReverse($preserveKeys)
    {
        $this->array = array_reverse($this->array, $preserveKeys);
        return $this;
    }
}
