<?php

return [
    'subtitle'   => 'Hər bildiriş kateqoriyası üçün çatdırılma kanallarını seçin.',
    'page_subtitle' => 'Hesabınız üçün bildiriş çatdırılma kanalları',
    'category'   => 'Kateqoriya',
    'load_error' => 'Bildiriş ayarlarını yükləmək alınmadı.',
    'saved'      => 'Bildiriş ayarları yadda saxlanıldı',
    'save_error' => 'Yadda saxlamaq alınmadı',

    // Kateqoriyalar (NotificationCategory enum) — ayarlar matrisi və zəng
    'cat' => [
        'request_status'    => ['label' => 'Sorğu statusu',          'desc' => 'Operator sorğunu işə götürdükdə və ya ləğv etdikdə'],
        'rfq'               => ['label' => 'Sorğular',               'desc' => 'Operatorlardan yeni qiymət sorğuları'],
        'proposal'          => ['label' => 'Kommersiya təklifləri',   'desc' => 'Sorğularınız üzrə yeni təkliflər'],
        'booking'           => ['label' => 'Bronlar',               'desc' => 'Hesablar, ödənişlər və bron statusunun dəyişməsi'],
        'offer'             => ['label' => 'Offer qərarları',         'desc' => 'Offeriniz qəbul və ya rədd edildikdə'],
        'operator_offer'    => ['label' => 'Yeni offerlər',          'desc' => 'Təchizatçı sorğu üzrə offer verdi'],
        'operator_proposal' => ['label' => 'Agentliyin təklif qərarları', 'desc' => 'Agentlik təklifi qəbul və ya rədd etdi'],
        'operator_request'  => ['label' => 'Yeni sorğular',          'desc' => 'Agentlik yeni sorğu göndərdi'],
    ],

    'tg' => [
        'linked'         => 'Bağlanıb',
        'not_linked'     => 'Bağlanmayıb',
        'link'           => 'Telegram bağla',
        'unlink'         => 'Ayır',
        'link_hint'      => 'Telegram-ı açın və «Start» düyməsinə basın, sonra səhifəni yeniləyin.',
        'link_error'     => 'Bağlama keçidini almaq alınmadı',
        'unlink_confirm' => 'Telegram-ı ayırmaq? Telegram-da bildirişlər gəlməyəcək.',
        'unlinked'       => 'Telegram ayrıldı',
        'unlink_error'   => 'Ayırmaq alınmadı',
    ],

    // Bildiriş zəngi (in-app) — bütün kabinetlərdə ümumi partial
    'bell' => [
        'title'      => 'Bildirişlər',
        'mark_all'   => 'Hamısını oxu',
        'settings'   => 'Bildiriş parametrləri',
        'empty'      => 'Bildiriş yoxdur',
        'load_error' => 'Yükləmək alınmadı',
        'just_now'   => 'indicə',
        'min_ago'    => ':n dəq əvvəl',
        'hour_ago'   => ':n saat əvvəl',
        'day_ago'    => ':n gün əvvəl',
    ],
];
