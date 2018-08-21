<?php

/**
 * Класс Router
 * Компонент для работы с маршрутами
 */
class Router {

  /**
   * Свойство для хранения массива роутов
   * @var array 
   */
  private $routes;

  /**
   * Конструктор
   */
  public function __construct() {
    // Путь к файлу с роутами
    $routesPath = ROOT . '/config/routes.php';

    // Получаем массив роутов из файла
    $this->routes = include($routesPath);
  }

  /**
   * Returns request string - Возвращает строку запроса
   * @return string
   */
  private function getURI() {
    if (!empty($_SERVER['REQUEST_URI'])) {
      return trim($_SERVER['REQUEST_URI'], '/');
    }
  }

  /**
   * Метод для обработки запроса
   */
  public function run() {

    // Получаем строку запроса
    $uri = $this->getURI();

    // Проверить наличие такого запроса в массиве маршрутов (routes.php)
    foreach ($this->routes as $uriPattern => $path) {
      
      //Сравниваем $urlPattern и $uri
      if (preg_match("~^$uriPattern$~", $uri)) { # тильда вместо слешей, т.к. они могут быть в строке запроса

        // Получаем внутренний путь из внешнего согласно правилу
        $internalRoute = preg_replace("~$uriPattern~", $path, $uri);

        // Определим какой контроллер и action(параметры) обрабатывают запрос

        $segments = explode('/', $internalRoute);

        $controllerName = array_shift($segments) . 'Controller';
        $controllerName = ucfirst($controllerName);

        $actionName = 'action' . ucfirst(array_shift($segments));
        
        $parameters = $segments;

        // Подключить файл класса-контроллера
        $controllerFile = ROOT . '/controllers/' .
          $controllerName . '.php';

        if (file_exists($controllerFile)) {
          include_once($controllerFile);
        }

        // Создать объект, вызвать action-метод
        $controllerObject = new $controllerName;

        /**
         * Вызываем необходимый метод ($actionName) у определенного 
         * класса ($controllerObject) с заданными ($parameters) параметрами
         */
        $result = call_user_func_array(array($controllerObject, $actionName), $parameters);

        // Если метод контроллера успешно вызван, завершаем работу роутера
        if ($result != null) {
          break;
        }

      }
    }
  }

}

?>