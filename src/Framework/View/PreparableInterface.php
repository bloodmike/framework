<?php

namespace Framework\View;

/**
 *
 * @author admin
 */
interface PreparableInterface {
    /**
     * @param string $js
     * @param bool $plain
     * @param string|null $charset
     * 
     * @return static
     */
    public function addJS($js, $plain = false, $charset = null);
}
