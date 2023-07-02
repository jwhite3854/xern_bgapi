<?php

const DS = '/';
define('APP_ROOT', dirname(__FILE__));

require APP_ROOT . DS .'vendor/autoload.php';

// Run Helium
Helium\Helium::run($_SERVER["REQUEST_URI"]);
