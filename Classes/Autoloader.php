<?php

define('AUTOLOAD_DIR', dirname(__FILE__) . DIRECTORY_SEPARATOR);

function __autoload($className) {
    include AUTOLOAD_DIR . $className . '.php';
}