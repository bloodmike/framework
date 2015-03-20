<?php

namespace Framework\Response\Head;

use Framework\Response\HtmlResponse;

/**
 * Элемент стиля: plain-стиль, CSS-файл или LESS-файл
 * 
 * @author Mikhail Koshkin <bloodmike.ru@gmail.com>
 */
interface StylesheetInterface {
	
	/**
	 * Отрисовывает теги для использования стиля на странице
	 * 
	 * @param HtmlResponse $htmlResponse объект html-ответа, внутри которого производится отрисовка
	 */
	public function draw(HtmlResponse $htmlResponse);
}
