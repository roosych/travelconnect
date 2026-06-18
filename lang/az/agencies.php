<?php

return [
    'title'           => 'Agentlik profili',
    'breadcrumb_list' => 'Agentliklər',

    // Общая форма (create/edit, partial _form)
    'form' => [
        'name'     => 'Agentliyin adı',
        'name_ph'  => 'məs. Sunshine Travel Agency',
        'email'    => 'E-poçt',
        'phone'    => 'Telefon',
        'country'  => 'Ölkə',
        'currency' => 'Valyuta',
    ],
    'select_none' => '— göstərilməyib —',

    // Список агентств (index)
    'index' => [
        'add'           => 'Agentlik əlavə et',
        'search_ph'     => 'Ad, e-poçt, telefon üzrə axtarış…',
        'all_countries' => 'Bütün ölkələr',
        'sort' => [
            'name_asc'  => 'Ada görə (A-Z)',
            'name_desc' => 'Ada görə (Z-A)',
            'bookings'  => 'Daha çox bron',
            'requests'  => 'Daha çox sorğu',
            'newest'    => 'Əvvəlcə yenilər',
        ],
        'chips' => [
            'all'           => 'Hamısı',
            'with_bookings' => 'Bronlu',
            'with_requests' => 'Sorğulu',
            'dormant'       => 'Fəaliyyətsiz',
        ],
        'load_error' => 'Agentlikləri yükləmək mümkün olmadı. Səhifəni yeniləyin.',
        'empty'      => 'Agentlik tapılmadı.',
        'cols' => [
            'agency'     => 'Agentlik',
            'contacts'   => 'Əlaqə',
            'requests'   => 'Sorğular',
            'bookings'   => 'Bronlar',
            'members'    => 'Əməkdaşlar',
            'registered' => 'Qeydiyyat',
            'actions'    => 'Əməliyyatlar',
        ],
        'pagination'     => ':total-dan :from–:to',
        'quick_view'     => 'Sürətli baxış',
        'created'        => 'Agentlik uğurla yaradıldı.',
        'delete_confirm' => 'Bu agentliyi silmək? Əməliyyat geri qaytarılmır.',
        'error_generic'  => 'Xəta baş verdi.',
        'drawer' => [
            'default_title' => 'Agentlik',
            'stat_requests' => 'Sorğular',
            'stat_bookings' => 'Bronlar',
            'stat_members'  => 'Əməkdaşlar',
            'contacts'      => 'Əlaqə',
            'not_specified' => 'Göstərilməyib',
            'since'         => ':date-dən',
            'open_card'     => 'Kartı aç',
        ],
    ],

    // Шапка профиля
    'header' => [
        'avatar_hint'   => 'Şəkli dəyişmək üçün klikləyin',
        'stat_requests' => 'Sorğular',
        'stat_bookings' => 'Bronlar',
        'stat_clients'  => 'Müştərilər',
        'member_since'  => ':date-dən',
        'load_error'    => 'Agentliyi yükləmək mümkün olmadı.',
        'photo_updated' => 'Şəkil yeniləndi.',
        'photo_error'   => 'Şəkli yükləmək mümkün olmadı.',
    ],

    'tabs' => [
        'requests'  => 'Sorğular',
        'proposals' => 'Kommersiya təklifləri',
        'members'   => 'Əməkdaşlar',
    ],

    // Вкладка «Заявки»
    'requests' => [
        'card_title'   => 'Sorğular',
        'empty'        => 'Bu agentlikdən hələ sorğu yoxdur.',
        'load_error'   => 'Sorğuları yükləmək mümkün olmadı.',
        'col_request'  => 'Sorğu və marşrut',
        'col_services' => 'Xidmətlər',
        'col_pax'      => 'Turist',
        'col_dates'    => 'Tur tarixləri',
        'col_status'   => 'Status',
        'col_created'  => 'Yaradılıb',
        'col_actions'  => 'Əməliyyatlar',
        'quick_view'   => 'Sürətli baxış',
        'open_page'    => 'Səhifəni aç',
        'default_title' => 'Sorğu :id',
        'whole_country' => 'bütün ölkə üzrə',
    ],

    // Вкладка «Коммерческие предложения»
    'proposals' => [
        'card_title'    => 'Kommersiya təklifləri',
        'empty'         => 'Bu agentlik üçün hələ kommersiya təklifi yoxdur.',
        'load_error'    => 'Kommersiya təkliflərini yükləmək mümkün olmadı.',
        'col_title'     => 'Ad',
        'col_request'   => 'Sorğu',
        'col_amount'    => 'Məbləğ',
        'col_status'    => 'Status',
        'col_created'   => 'Yaradılıb',
        'col_open'      => 'Aç',
        'default_title' => 'Təklif :id',
    ],

    // Вкладка «Сотрудники»
    'members' => [
        'card_title'     => 'Əməkdaşlar',
        'add'            => 'Əməkdaş əlavə et',
        'empty'          => 'Bu agentliyin hələ əməkdaşı yoxdur.',
        'load_error'     => 'Əməkdaşları yükləmək mümkün olmadı.',
        'col_member'     => 'Əməkdaş',
        'col_role'       => 'Rol',
        'col_joined'     => 'Əlavə edilib',
        'col_actions'    => 'Əməliyyatlar',
        'remove'         => 'Sil',
        'remove_confirm' => 'Əməkdaşı agentlikdən silmək?',
        'removed'        => 'Əməkdaş silindi.',
        'added'          => 'Əməkdaş əlavə edildi.',
    ],

    'roles' => [
        'owner'   => 'Sahib',
        'manager' => 'Menecer',
        'staff'   => 'Əməkdaş',
    ],

    // Модалка добавления сотрудника
    'add_modal' => [
        'title'          => 'Əməkdaş əlavə et',
        'email'          => 'Əməkdaşın e-poçtu',
        'email_hint'     => 'Hesab mövcud deyilsə — avtomatik yaradılacaq.',
        'name'           => 'Ad',
        'name_hint'      => '(yeni hesab üçün)',
        'name_ph'        => 'Əməkdaşın adı',
        'role'           => 'Rol',
        'email_required' => 'Əməkdaşın e-poçtunu daxil edin.',
        'error_generic'  => 'Xəta baş verdi.',
    ],

    // Модалка редактирования агентства
    'edit_modal' => [
        'title'             => 'Agentliyi redaktə et',
        'new_password'      => 'Yeni şifrə',
        'new_password_hint' => '(dəyişməmək üçün boş buraxın)',
        'submit'            => 'Agentliyi yenilə',
        'updated'           => 'Agentlik yeniləndi.',
        'error_generic'     => 'Xəta baş verdi.',
    ],

    'delete' => [
        'confirm' => 'Bu agentliyi həmişəlik silmək?',
        'done'    => 'Agentlik silindi.',
    ],

    // Дровер быстрого просмотра заявки
    'drawer' => [
        'title'        => 'Sorğu',
        'route'        => 'Ölkələr üzrə marşrut',
        'services'     => 'Xidmətlər',
        'pax'          => 'Turist',
        'dates'        => 'Tur tarixləri',
        'deadline'     => 'Son tarix',
        'created'      => 'Yaradılıb',
        'rfqs'         => 'Təchizatçılara sorğular',
        'proposals'    => 'Təkliflər',
        'notes'        => 'Qeydlər',
        'open_request' => 'Sorğunu aç',
    ],

    // Yaradıldıqdan sonra bir dəfə göstərilən giriş məlumatları modalı
    'credentials' => [
        'title'    => 'Agentlik yaradıldı',
        'notice'   => 'Bu giriş məlumatlarını saxlayın — parol yalnız indi göstərilir və bir daha əlçatan olmayacaq.',
        'login'    => 'Login (e-poçt)',
        'password' => 'Parol',
        'copied'   => 'Kopyalandı',
        'done'     => 'Hazırdır',
    ],
];
