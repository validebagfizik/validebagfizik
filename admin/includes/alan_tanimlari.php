<?php
/*
    admin/includes/alan_tanimlari.php
    ===================================================================
    ÇOKLU sayfaların özel alanlarının TEK KAYNAĞI. Yeni bir sayfaya özel
    alan eklemek/çıkarmak istediğinde SADECE burayı düzenle — modal.php,
    meta çubuğu ve liste tablosu buradan otomatik besleniyor.

    'once'  => Konu/Başlık alanlarından ÖNCE popup'ta gösterilecekler
    'sonra' => Konu/Başlık alanlarından SONRA popup'ta gösterilecekler
    type    => 'select' | 'text' | 'kapak'
    liste_* => Liste tablosunda bu alanın nasıl görüneceği (yoksa hiç
               sütun eklenmez — sadece popup+meta çubuğunda kalır)
    ===================================================================
*/
return [

    'soru' => [
        'once' => [
            ['key' => 'sinif', 'label' => 'Sınıf Seviyesi', 'type' => 'select',
             'options' => ['9. Sınıf', '10. Sınıf', '11. Sınıf', '12. Sınıf'], 'default' => '9. Sınıf',
             'liste_baslik' => 'Sınıf', 'liste_genislik' => '100px', 'liste_stil' => 'normal'],
        ],
        'sonra' => [
            ['key' => 'dogru', 'label' => 'Doğru Cevap', 'type' => 'select',
             'options' => ['A', 'B', 'C', 'D', 'E'], 'default' => 'A',
             'liste_baslik' => 'Doğru Cevap', 'liste_genislik' => '110px', 'liste_stil' => 'kalin'],
        ],
    ],

    'deney' => [
        'once' => [
            ['key' => 'sinif', 'label' => 'Sınıf Seviyesi', 'type' => 'select',
             'options' => ['9. Sınıf', '10. Sınıf', '11. Sınıf', '12. Sınıf'], 'default' => '9. Sınıf',
             'liste_baslik' => 'Sınıf', 'liste_genislik' => '100px', 'liste_stil' => 'normal'],
        ],
        'sonra' => [],
    ],

    'okuma' => [
        'once' => [],
        'sonra' => [
            ['key' => 'yazar', 'label' => 'Yazar', 'type' => 'text', 'placeholder' => 'Yazar adı',
             'liste_baslik' => 'Yazar', 'liste_genislik' => '160px', 'liste_stil' => 'normal'],
            ['key' => 'kapak', 'label' => 'Kapak Görseli', 'type' => 'kapak',
             'liste_baslik' => 'Kapak', 'liste_genislik' => '70px', 'liste_stil' => 'kapak'],
        ],
    ],

    'yonetim' => [
        'once' => [],
        'sonra' => [],
    ],

];
