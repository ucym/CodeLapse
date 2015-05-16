<?php

/* - - - - - - - - - - - - - - - - - - - - - - - - - - -

 Title : HTML Output for Php Quick Profiler
 Author : Created by Ryan Campbell
 URL : https://github.com/particletree ( http://www.particletree.com no longer works  )

 Last Updated : August 19, 2012 by Peter Schmalfeldt <manifestinteractive@gmail.com>

 Description : This is a horribly ugly function used to output
 the PQP HTML. This is great because it will just work in your project,
 but it is hard to maintain and read. See the README file for how to use
 the Smarty file we provided with PQP.

- - - - - - - - - - - - - - - - - - - - - - - - - - - - - */

function displayPqp($output, $config = array()) {
    require_once 'presenter/console.php';

    $css = preg_replace('/[\n\r]/', '', file_get_contents(dirname(__FILE__).'/assets/pqp.css'));
    $js = file_get_contents(dirname(__FILE__).'/assets/pqp.js');

    $conf_display = D5_Arr::get($config, 'open', false);
    $conf_tall = D5_Arr::get($config, 'tall', false);

    $return_output = '<style>'.$css.'</style>'
        . '<script type="text/javascript">'.$js.'</script>'
        . '<div style="clear:both;"></div>'
        . '<div id="pqp-container" class="pQp '.($conf_tall ? 'pqp-tallDetails' : '').'"'
        . ' style="display:'.($conf_display ? 'block' : 'none').';position:inherit;">';

    $logCount = count($output['logs']['console']);
    $fileCount = count($output['files']);
    $memoryUsed = $output['memoryTotals']['used'];
    $queryCount = $output['queryTotals']['count'];
    $speedTotal = $output['speedTotals']['total'];

function _pqp_printArray($items, $depth, &$class, &$count)
{
    $output = '';
    foreach($items as $item => $value) {
        $count++;
        $output .='<tr><td class="'.$class.'">';
        if (is_bool($value))
        {
            $output .= '<b class="pqp-var-value">'.($value?'true':'false').'</b>';
        }
        elseif (is_null($value))
        {
            $output .= '<b class="pqp-var-value">null</b>';
        }
        elseif( ! is_array($value) AND ! is_object($value))
        {
            $output .= '<b class="pqp-var-value">'.Security::safeHtml($value).'</b>';
        }
        $output .= str_repeat('&rsaquo;&nbsp;', $depth).Security::safeHtml($item).'</td></tr>';
        if($class == '') $class = 'pqp-alt'; else $class = '';
        is_array($value) and $output .= _pqp_printArray($value, $depth + 1, $class, $count);
        is_object($value) and $output .= _pqp_printArray($value, $depth + 1, $class, $count);
    }
    return $output;
};

$class = '';
$configCount = 0;
$output['configItems'] = _pqp_printarray(Config::get(), 0, $class, $configCount);

$class = '';
$sessionCount = 0;
$output['sessionItems'] = _pqp_printArray(Session::get(null), 0, $class, $sessionCount);

$class = '';
$getCount = 0;
$output['getItems'] = _pqp_printArray(Input::get(), 0, $class, $getCount);

$class = '';
$postCount = 0;
$output['postItems'] = _pqp_printArray(Input::post(), 0, $class, $postCount);

    $return_output .=<<<PQPTABS
<div id="pQp" class="pqp-console">
<table id="pqp-metrics" cellspacing="0">
<tr>
    <td class="pqp-green" onclick="pqp.changeTab('pqp-console');">
        <var>$logCount</var>
        <h4>Console</h4>
    </td>
    <td class="pqp-blue" onclick="pqp.changeTab('pqp-speed');">
        <var>$speedTotal</var>
        <h4>Load Time</h4>
    </td>
    <td class="pqp-purple" onclick="pqp.changeTab('pqp-queries');">
        <var>$queryCount Queries</var>
        <h4>Database</h4>
    </td>
    <td class="pqp-orange" onclick="pqp.changeTab('pqp-memory');">
        <var>$memoryUsed</var>
        <h4>Memory Used</h4>
    </td>
    <td class="pqp-red" onclick="pqp.changeTab('pqp-files');">
        <var>{$fileCount} Files</var>
        <h4>Included</h4>
    </td>
    <td class="pqp-yellow" onclick="pqp.changeTab('pqp-config');">
        <var>{$configCount} Config</var>
        <h4>items loaded</h4>
    </td>
    <td class="pqp-cyan" onclick="pqp.changeTab('pqp-session');">
        <var>{$sessionCount} Session</var>
        <h4>vars loaded</h4>
    </td>
    <td class="pqp-pink" onclick="pqp.changeTab('pqp-get');">
        <var>{$getCount} GET</var>
        <h4>vars loaded</h4>
    </td>
    <td class="pqp-flesh" onclick="pqp.changeTab('pqp-post');">
        <var>{$postCount} POST</var>
        <h4>vars loaded</h4>
    </td>
</tr>
</table>
PQPTABS;

    //
    //-- Render console
    //
    $return_output .='<div id="pqp-console" class="pqp-box">';

    if($logCount ==  0) {
        $return_output .='<h3>This panel has no log items.</h3>';
    }
    else {
        $return_output .='<table class="pqp-side" cellspacing="0">
            <tr>
                <td class="pqp-alt1"><var>'.$output['logs']['logCount'].'</var><h4>Logs</h4></td>
                <td class="pqp-alt2"><var>'.$output['logs']['errorCount'].'</var> <h4>Errors</h4></td>
            </tr>
            <tr>
                <td class="pqp-alt3"><var>'.$output['logs']['memoryCount'].'</var> <h4>Memory</h4></td>
                <td class="pqp-alt4"><var>'.$output['logs']['speedCount'].'</var> <h4>Speed</h4></td>
            </tr>
            </table>
            <div class="pqp-main"><table cellspacing="0">';

            $class = '';
            foreach($output['logs']['console'] as $log) {
                $return_output .='<tr class="pqp-log-'.$log['type'].'">
                    <td class="pqp-type">'.$log['type'].'</td>
                    <td class="'.$class.'">';

                switch ($log['type']) {
                    case 'log'      :
                    case 'info'     :
                        $log['data'] = is_string($log['data']) ? D5_Security::safeHtml($log['data']) : Pqp_Presenter_Console::renderVar($log['data']);
                        $return_output .='<div>'.$log['data'].'</div>';
                        break;

                    case 'memory'   :
                        $return_output .='<div><pre>'.$log['data'].'</pre> <em>'.$log['dataType'].'</em>: '.$log['name'].' </div>';
                        break;

                    case 'speed'    :
                        $return_output .='<div><pre>'.$log['data'].'</pre> <em>'.$log['name'].'</em></div>';
                        break;

                    case 'error'    :
                        $return_output .= Pqp_Presenter_Console::renderError($log);
                }

                $return_output .='</td></tr>';
                if($class == '') $class = 'pqp-alt';
                else $class = '';
            }

            $return_output .='</table></div>';
    }

    $return_output .='</div>';

    //
    //-- Render speed
    //
    $return_output .='<div id="pqp-speed" class="pqp-box">';

    if($output['logs']['speedCount'] ==  0) {
        $return_output .='<h3>This panel has no log items.</h3>';
    }
    else {
        $return_output .='<table class="pqp-side" cellspacing="0">
              <tr><td><var>'.$output['speedTotals']['total'].'</var><h4>Load Time</h4></td></tr>
              <tr><td class="pqp-alt"><var>'.$output['speedTotals']['allowed'].' s</var> <h4>Max Execution Time</h4></td></tr>
             </table>
            <div class="pqp-main"><table cellspacing="0">';

            $class = '';
            foreach($output['logs']['console'] as $log) {
                if($log['type'] == 'speed') {
                    $return_output .='<tr class="pqp-log-'.$log['type'].'">
                    <td class="'.$class.'">';
                    $return_output .='<div><pre>'.$log['data'].'</pre> <em>'.$log['name'].'</em></div>';
                    $return_output .='</td></tr>';
                    if($class == '') $class = 'pqp-alt';
                    else $class = '';
                }
            }

            $return_output .='</table></div>';
    }

    $return_output .='</div>';

    //
    //-- Render Queries
    //
    $return_output .='<div id="pqp-queries" class="pqp-box">';

    if($output['queryTotals']['count'] ==  0) {
        $return_output .='<h3>This panel has no log items.</h3>';
    }
    else {
        $return_output .='<table class="pqp-side" cellspacing="0">
              <tr><td><var>'.$output['queryTotals']['count'].'</var><h4>Total Queries</h4></td></tr>
              <tr><td><var>'.$output['queryTotals']['time'].'</var> <h4>Total Time</h4></td></tr>
              <tr><td class="pqp-alt"><var>'.$output['queryTotals']['duplicates'].'</var> <h4>Duplicates</h4></td></tr>
             </table>
            <div class="pqp-main"><table cellspacing="0">';

            $class = '';
            foreach($output['queries'] as $query) {
                $return_output .='<tr>
                    <td class="'.$class.'">'.$query['sql'];
                $return_output .='<em>';
                $return_output .='Connection name: <b>'.$query['dbname'].'</b><br />Speed: <b>'.$query['time'].'</b>';
                $query['duplicate'] and $return_output .=' &middot; <b>DUPLICATE</b>';
                if(isset($query['explain'])) {
                    $return_output .= '<br />Query analysis:';
                    foreach($query['explain'] as $qe)
                    {
                        isset($qe['select_type']) and $return_output .='<br /> &middot; Query: <b>'.$qe['select_type'].'</b>';
                        empty($qe['table']) or $return_output .=' on <b>'.htmlentities($qe['table']).'</b>';
                        isset($qe['possible_keys']) and $return_output .=' &middot; Possible keys: <b>'.$qe['possible_keys'].'</b>';
                        isset($qe['key']) and $return_output .=' &middot; Key Used: <b>'.$qe['key'].'</b>';
                        isset($qe['type']) and $return_output .=' &middot; Type: <b>'.$qe['type'].'</b>';
                        isset($qe['rows']) and $return_output .=' &middot; Rows: <b>'.$qe['rows'].'</b>';
                        empty($qe['Extra']) or $return_output .=' ('.$qe['Extra'].')';
                        //$return_output .='<br />';
                    }
                }
                if ( ! empty($query['stacktrace']))
                {
                    $return_output .='<br />Call trace for this query:</em>';
                    foreach ($query['stacktrace'] as $st)
                    {
                        $return_output .='<em>File: <b>'.$st['file'].'</b>, line <b>'.$st['line'].'</b></em>';
                    }
                }
                else
                {
                    $return_output .='</em>';
                }
                $return_output .='</td></tr>';
                if($class == '') $class = 'pqp-alt';
                else $class = '';
            }

            $return_output .='</table></div>';
    }

    $return_output .='</div>';

    //
    //-- Render Memory
    //
    $return_output .='<div id="pqp-memory" class="pqp-box">';

    if($output['logs']['memoryCount'] ==  0) {
        $return_output .='<h3>This panel has no log items.</h3>';
    }
    else {
        $return_output .='<table class="pqp-side" cellspacing="0">
              <tr><td><var>'.$output['memoryTotals']['used'].'</var><h4>Used Memory</h4></td></tr>
              <tr><td class="pqp-alt"><var>'.$output['memoryTotals']['total'].'</var> <h4>Total Available</h4></td></tr>
             </table>
            <div class="pqp-main"><table cellspacing="0">';

            $class = '';
            foreach($output['logs']['console'] as $log) {
                if($log['type'] == 'memory') {
                    $return_output .='<tr class="pqp-log-'.$log['type'].'">';
                    $return_output .='<td class="'.$class.'"><b>'.$log['data'].'</b> <em>'.$log['dataType'].'</em>: '.$log['name'].'</td>';
                    $return_output .='</tr>';
                    if($class == '') $class = 'pqp-alt';
                    else $class = '';
                }
            }

            $return_output .='</table></div>';
    }

    $return_output .='</div>';

    //
    //-- Render Included
    //
    $return_output .='<div id="pqp-files" class="pqp-box">';

    if($output['fileTotals']['count'] + $output['pathTotals']['count'] ==  0) {
        $return_output .='<h3>This panel has no log items.</h3>';
    }
    else {
        $return_output .='<table class="pqp-side" cellspacing="0">
                  <tr><td><var>'.count($output['paths']).'</var><h4>Finder Paths</h4></td></tr>
                  <tr><td><var>'.$output['fileTotals']['count'].'</var><h4>Total Files</h4></td></tr>
                <tr><td><var>'.$output['fileTotals']['size'].'</var> <h4>Total Size</h4></td></tr>
                <tr><td class="pqp-alt"><var>'.$output['fileTotals']['largest'].'</var> <h4>Largest</h4></td></tr>
             </table>
            <div class="pqp-main"><table cellspacing="0">';

            $class ='';
            $return_output .='<tr><td><strong style="font-size:120%;">Finder paths:</strong></td></tr>';
            foreach($output['paths'] as $path) {
                $return_output .='<tr><td class="'.$class.'">'.$path.'</td></tr>';
                if($class == '') $class = 'pqp-alt';
                else $class = '';
            }
            $return_output .='<tr><td><strong style="font-size:120%;">Loaded files:</strong></td></tr>';
            foreach($output['files'] as $file) {
                $return_output .='<tr><td class="'.$class.'"><b>'.$file['size'].'</b> '.$file['name'].'</td></tr>';
                if($class == '') $class = 'pqp-alt';
                else $class = '';
            }

            $return_output .='</table></div>';
    }

    $return_output .='</div>';

    //
    //-- Render Configs
    //
    $return_output .='<div id="pqp-config" class="pqp-box">';

    if($configCount ==  0) {
        $return_output .='<h3>This panel has no config items.</h3>';
    }
    else {
        $return_output .='<table class="pqp-side" cellspacing="0">
                <tr><td class="pqp-alt"><var>'.$configCount.'</var> <h4>Configuration items</h4></td></tr>
             </table>
            <div class="pqp-main"><table cellspacing="0">';

            $return_output .= $output['configItems'];

            $return_output .='</table></div>';
    }

    $return_output .='</div>';

    $return_output .='<div id="pqp-session" class="pqp-box">';

    if($sessionCount ==  0) {
        $return_output .='<h3>This panel has no session variables.</h3>';
    }
    else {
        $return_output .='<table class="pqp-side" cellspacing="0">
                <tr><td class="pqp-alt"><var>'.$sessionCount.'</var> <h4>Session variables</h4></td></tr>
             </table>
            <div class="pqp-main"><table cellspacing="0">';

            $return_output .= $output['sessionItems'];

            $return_output .='</table></div>';
    }

    $return_output .='</div>';


    //
    //-- Render $_GET
    //
    $return_output .='<div id="pqp-get" class="pqp-box">';

    if($getCount ==  0) {
        $return_output .='<h3>This panel has no GET variables.</h3>';
    }
    else {
        $return_output .='<table class="pqp-side" cellspacing="0">
                <tr><td class="pqp-alt"><var>'.$getCount.'</var> <h4>GET variables</h4></td></tr>
             </table>
            <div class="pqp-main"><table cellspacing="0">';

            $return_output .= $output['getItems'];

            $return_output .='</table></div>';
    }

    $return_output .='</div>';


    //
    //-- Render $_POST
    //
    $return_output .='<div id="pqp-post" class="pqp-box">';

    if($postCount ==  0) {
        $return_output .='<h3>This panel has no POST variables.</h3>';
    }
    else {
        $return_output .='<table class="pqp-side" cellspacing="0">
                <tr><td class="pqp-alt"><var>'.$postCount.'</var> <h4>POST variables</h4></td></tr>
             </table>
            <div class="pqp-main"><table cellspacing="0">';

            $return_output .= $output['postItems'];

            $return_output .='</table></div>';
    }

    $return_output .='</div>';

$return_output .=<<<FOOTER
    <table id="pqp-footer" cellspacing="0">
        <tr>
            <td class="pqp-credit">
                <a href="https://github.com/particletree" target="_blank">
                Based on
                <strong>PHP</strong>
                <b class="pqp-green">Q</b><b class="pqp-blue">u</b><b class="pqp-purple">i</b><b class="pqp-orange">c</b><b class="pqp-red">k</b>
                Profiler</a></td>
            <td class="pqp-actions">
                <a class="pqp-closeProfiler" href="#" onclick="pqp.closeProfiler();return false" title="Close Code Profiler">Close</a>
                <a class="pqp-heightToggle" href="#" onclick="pqp.toggleHeight();return false" title="Toggle Height">Height</a>
                <a class="pqp-bottomToggle" href="#" onclick="pqp.toggleBottom();return false" title="Toggle Bottom">Bottom</a>
            </td>
        </tr>
    </table>
FOOTER;

    $return_output .='</div></div>'
        . '<div id="pqp-openProfiler" style="display:'.($conf_display ? 'none' : 'block').'">'
        . '<a href="#" onclick="pqp.openProfiler();return false" title="Open Code Profiler">Code Profiler</a></div>';

    return $return_output;
}
