<?php

  /**
   * Класс Cart
   * Компонент для работы корзиной
   */
  class Cart {

    /**
     * Добавление товара в корзину (сессию)
     * @param int $id <p>id товара</p>
     * @return integer <p>Количество товаров в корзине</p>
     */
    public static function addProduct($id) {

      // Приводим $id к типу integer
      $id = intval($id);

      // Пустой массив для товаров в корзине
      $productsInCart = array();

      // Если в корзине уже есть товары (они хранятся в сессии)
      if (isset($_SESSION['products'])) {
        // То заполним наш массив товарами
        $productsInCart = $_SESSION['products'];
      }

      // Если товар есть в корзине, но был добавлен ещё раз, то увеличим количество
      if (array_key_exists($id, $productsInCart)) {
        $productsInCart[$id]++;
      } else {
        // Добавляем новый товар в корзину
        $productsInCart[$id] = 1;
      }

      $_SESSION['products'] = $productsInCart;

      return self::countItems();
    }

    /**
     * Подсчёт количества товаров в корзине (в сессии)
     * @return int <p>Количество товаров в корзине</p>
     */
    public static function countItems() {

      // Проверка наличия товаров в корзине
      if (isset($_SESSION['products'])) {
        // Если массив с товарами есть
        // Подсчитаем и вернем их количество
        $count = 0;
        foreach ($_SESSION['products'] as $id => $quantity) {
          $count = $count + $quantity;
        }
        return $count;
      } else {
        // Если товаров нет, вернем 0
        return 0;
      }
    }

    /**
     * Удаляет товар с указанным id из корзины
     * @param integer $id <p>id товара</p>
     */
    public static function deleteProduct($id) {

      // Приводим $id к типу integer
      $id = intval($id); // Не знаю зачем, но все же. Как в addProduct

      // Получаем массив с идентификаторами и количеством товаров в корзине
      $productsInCart = self::getProducts($id);

      // Удаляем из корзины элемент с указанным id
      unset($productsInCart[$id]);

      // Записываем массив с удаленным товаром обратно в сессию
      $_SESSION['products'] = $productsInCart;
    }

    /**
     * Возвращает массив с идентификаторами и количеством товаров в корзине<br/>
     * Если товаров нет, возвращает false;
     * @return mixed: boolean or array
     */
    public static function getProducts() {

      if (isset($_SESSION['products'])) {
        return $_SESSION['products'];
      }

      return false;
    }

    /**
     * Получаем общую стоимость переданных товаров
     * @param array $products <p>Массив с информацией о товарах</p>
     * @return integer <p>Общая стоимость</p>
     */
    public static function getTotalPrice($products) {

      // Получаем массив с идентификаторами и количеством товаров в корзине
      $productsInCart = self::getProducts();

      // Подсчитываем общую стоимость
      $total = 0;
      if ($productsInCart) {
        // Если в корзине не пусто
        // Проходим по переданному в метод массиву товаров
        foreach ($products as $item) {
          // Находим общую стоимость: цена товара * количество товара
          $total += $item['price'] * $productsInCart[$item['id']];
        }
        
      } 

      return $total;
    }

    /**
     * Очищает корзину
     */
    public static function clear() {

      if (isset($_SESSION['products'])) {
        unset($_SESSION['products']);
      }

    }

  }

?>