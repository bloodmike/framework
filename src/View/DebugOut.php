<?php

namespace Framework\View;

use Framework\Service\Container;

/**
 * 
 *
 * @author Mikhail Koshkin <bloodmike.ru@gmail.com>
 */
abstract class DebugOut {
	
    /**
     * @return array[]
     */
    private static function getLogEntries(Container $container) {
        return $container->get('datasource_logger')->getLogs();
    }
    
	/**
	 * @param Container $container
	 * 
	 * @return array
	 */
	public static function json(Container $container) {
		$log = compact('_SERVER', '_GET', '_POST', '_COOKIE');
		
		$log['entries'] = [];
		
		$queryTime = 0;
        $logs = self::getLogEntries($container);
		
        foreach($logs as $dbLog){
            $queryTime += $dbLog['time'];
            $time = number_format($dbLog['time'], 5);

            $entry = ['time' => $time, 'content' => $dbLog['content']];

            if ($dbLog['errno'] != '') {
                $entry['errno'] = $dbLog['errno'];
                $entry['error'] = $dbLog['error'];
            }

            $log['entries'][] = $entry;
        }

		
		$log['queriesTime'] = number_format($queryTime, 5);
		$log['memory'] = ceil(memory_get_usage(true) / 1024) . 'KB';
		
        return $log;
	}
	
	/**
	 * @param Container $container
	 * 
	 * @return string
	 */
	public static function html(Container $container) {
        $s = '<style type="text/css">
#TDeb .TDebrow { padding: 5px 0px; border-top: 1px solid #bbb; background: #fff; color: #888; _height: 0; _zoom:1; _overflow: visible;}
#TDeb .TDebrow span { background: #888; color:#fff; margin-right:5px; padding:0px 0px 0px 5px }
#TDeb .TDebrow span.alarm {background:#F00000}
#TDeb .TDebrow span.warning {background:#FF8000}
#TDeb .TDebrow .err { background:#F99; color:#fff; font-weight:bold}</style>';
        $s .= '<noindex><div id="TDeb">';
        //echo '<div class="TDebrow"><span>Classes: </span> ' . implode(', ', $CLASSES_LOADED) . '</div>';

        $s .= '<div class="TDebrow"><span>_SERVER: </span> ' . print_r($_SERVER, true) . '</div>';
        $s .= '<div class="TDebrow"><span>_GET: </span> ' . print_r($_GET, true) . '</div>';
        $s .= '<div class="TDebrow"><span>_POST: </span> ' . print_r($_POST, true) . '</div>';
        $s .= '<div class="TDebrow"><span>_COOKIE: </span> ' . print_r($_COOKIE, true) . '</div>';
        //$s .= '<div class="TDebrow"><span>_SESSION: </span> ' . print_r($_SESSION, true) . '</div>';
        $queryTime = 0;
        
        $logs = self::getLogEntries($container);
        
        foreach($logs as $dbLog){
            $queryTime += $dbLog['time'];
            $time = number_format($dbLog['time'], 5);
            $alarm = ($time >= 0.2) ? 'alarm' : ($time >= 0.05 ? 'warning' : '');
            $s .= '<div class="TDebrow">';
            $s .= '<span class="' . $alarm . '">' . $time . '</span>';
            $s .= '[' . $dbLog['source'] . '] '. $dbLog['content'];
            if ($dbLog['errno'] != '') {
                $s .= "<div class='err'>" . $dbLog['errno'] . " : " . $dbLog['error'] . "</div>";
            }
            $s .= '</div>';
        }

        $s .= '<div class="TDebrow">' . ceil(memory_get_usage(true) / 1024) . ' KB / ' . number_format($queryTime, 5) . ' / ' . count($logs) . ' </div>';
        $s .= '</div></noindex>';
		return $s;
	}
	
}