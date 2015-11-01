<?php
/**
 * 
 */
class CL_LateBinding
{
    /**
     * @see http://stackoverflow.com/a/24571864 This is nice implement of "get_called_class"!
     */
    public static function getCalledClass()
    {
        try {
            $fl = null;
            $i  = 0;
            $d  = 1;
            $result = false;
            $cuf = array('call_user_func', 'call_user_func_array');
            $bt = debug_backtrace();

            /*
                echo '<br><br>';
                echo '<pre>';
                print_r($bt);
                echo '</pre>';
            */

            if (isset($bt[1]['file'])) {
                if ($fl == $bt[1]['file'] . $bt[1]['line']) {
                    $i++;
                }
                else {
                    $i = 0;
                    $fl = $bt[1]['file'] . $bt[1]['line'];
                }
            }

            if ($bt[1]['type'] == '::') {

                if (isset($bt[1]['file'])) {
                    $lines = file($bt[1]['file']);
                    preg_match_all('/([a-zA-Z0-9\_]+)::' . $bt[1]['function'] . '/', $lines[$bt[1]['line'] - 1], $matches);
                    $result = $matches[1][$i];
                }
                else if (isset($bt[2]['function']) and in_array($bt[2]['function'], $cuf)) {
                    // Case of 'call_user_func'

                    // get first arguments
                    $call = $bt[2]['args'][0];

                    // if not classMethod call
                    if (! is_array($call) or count($call) !== 2) {
                        $result = false;
                    }
                    else {
                        $result = is_object($call[0]) ? get_class($call[0]) : $call[0];
                    }
                }

            }
            else if ($bt[$d]['type'] == '->') {
                $result = get_class($bt[$d]['object']);
            }

            return $result;
        }
        catch (Exception $e) {
        }
    }
}
