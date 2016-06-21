<?php

require_once('vendor/autoload.php');

spl_autoload_register(function($className) {
    $explode = explode('\\', $className);
    if ($explode[0] == 'Framework') {
        if ($explode[1] == 'TestCases') {
            unset($explode[0], $explode[1]);
            $fileName = 'tests/' . implode('/', $explode) . '.php';
        } else {
            $explode[0] = 'src';
            $fileName = implode('/', $explode) . '.php';
        }
        if (file_exists($fileName)) {
            require_once($fileName);
        }
    }
});
