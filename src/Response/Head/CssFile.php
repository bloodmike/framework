<?php

namespace Framework\Response\Head;

use Framework\Response\HtmlResponse;

/**
 * Вставка css-файла в заголовок html-страницы
 *
 * @author Mikhail Koshkin <bloodmike.ru@gmail.com>
 */
class CssFile implements StylesheetInterface {
	
	/**
	 * @var string
	 */
	protected $path;
	
	/**
	 * @param string $path путь к файлу
	 */
	public function __construct($path) {
		$this->path = $path;
	}

	/**
	 * @param HtmlResponse $htmlResponse
	 */
	public function draw(HtmlResponse $htmlResponse) {
		$versionSuffix = $htmlResponse->getBuildNumber();
		
		$prefix = "";
        $postfix = "";
        if (strpos($this->path, "/") !== 0 && strpos($this->path, "http") !== 0 && strpos($this->path, "https") !== 0) {
            $prefix = "/i/css/";
            $postfix = ".css" . ($versionSuffix != '' ? '?' . $versionSuffix : '');
		}
		
		echo '<link rel="stylesheet" href="' . $prefix . $this->path . $postfix . '" />' . PHP_EOL;
	}
}
