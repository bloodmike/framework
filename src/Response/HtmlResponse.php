<?php

namespace Framework\Response;

use Framework\Response\Head\CssFile;
use Framework\Response\Head\CssPlain;
use Framework\Response\Head\LessFile;
use Framework\Response\Head\StylesheetInterface;
use Framework\Service\Container;
use Framework\View\PreparableInterface;
use Framework\View\DebugOut;

/**
 * Description of HtmlResponse
 *
 * @author mkoshkin
 */
abstract class HtmlResponse implements ResponseInterface, PreparableInterface {
    
    const SESSION_MESSAGES = 'msg';
    
    /**
     * @var int
     */
    private $statusCode;
    
    /**
     * @var array
     */
    private $javascripts = array();
    
    /**
     * @var array
     */
    private $htmlBefore = array();
    
    /**
     * @var array
     */
    private $htmlAfter = array();
    
    /**
     * @var array
     */
    private $links = array();
    
    /**
     * @var array
     */
    private $meta = array();
    
    /**
     * @var array
     */
    private $metaProperty = array();
        
    /**
     * @var string[]
     */
    private $title = array();
    
    /**
     * @var string
     */
    private $titleSeparator = "";
    
    /**
     * @var string[]
     */
    private $bodyAttrs = array();
    
    /**
     * @var string
     */
    private $customTitle = '';
    
    /**
     * @var string[]
     */
    private $errors = array();
    
    /**
     * @var string[]
     */
    private $messages = array();
	
    /**
     * @var array
     */
    private $customData = array();
    
    /**
     * @var bool
     */
    private $drawingStarted = false;
    
    /**
     * @var string
     */
    private $htmlLang = "";
    
    /**
     * @var string
     */
    private $buildNumber = '';
    
    /**
     * @var bool
     */
    private $lessCompiled = false;
    
	/**
	 * @var StylesheetInterface[]
	 */
	private $stylesheets = array();
	
    /**
     * @var string путь, по которому располагаются файлы стилей проекта
     */
    private $stylesheetsPath = '/i/css/';
    
    /**
     * @var string путь, по которому располагаются js-файлы проекта
     */
    private $javascriptsPath = '/i/js/';
    
	/**
	 * @var string 
	 */
	private $rootPrefix  = '';
    
    /**
     * @param int $code
     */
    public function __construct($code = 200) {
        $this->statusCode = $code;
    }
    
    /**
     * @param string $path
     * 
     * @return $this
     */
    public function setStylesheetsPath($path) {
        $this->stylesheetsPath = $path;
        return $this;
    }
    
    /**
     * @return string
     */
    public function getStylesheetsPath() {
        return $this->stylesheetsPath;
    }
    
	/**
	 * @param string $rootPrefix
	 * 
	 * @return $this
	 */
	public function setRootPrefix($rootPrefix) {
		$this->rootPrefix = $rootPrefix;
		return $this;
	}
	
	/**
	 * @return string
	 */
	public function getRootPrefix() {
		return $this->rootPrefix;
	}
    
    /**
     * @param string $path
     * 
     * @return $this
     */
    public function setJavascriptsPath($path) {
        $this->javascriptsPath = $path;
        return $this;
    }
    
    /**
     * @return string
     */
    public function getJavascriptsPath() {
        return $this->javascriptsPath;
    }
    
    /**
     * @inheritdoc
     */
    public function show() {
        http_response_code($this->statusCode);
        
		// :TODO: убрать
        if (!$this->getLessCompiled()) {
            $this->addJS("less = { env: 'development' }", true);
			$this->addJS("less.min");
        }
        
        $this->drawingStarted = true;
        ?><!DOCTYPE html>
        <html<?=$this->htmlLang != "" ? ' lang="' . $this->htmlLang . '"' : ''?>>
            <head>
                <meta charset="utf-8"/>
                <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
                <meta http-equiv="Content-Style-Type" content="text/css" />
                <meta http-equiv="Content-Script-Type" content="text/javascript" />
                <?$this->drawMeta();?>
                <?$this->drawLinks();?>
                <title><?=$this->getTitle();?></title>
                
                <?
                $this->drawStylesheets();
                $this->drawJS();
                ?>
            </head>
            <body<?$this->drawBodyAttrs();?>><?
            $this->drawCustomHTMLBefore();
            $this->draw();
            $this->drawCustomHTMLAfter();
            ?></body>
        </html>
        <?
        if (filter_input(INPUT_COOKIE, self::DEBUG_KEY) == self::DEBUG_VALUE) {
            echo DebugOut::html(Container::$inst);
        } 
    }
    
    /**
     * 
     * @param   string      $css
     * @param   boolean     $plain
     * 
     * @return static
     */
    public function addCSS($css, $plain = false) {
		if ($plain) {
			$this->stylesheets[] = new CssPlain($css);
		}
		else {
			$this->stylesheets[] = new CssFile($css);
		}
		
        return $this;
    }
    
    /**
     * @param string $message
     * 
     * @return static
     */
    public final function addError($message) {
        $this->errors[] = $message;
        return $this;
    }
    
    /**
     * 
     * @param   string  $less
     * 
     * @return static
     */
    public function addLESS($less) {
		$this->stylesheets[] = new LessFile($less);
        return $this;
    }
    
    /**
     * 
     * @param string $html
     * 
     * @return static
     */
    public final function addHTMLAfter($html) {
        $this->htmlAfter[] = (string)$html;
        return $this;
    }
    
    /**
     * 
     * @param string $html
     * 
     * @return static
     */
    public final function addHTMLBefore($html) {
        $this->htmlBefore[] = (string)$html;
        return $this;
    }
    
    /**
     * 
     * @param string    $js
     * @param boolean   $plain
     * @param string    $charset
     * 
     * @return static
     */
    public function addJS($js, $plain = false, $charset = null) {
        $this->javascripts[] = array($js, $plain, $charset);
        return $this;
    }
    
    /**
     * @param string $rel
     * @param string $href
     * 
     * @return static
     */
    public final function addLink($rel, $href) {
        $this->links[] = array($rel, $href);
        return $this;
    }
    
    /**
     * @param string $message
     * 
     * @return static
     */
    public final function addMessage($message) {
        $this->messages[] = $message;
        return $this;
    }
    
    /**
     * 
     * @param string $title
     * 
     * @return static
     */
    public final function addTitle($title) {
        $this->title[] = (string)$title;
        return $this;
    }
    
    /**
     * 
     */
    private final function drawCustomHTMLBefore() {
        echo implode(PHP_EOL, $this->htmlBefore);
    }
    
    /**
     * 
     */
    private final function drawCustomHTMLAfter() {
        echo implode(PHP_EOL, $this->htmlAfter);
    }
    
    /**
     * 
     * @param string $attr_name
     * @param string $attr_value
     */
    public final function setBodyAttr($attr_name, $attr_value) {
        $this->bodyAttrs[$attr_name] = (string)$attr_value;
    }
    
    /**
     * @param string $buildNumber
     * 
     * @return $this
     */
    public function setBuildNumber($buildNumber) {
        $this->buildNumber = $buildNumber;
        return $this;
    }
    
    /**
     * @param bool $lessCompiled
     * 
     * @return $this
     */
    public function setLessCompiled($lessCompiled) {
        $this->lessCompiled = $lessCompiled;
        return $this;
    }
    
    /**
     * 
     */
    abstract public function draw();
    
    /**
     * 
     * @return bool
     */
    public final function drawingStarted() {
        return $this->drawingStarted;
    }
    
    /**
     * 
     * @param string $name
     * @param string $value
     * 
     * @return self
     */
    public function setMeta($name, $value) {
        if ($value !== null) {
            $this->meta[(string)$name] = (string)$value;
		}
        elseif (array_key_exists((string)$name, $this->meta)) {
            unset($this->meta[(string)$name]);
		}
        
        return $this;
    }
    
    /**
     * 
     * @param string $property
     * @param string $value
     * 
     * @return self
     */
    public function setMetaProperty($property, $value) {
        if ($value !== null) {
            $this->metaProperty[(string)$property] = (string)$value;
		}
        elseif (array_key_exists((string)$property, $this->metaProperty)) {
            unset($this->metaProperty[(string)$property]);
		}
        
        return $this;
    }
    
    /**
     * @param int $code
     * 
     * @return $this
     */
    public function setStatusCode($code) {
        $this->statusCode = $code;
        return $this;
    }
    
    /**
     * 
     */
    private final function drawBodyAttrs() {
        $attrsLine = "";
        foreach ($this->bodyAttrs as $attr => $value) {
            if ($value != "") {
                $attrsLine .= " " . $attr . '="' . htmlspecialchars($value) . '"';
			}
        }
            
        echo $attrsLine;
    }
    
    /**
     * 
     */
    private final function drawLinks() {
        foreach ($this->links as $linkBlock) {
            $this->drawLink($linkBlock[0], $linkBlock[1], array_key_exists(2, $linkBlock) ? $linkBlock[2] : '');
        }
    }
    
    /**
     * 
     * @param string $rel
     * @param string $href
     * @param string $type
     */
    public final function drawLink($rel, $href, $type) {
        echo '<link rel="' . $rel . '"' . ($type ? ' type="' . $type . '"' : '') . ' href="' . htmlspecialchars($href) . '" />' . PHP_EOL;
    }
    
    /**
     * Отрисовка всех вложенных в ответ стилей
     */
    protected function  drawStylesheets() {
		foreach ($this->stylesheets as $stylesheet) {
			$stylesheet->draw($this);
		}
    }
    
    /**
     * Отрисовка всех вложенных в ответ жаваскриптов
     */
    protected function drawJS() {
		
		$versionSuffix = '';
		
		if ($this->buildNumber != '') {
			$versionSuffix = '?' . $this->buildNumber;
		}
		
        foreach ($this->javascripts as $jsBlock) {
            if ($jsBlock[1]) {
                echo '<script>' . $jsBlock[0] . '</script>' . PHP_EOL;
            }
            else {
                echo '<script src="' . $this->getJSFilePath($jsBlock[0], $versionSuffix) . '"' . ($jsBlock[2] !== null ? ' charset="' . $jsBlock[2] . '"' : '') . '></script>' . PHP_EOL;
            }
        }
    }
    
    /**
     * 
     * @param string $jsfile
	 * @param string $versionSuffix
     * @return string
     */
    protected function getJSFilePath($jsfile, $versionSuffix) {
        $prefix = "";
        $postfix = "";
        if (strpos($jsfile, "/") !== 0 && strpos($jsfile, "http") !== 0 && strpos($jsfile, "https") !== 0) {
            $prefix = $this->getJavascriptsPath();
            $postfix = ".js" . $versionSuffix;
        }
        
        return $prefix . $jsfile . $postfix;
    }
    
    /**
     * 
     */
    private final function drawMeta() {
        foreach ($this->meta as $name => $value) {
            echo '<meta name="' . $name . '" content="' . htmlspecialchars($value, ENT_COMPAT) . '"/>' . PHP_EOL;
		}
        
        foreach ($this->metaProperty as $property => $value) {
            echo '<meta property="' . $property . '" content="' . htmlspecialchars($value, ENT_COMPAT) . '"/>' . PHP_EOL;
		}
    }
    
    /**
     * @return string
     */
    public final function getTitle() {
        if ($this->titleIsCustom()) {
            return $this->customTitle;
		}
        
        return implode($this->titleSeparator, $this->title);
    }
    
    /**
     * @param string $title
     * 
     * @return static
     */
    public final function setCustomTitle($title) {
        $this->customTitle = (string)$title;
        return $this;
    }
    
    /**
     * @param string $lang 
     * 
     * @return static
     */
    protected final function setHtmlLang($lang) {
        $this->htmlLang = (string)$lang;
        return $this;
    }

	/**
     * @param string $separator
     * 
     * @return static
     */
    public function setSeparator($separator) {
        $this->titleSeparator = (string)$separator;
        return $this;
    }
    
    /**
     * 
     * @return boolean
     */
    public final function titleIsCustom() {
        return $this->customTitle != "";
    }
    
    /**
     * 
     * @param string $field
     * @param mixed $value
     */
    public final function setCustomValue($field, $value) {
        if ($value !== null) {
            $this->customData[$field] = $value;
		}
        elseif (array_key_exists($field, $this->customData)) {
            unset($this->customData[$field]);
		}
    }
    
    /**
     * @param string $field
     * 
     * @return mixed
     */
    public final function getCustomValue($field) {
        if (array_key_exists($field, $this->customData)) {
            return $this->customData[$field];
		}
        
        return null;
    }
    
	/**
	 * @return array
	 */
    public function getErrors() {
        return $this->errors;
    }
    
	/**
	 * @return bool
	 */
	public function getLessCompiled() {
		return $this->lessCompiled;
	} 
	
	/**
	 * @return string
	 */
	public function getBuildNumber() {
		return $this->buildNumber;
	}
	
	/**
	
	/**
	 * @return array
	 */
    public function getMessages() {
        return $this->messages;
    }
}
