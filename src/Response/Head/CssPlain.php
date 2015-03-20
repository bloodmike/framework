<?php

namespace Framework\Response\Head;

use Framework\Response\HtmlResponse;

/**
 * Вставка plain-стилей в заголовок страницы
 *
 * @author Mikhail Koshkin <bloodmike.ru@gmail.com>
 */
class CssPlain implements StylesheetInterface {
	
	/**
	 * @var string
	 */
	private $content;
	
	/**
	 * @param string $content
	 */
	public function __construct($content) {
		$this->content = $content;
	}
	
	/**
	 * @param HtmlResponse $htmlResponse
	 */
	public function draw(HtmlResponse $htmlResponse) {
		echo '<style>' . $this->content . '</style>';
	}
}
