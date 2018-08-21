<?php

/**
 * Класс User - модель для работы с пользователями
 */
class User {

  /**
   * Регистрация пользователя 
   * @param string $name <p>Имя</p>
   * @param string $email <p>E-mail</p>
   * @param string $password <p>Пароль</p>
   * @return boolean <p>Результат выполнения метода</p>
   */
  public static function register($name, $email, $password) {

    $db = Db::getConnection();

    $sql = "INSERT INTO user (name, email, password) "
            . "VALUES (:name, :email, :password)"; //вместо прямого внедрения переменной - исп. плейсхолдер

    $result = $db->prepare($sql); //в целях повышения безопасности
    $result->bindParam(':name', $name, PDO::PARAM_STR); //для избежания sql-инъекций
    $result->bindParam(':email', $email, PDO::PARAM_STR);
    $result->bindParam(':password', $password, PDO::PARAM_STR);

    return $result->execute(); //возвращаем результат, true или false;
  }

  /**
   * Редактирование данных пользователя
   * @param integer $id <p>id пользователя</p>
   * @param string $name <p>Имя</p>
   * @param string $password <p>Пароль</p>
   * @return boolean <p>Результат выполнения метода</p>
   */
  public static function edit($id, $name, $password) {

    $db = Db::getConnection();

    $sql = "UPDATE user 
        SET name = :name, password = :password
        WHERE id = :id";

    $result = $db->prepare($sql);
    $result->bindParam(':id', $id, PDO::PARAM_INT);
    $result->bindParam(':name', $name, PDO::PARAM_STR);
    $result->bindParam(':password', $password, PDO::PARAM_STR);

    return $result->execute(); //возвращаем результат, true или false;
  }

  /**
   * Проверяет имя: не меньше, чем 2 символа
   * @param string $name <p>Имя</p>
   * @return boolean <p>Результат выполнения метода</p>
   */
  public static function checkName($name) {
    
    if (strlen($name) >= 2) {
      return true;
    }
    return false;
  }

  /**
   * Проверяет имя: не меньше, чем 6 символов
   * @param string $password <p>Пароль</p>
   * @return boolean <p>Результат выполнения метода</p>
   */
  public static function checkPassword($password) {
    
    if (strlen($password) >= 6) {
      return true;
    }
    return false;
  }

  /**
   * Проверяет телефон: не меньше, чем 10 символов
   * @param string $phone <p>Телефон</p>
   * @return boolean <p>Результат выполнения метода</p>
   */
  public static function checkPhone($phone) {
    
    if (strlen($phone) >= 10) {
      return true;
    }
    return false;
  }

  /**
   * Проверяет email
   * @param string $email <p>E-mail</p>
   * @return boolean <p>Результат выполнения метода</p>
   */
  public static function checkEmail($email) {
    
    if (filter_var($email, FILTER_VALIDATE_EMAIL)) {
      return true;
    }
    return false;
  }

  /**
   * Проверяет не занят ли email другим пользователем
   * @param type $email <p>E-mail</p>
   * @return boolean <p>Результат выполнения метода</p>
   */
  public static function checkEmailExists($email) {

    $db = Db::getConnection();

    $sql = "SELECT COUNT(*) "
            . "FROM user "
            . "WHERE email = :email"; //вместо прямого внедрения переменной - исп. плейсхолдер

    // Получение результатов. Используется подготовленный запрос
    $result = $db->prepare($sql); //в целях повышения безопасности
    $result->bindParam(':email', $email, PDO::PARAM_STR); //для избежания sql-инъекций
    $result->execute();

    if ($result->fetchColumn())
      return true;
    return false;

  }

  /**
   * Проверяем существует ли пользователь с заданными $email и $password
   * @param string $email
   * @param string $password
   * @return mixed : integer user id or false
   */
  public static function checkUserData($email, $password) {

    $db = Db::getConnection();

    $sql = "SELECT * FROM user WHERE email = :email AND "
            . "password = :password"; //вместо прямого внедрения переменной - исп. плейсхолдер

    $result = $db->prepare($sql);
    $result->bindParam(':email', $email, PDO::PARAM_STR); 
    $result->bindParam(':password', $password, PDO::PARAM_STR);
    $result->execute();

    // Обращаемся к записи
    $user = $result->fetch();
    
    if ($user) {
      // Если запись существует, возвращаем id пользователя
      return $user['id'];
    }

    return false;
  }

  /**
   * Запоминаем пользователя
   * @param integer $userId <p>id пользователя</p>
   */
  public static function auth($userId) {
    // Записываем идентификатор пользователя в сессию
    $_SESSION['user'] = $userId;
  }


  /**
   * Возвращает идентификатор пользователя, если он авторизирован.<br/>
   * Иначе перенаправляет на страницу входа
   * @return string <p>Идентификатор пользователя</p>
   */
  public static function checkLogged() {

    // Если сессия есть, вернем идентификатор пользователя
    if (isset($_SESSION['user'])) {
      return $_SESSION['user'];
    }

    header("Location: /user/login/"); // параметр Location для редиректа и значение - роут
  }

  /**
   * Проверяет является ли пользователь гостем
   * @return boolean <p>Результат выполнения метода</p>
   */
  public static function isGuest() {
    
    if (isset($_SESSION['user'])) {
      return false;
    }

    return true;
  }

  /**
   * Возвращает пользователя с указанным id
   * @param integer $id <p>id пользователя</p>
   * @return array <p>Массив с информацией о пользователе</p>
   */
  public static function getUserById($id) {

      $db = Db::getConnection();

      $sql = 'SELECT * FROM user WHERE id = :id';

      // Получение и возврат результатов. Используется подготовленный запрос
      $result = $db->prepare($sql);
      $result->bindParam(':id', $id, PDO::PARAM_STR); 

      // Указываем, что хотим получить данные лишь в виде ассоц. массива
      $result->setFetchMode(PDO::FETCH_ASSOC);
      $result->execute();

      return $result->fetch();
    
  }
}

?>