<?php

namespace Framework\Response\Head;

/**
 * Вставка less-файла в заголовок страницы
 *
 * @author Mikhail Koshkin <bloodmike.ru@gmail.com>
 */
class LessFile extends CssFile {
	
	/**
	 * @inheritdoc
	 */
	public function draw(\Framework\Response\HtmlResponse $htmlResponse) {
		if ($htmlResponse->getLessCompiled()) {
			parent::draw($htmlResponse);
		}
		else {
			$htmlResponse->drawLink("stylesheet/less", '/i/less/' . $this->path . '.less', 'text/css');
		}
	}
}
