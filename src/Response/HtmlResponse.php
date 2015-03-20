<?php

namespace Framework\Response;

use Framework\View\PreparableInterface;
use WM\View\Widget\Statics\DebugOut;

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
    private $css = [];
    
    /**
     * @var array
     */
    private $js = [];
    
    /**
     * @var array
     */
    private $less = [];
    
    /**
     * @var array
     */
    private $htmlBefore = [];
    
    /**
     * @var array
     */
    private $htmlAfter = [];
    
    /**
     * @var array
     */
    private $links = [];
    
    /**
     * @var array
     */
    private $meta = [];
    
    /**
     * @var array
     */
    private $metaProperty = [];
        
    /**
     * @var string[]
     */
    private $title = [];
    
    /**
     * @var string
     */
    private $titleSeparator = "";
    
    /**
     * @var string[]
     */
    private $bodyAttrs = [];
    
    /**
     * @var string
     */
    private $customTitle = '';
    
    /**
     * @var string[]
     */
    private $errors = [];
    
    /**
     * @var string[]
     */
    private $messages = [];
	
    /**
     * @var array
     */
    private $customData = [];
    
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
     * @param int $code
     */
    public function __construct($code = 200) {
        $this->statusCode = $code;
    }
    
    /**
     * @inheritdoc
     */
    public function show() {
        http_response_code($this->statusCode);
        
        if (count($this->less) > 0) {
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
                $this->drawLESS();
                $this->drawJS();
                $this->drawCSS();
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
            echo DebugOut::html();
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
        $this->css[] = [$css, $plain];
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
        if ($this->lessCompiled) {
            $this->addCSS($less);
        }
        else {
            $this->less[$less] = true;
        }
        
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
        //if ($plain || !defined('JS_COMPILED') || !JS_COMPILED || count($this->js) == 0) {
            $this->js[] = [$js, $plain, $charset];
        //}
        //else {
        //    $this->js[''] = array(JS_COMPILED_NAME, false, null);
        //}
        return $this;
    }
    
    /**
     * 
     * @param string $rel
     * @param string $href
     * 
     * @return static
     */
    public final function addLink($rel, $href) {
        $this->links[] = [$rel, $href];
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
        var_dump($this->lessCompiled);
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
    private final function drawLink($rel, $href, $type) {
        echo '<link rel="' . $rel . '"' . ($type ? ' type="' . $type . '"' : '') . ' href="' . htmlspecialchars($href) . '" />' . PHP_EOL;
    }
    
    /**
     * 
     */
    private final function drawCSS() {
		$versionSuffix = '';
		
		if ($this->buildNumber != '') {
			$versionSuffix = '?' . $this->buildNumber;
		}
		
        foreach ($this->css as $cssBlock) {
            if ($cssBlock[1]) {
                echo "<style>" . $cssBlock[0] . "</style>" . PHP_EOL;
            }
            else {
                echo '<link rel="stylesheet" href="' . $this->getCSSFilePath($cssBlock[0], $versionSuffix) . '" />' . PHP_EOL;
            }
        }
    }
    
    /**
     * 
     */
    private final function drawJS() {
		
		$versionSuffix = '';
		
		if ($this->buildNumber != '') {
			$versionSuffix = '?' . $this->buildNumber;
		}
		
        foreach ($this->js as $jsBlock) {
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
     */
    private final function drawLESS() {
        foreach (array_keys($this->less) as $lessHref) {
            $this->drawLink("stylesheet/less", '/i/less/' . $lessHref . '.less', 'text/css');
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
            $prefix = "/i/js/";
            $postfix = ".js" . $versionSuffix;
        }
        
        return $prefix . $jsfile . $postfix;
    }
    
    /**
     * 
     * @param string $cssfile
	 * @param string $versionSuffix
     * @return string
     */
    protected function getCSSFilePath($cssfile, $versionSuffix) {
        $prefix = "";
        $postfix = "";
        if (strpos($cssfile, "/") !== 0 && strpos($cssfile, "http") !== 0 && strpos($cssfile, "https") !== 0) {
            $prefix = "/i/css/";
            $postfix = ".css" . $versionSuffix;
        }
        
        return $prefix . $cssfile . $postfix;
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
	 * @return array
	 */
    public function getMessages() {
        return $this->messages;
    }
}