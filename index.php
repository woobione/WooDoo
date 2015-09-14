<?php

// Quick and dirty start - will fix soon
define('MAIN_DIR', dirname(__FILE__) . DIRECTORY_SEPARATOR);
require_once '/Classes/Autoloader.php';

WooDoo::Instance()->Configure(MAIN_DIR . 'Content' . DIRECTORY_SEPARATOR);
WooDoo::Instance()->HandleRequest();