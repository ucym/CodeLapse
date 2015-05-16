<?php
class Pqp_Presenter_Console
{
    public static function renderVar($data, $depth = 3, $options = array())
    {
        $conf_holderClass = D5_Arr::get($options, 'holderClass');

        if (is_null($data)) {
            return '<b class="pqp-var-type">null</b><br>';
        }

        else if (is_bool($data)) {
            return '<b class="pqp-var-type">boolean</b> '.($data ? 'true' : 'false');
        }

        else if (is_string($data)) {
            $blockId = mt_rand();

            $strLen = mb_strlen($data);
            $useCollapse = (mb_strpos($data, PHP_EOL) !== false) or $strLen > 30;

            if ($useCollapse) {
                return '<a href="#" onclick="pqp.toggleDisplayById(\'pqp-array-'.$blockId.'\');return !1"><b class="pqp-var-type">'
                    . 'string('.$strLen.')...</b></a><br>'
                    . '<pre id="pqp-array-'.$blockId.'" class="pqp-string-holder">'
                    . Security::safeHtml($data)
                    . '</pre>';
            }
            else {
                return '<b class="pqp-var-type">string('.$strLen.')</b> "'
                    . Security::safeHtml($data)
                    . '"<br>';
            }
        }
        else if (is_object($data)) {
            $ref = new ReflectionObject($data);
            $propValues = array();
            do {
                $props = $ref->getProperties();
                $values = array();

                foreach ($props as $p) {
                    $p->setAccessible(true);

                    switch (true) {
                        case $p->isPrivate()    : $accessor = "private"; break;
                        case $p->isProtected()  : $accessor = "protected"; break;
                        case $p->isPublic()     : $accessor = "public"; break;
                    }

                    $values[$p->getName().' : '.$accessor] = $p->getValue($data);
                }

                $propValues = array_merge($values, $propValues);
            } while ($ref = $ref->getParentClass());

            $holderId = mt_rand();
            $buffer = '<a href="#" onclick="pqp.toggleDisplayById(\'pqp-array-'.$holderId.'\');return !1">'
                . '<b class="pqp-var-type pqp-var-type-linked">object('.get_class($data).')</b></a><br>'
                . '<ul id="pqp-array-'.$holderId.'" class="pqp-array-holder '.$conf_holderClass.'">';

            if ($depth !== 0) {
                foreach ($propValues as $key => $value) {
                    $buffer .= '<li>'.Security::safeHtml($key).' => '.self::renderVar($value, $depth - 1).'</li>';
                }
            }
            else {
                $buffer .= '<li>...</li>';
            }

            // $buffer .= _pqp_prettyVar($propValues);
            $buffer .= '</ul>';
            return $buffer;
        }
        else if (is_array($data)) {
            $length = count($data);
            $array_id = mt_rand();
            $buffer = '<a href="#" onclick="pqp.toggleDisplayById(\'pqp-array-'.$array_id.'\')"><b class="pqp-var-type pqp-var-type-linked">Array('.$length.')</b></a><br>';
            $buffer .= '<ul id="pqp-array-'.$array_id.'" class="pqp-array-holder '.$conf_holderClass.'">';

            if ($depth !== 0) {
                foreach ($data as $k => $item) {
                    $buffer .= '<li>'.Security::safeHtml($k).' => '.self::renderVar($item, $depth - 1).'</li>';
                }
            }
            else {
                $buffer .= '<li>...</li>';
            }

            $buffer .= '</ul>';

            return $buffer;
        }
    }


    public static function renderLog($logItem)
    {

    }

    public static function renderError($log)
    {
        $buffer = '<div><pre>Error in "'.$log['file'].'" <em>Line '.$log['line'].'</em></pre>'
            . $log['data'].' </div>';

        if (isset($log['stack'])) {
            $holderId = mt_rand();
            $buffer .= '<a href="#" onclick="pqp.toggleDisplayById(\'pqp-trace-'.$holderId.'\');return !1"><b class="pqp-trace-open">Show stacktrace</b></a>';
            $buffer .= '<ul id="pqp-trace-'.$holderId.'" class="pqp-trace-list">';

            $stackNum = count($log['stack']) - 1;
            foreach ($log['stack'] as $i => $stack) {
                $buffer .= '<li>› <em>'.($stackNum - $i).'. Line '.$stack['line'].'</em> : '.$stack['file'].'<br>';

                if (isset($stack['function'])) {
                    $buffer .= '&nbsp;&nbsp;&nbsp;&nbsp;Call <b class="pqp-func-name">';
                    isset($stack['class']) and $buffer .= $stack['class'].$stack['type'];
                    $buffer .= $stack['function'];
                    $buffer .= '</b>';
                }

                isset($stack['args']) and $buffer .= ' with '.self::renderVar($stack['args'], 3, array(
                    'holderClass'   => 'pqp-trace-argList'
                ));
                $buffer .= '</li>';
            }
            $buffer .= '</ul>';
        }

        return $buffer;
    }
}
