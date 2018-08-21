<?php

/**
 * Контроллер CartController
 * Корзина
 */
class CartController {

  /**
   * Action для добавления товара в корзину синхронным запросом<br/>
   * (для примера, не используется)
   * @param integer $id <p>id товара</p>
   */
  public function actionAdd($id) {

    // Добавляем товар в корзину
    Cart::addProduct($id);

    // Возвращаем пользователя на страницу, с которой он пришел
    $referer = $_SERVER['HTTP_REFERER'];
    header("Location: $referer");
  }

  /**
   * Action для добавления товара в корзину при помощи асинхронного запроса (ajax)
   * @param integer $id <p>id товара</p>
   */
  public function actionAddAjax($id) {

    // Добавляем товар в корзину и печатаем результат - количество товаров в корзине
    echo Cart::addProduct($id); // Через echo формируется ответ для запроса клиента. Все, что напечатается в ходе скрипта
    return true;
  }

  /**
   * Action для удаления товара из корзины
   * @param integer $id <p>id товара</p>
   */
  public function actionDelete($id) {

    // Удаляем заданный товар из корзины
    Cart::deleteProduct($id);
    // Возвращаем пользователя на страницу корзины
    header("Location: /cart/");
  }

  /**
   * Action для страницы "Корзина"
   */
  public function actionIndex() {

    // Список категорий для левого меню
    $categories = Category::getCategoriesList();

    $productsInCart = false;

    // Получаем данные из корзины: идентификаторы и количество товаров
    $productsInCart = Cart::getProducts();

    // Если в корзине есть товары, получаем полную информацию о товарах для списка
    if ($productsInCart) {

      // Получаем массив только с идентификаторами товаров
      $productsIds = array_keys($productsInCart);

      // Получаем массив с полной информацией о необходимых товарах
      $products = Product::getProductsByIds($productsIds);

      // Получаем общую стоимость товаров
      $totalPrice = Cart::getTotalPrice($products);
    }

    // Подключаем вид
    require_once(ROOT . '/views/cart/index.php');
    return true;
  }

  /**
   * Action для страницы "Оформление покупки"
   */
  public function actionCheckout() {

    // Получаем данные из корзины
    $productsInCart = Cart::getProducts();

    if ($productsInCart == false) {
      // Если товаров нет, отправляем пользователи искать товары на главную
      header("Location: /");
    }

    // Список категорий для левого меню
    $categories = Category::getCategoriesList();

    // Статус успешного оформления заказа
    $result = false;

    // Итоги: находим общую стоимость
    $productsIds = array_keys($productsInCart);
    $products = Product::getProductsByIds($productsIds);
    $totalPrice = Cart::getTotalPrice($products);
    // Количество товаров
    $totalQuantity = Cart::countItems();

    // Поля для формы
    $userName = false;
    $userPhone = false;
    $userComment = false;

    // Пользователь авторизован?
    if (!User::isGuest()) {
      // Если пользователь не гость
      // Получаем информацию о пользователе из БД по id
      $userId = User::checkLogged(); // Получаем id пользователя из сессии
      $user = User::getUserById($userId);
      // Подставляем данные в форму
      $userName = $user['name'];
      
    } else {
      // Если гость, поля формы останутся пустыми
      $userId = false;
    }

    // Форма отправлена?
    if (isset($_POST['submit'])) {
      // Форма отправлена? - Да

      // Считываем данные формы
      $userName = $_POST['userName'];
      $userPhone = $_POST['userPhone'];
      $userComment = $_POST['userComment'];

      // Флаг ошибок
      $errors = false;

      // Валидация полей
      if (!User::checkName($userName)) {
        $errors[] = 'Имя не должно быть короче 2-х символов';
      }

      if (!User::checkPhone($userPhone)) {
        $errors[] = 'Неправильный телефон';
      }

      // Форма заполнена корректно?
      if ($errors == false) {
        // Форма заполнена корректно? - Да

        // Сохраняем заказ в БД
        $result = Order::save($userName, $userPhone, $userComment, $userId, $productsInCart);

        if ($result) {
          // Если заказ успешно сохранен
          // Оповещаем администратора о новом заказе по почте
          $adminEmail = 'fnajer@mail.ru';
          $message = '<a href="http://ishop/admin/orders">Список заказов</a>';
          $subject = 'Новый заказ!';
          mail($adminEmail, $subject, $message);

          // Очищаем корзину
          Cart::clear();
        }
      }
    }

    // Подключаем вид
    require_once(ROOT . '/views/cart/checkout.php');
    return true;
  }
}