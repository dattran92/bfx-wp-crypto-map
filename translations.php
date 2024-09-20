<?php

class BfxTranslations
{
  public static $translations = [
    'en' => [
      'filter_by' => 'Filter by',
      'search' => 'Search',
      'category' => 'Category',
      'accepts' => 'Accepts',
      'accepted_payment_methods' => 'Accepted Payment Methods',
      'clear_filters' => 'Clear Filters',
      'store_list' => 'Store List',
      'no_store' => 'No store found',
      'restaurant' => 'Restaurant',
      'take_away' => 'Take away',
      'boutique' => 'Boutique',
      'hair_stylist' => 'Hair stylist',
      'bar_and_cafe' => 'Bar and cafè',
      'electronics' => 'Electronics',
      'entertainment' => 'Entertainment',
      'sports_and_leisure' => 'Sports and leisure',
      'jewelry' => 'Jewelry',
      'pharmacy' => 'Pharmacy',
      'kiosk' => 'Kiosk',
      'flower_shop' => 'Flower shop',
      'service_provider' => 'Service provider',
      'book_shop' => 'Book shop',
      'optician' => 'Optician',
      'art_gallery' => 'Art gallery',
      'stationary_shop' => 'Stationary shop',
      'beauty_salon' => 'Beauty salon',
      'education' => 'Education',
      'grocery_store' => 'Grocery store',
      'hotel' => 'Hotel',
      'taxi' => 'Taxi',
      'auto_and_moto' => 'Auto and motorcycle',
      'retail' => 'Retail',
      'home_and_garden' => 'Home and garden',
      'local_food_products' => 'Local food products',
      'services' => 'Services',
      'food_and_drink' => 'Food and Drink',
      'fashion' => 'Fashion',
      'toys' => 'Toys',
      'other' => 'Other'
    ],
    'it' => [
      'filter_by' => 'Filtri',
      'search' => 'Cerca',
      'category' => 'Categoria',
      'accepts' => 'Accetta',
      'accepted_payment_methods' => 'Metodi di pagamento accettati',
      'services' => 'Servizi',
      'food_and_drink' => 'Cibo e bevande',
      'fashion' => 'Moda',
      'toys' => 'Giocattoli e prodotti per bambini',
      'other' => 'Altro',
      'restaurant' => 'Ristorante',
      'take_away' => 'Take away',
      'boutique' => 'Negozio d\'abbigliamento',
      'hair_stylist' => 'Parruchieri',
      'bar_and_cafe' => 'Bar e caffetteria',
      'electronics' => 'Elettronica',
      'entertainment' => 'Intrattenimento',
      'sports_and_leisure' => 'Sport e tempo libero',
      'jewelry' => 'Gioielleria',
      'pharmacy' => 'Farmacia',
      'kiosk' => 'Edicola',
      'flower_shop' => 'Fiorista',
      'service_provider' => 'Prestatori di servizio',
      'book_shop' => 'Libreria',
      'optician' => 'Ottico',
      'art_gallery' => 'Galleria d\'arte',
      'stationary_shop' => 'Cartoleria',
      'beauty_salon' => 'Salone di bellezza',
      'education' => 'Formazione',
      'grocery_store' => 'Alimentari ',
      'hotel' => 'Albergo',
      'taxi' => 'Taxi',
      'auto_and_moto' => 'Auto e moto',
      'retail' => 'Commerci',
      'home_and_garden' => 'Casa e giardino',
      'local_food_products' => 'Prodotti alimentari locali',
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
