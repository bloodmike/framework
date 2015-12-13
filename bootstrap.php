<?php

require_once('vendor/autoload.php');

spl_autoload_register(function($className) {
    $explode = explode('\\', $className);
    if ($explode[0] == 'Framework') {
        if ($explode[1] == 'TestCases') {
            unset($explode[0], $explode[1]);
            require_once('tests/' . implode('/', $explode) . '.php');
        } else {
            $explode[0] = 'src';
            require_once(implode('/', $explode) . '.php');
        }
    }
});
