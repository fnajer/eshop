<?php

// FRONT CONTROLLER

// 1. Общие настройки
ini_set('display_errors', 1); //Для разработки и
error_reporting(E_ALL);       //в продакшене не нужно.

session_start();

// 2. Подключение файлов системы
define('ROOT', __DIR__);
//define('FOOL_PATH', __FILE__);
require_once(ROOT . '/components/Autoload.php');

// 3. Вызов компонента Router
$router = new Router();
$router->run();

?>