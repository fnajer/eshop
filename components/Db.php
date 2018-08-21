<?php

/**
 * Класс Db
 * Компонент для работы с базой данных
 */
class Db {

  /**
   * Устанавливает соединение с базой данных
   * @return \PDO <p>Объект класса PDO для работы с БД</p>
   */
  public static function getConnection() {

    // Получаем параметры подключения из файла
    $paramsPath = ROOT . '/config/db_params.php';
    $params = include($paramsPath);

    // Устанавливаем соединение
    $db = new PDO("mysql:host={$params['host']};
                        dbname={$params['dbname']}",
                        $params['user'], 
                        $params['password']);

    // Задаем кодировку
    $db->exec("set names utf8"); // Не обязательно. И так робит.

    return $db;
  }
}


?>