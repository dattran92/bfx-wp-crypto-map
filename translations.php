<?php

class BfxTranslations
{
  public static $translations = [
    'en' => [
      'filter_by' => 'Filter by',
      'search' => 'Search',
      'category' => 'Category',
      'sports_and_leisure' => 'Sports and Leisure',
      'services' => 'Services',
      'food_and_drink' => 'Food and Drink',
      'fashion' => 'Fashion',
      'entertainment' => 'Entertainment',
      'home_and_garden' => 'Home and Garden',
      'electronics' => 'Electronics',
      'retail' => 'Retail',
      'auto_and_moto' => 'Auto and Moto',
      'toys' => 'Toys',
      'other' => 'Other',
      'accepts' => 'Accepts',
      'accepted_payment_methods' => 'Accepted Payment Methods',
      'clear_filters' => 'Clear Filters',
      'store_list' => 'Store List',
      'no_store' => 'No store found'
    ],
    'it' => [
      'filter_by' => 'Filtri',
      'search' => 'Cerca',
      'category' => 'Categoria',
      'sports_and_leisure' => 'Sport e tempo libero',
      'services' => 'Servizi',
      'food_and_drink' => 'Cibo e bevande',
      'fashion' => 'Moda',
      'entertainment' => 'Divertimento',
      'home_and_garden' => 'Casa e giardino',
      'electronics' => 'Elettronica',
      'retail' => 'Commerci',
      'auto_and_moto' => 'Auto e moto',
      'toys' => 'Giocattoli e prodotti per bambini',
      'other' => 'Altro',
      'accepts' => 'Accetta',
      'accepted_payment_methods' => 'Accepted Payment Methods'
    ],
  ];

  public function __construct($lang)
  {
    $this->lang = $lang;
    $this->translations = self::$translations[$lang];
    $this->default_translations = self::$translations['en'];
  }

  public function translate($key)
  {
    return $this->translations[$key] ?? $this->default_translations[$key] ?? $key;
  }
}

?>
