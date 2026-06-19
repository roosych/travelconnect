<?php

// Təchizatçının təklif göndərmə üçün açıq token səhifəsi (hesabsız).
return [
    'title'   => 'Təklif göndərin',
    'loading' => 'Sorğu məlumatları yüklənir...',

    'errors' => [
        'invalid_title' => 'Keçərsiz keçid',
        'invalid_msg'   => 'Bu keçid keçərsizdir və ya vaxtı bitib.',
        'expired'       => 'Keçidin vaxtı bitib',
        'not_found'     => 'Keçid tapılmadı',
        'rfq_closed'    => 'Sorğu bağlıdır',
        'generic'       => 'Xəta',
        'generic_msg'   => 'Xəta baş verdi.',
        'network'       => 'Şəbəkə xətası. Bağlantını yoxlayıb yenidən cəhd edin.',
        'network_short' => 'Şəbəkə xətası. Bağlantını yoxlayın.',
    ],

    'heading'    => 'Sizin təklifiniz',
    'subheading' => ':supplier tərəfindən cavab — hər xidmət üzrə doldurun',

    'request_notes'  => 'Sorğu qeydləri:',
    'operator_files' => 'Operatordan fayllar',
    'operator_note'  => 'Operatordan:',
    'pax_unit'       => 'nəfər',
    'deadline'       => 'Cavab müddəti',
    'closed'         => 'Bu xidmət üzrə sorğu bağlıdır.',

    'status' => [
        'received'  => 'Göndərildi, baxış gözlənilir',
        'reviewed'  => 'Baxılır',
        'selected'  => 'Seçildi ✓',
        'rejected'  => 'Seçilmədi',
        'withdrawn' => 'Geri götürüldü',
        'expired'   => 'Vaxtı bitdi',
    ],

    'submitted' => 'Təklif göndərildi',
    'edit'      => 'Dəyiş',
    'withdraw'  => 'Geri götür',
    'save'      => 'Yadda saxla',
    'existing_files' => 'Əlavə edilmiş fayllar',
    'add_files'      => 'Fayl əlavə et',

    'from_catalog'  => 'Kataloqdan seçin',
    'manual'        => '— Əl ilə daxil edin —',
    'capacity_unit' => 'yer',
    'name_ph'       => 'Qısa təsvir (istəyə bağlı)',
    'notes_ph'      => 'Qeydlər / şərtlər (istəyə bağlı)',
    'files_label'   => 'Fayllar (istəyə bağlı)',
    'fp_idle'       => 'Faylları buraya atın və ya <span class="filepond--label-action">seçin</span>',
    'consent'       => 'Qiymətin aktual olduğunu və xidməti göstərilən şərtlərlə yerinə yetirəcəyimi təsdiqləyirəm',
    'submit'        => 'Təklifi göndər',
    'submitting'    => 'Göndərilir...',

    'err_price'        => 'Qiymət daxil edin.',
    'err_consent'      => 'Göndərmədən əvvəl razılığı təsdiqləyin.',
    'err_submit'       => 'Təklif göndərilə bilmədi.',
    'err_upload'       => 'Fayl yüklənə bilmədi.',
    'withdraw_confirm' => 'Bu xidmət üzrə təklifinizi geri götürmək?',
    'withdraw_title'   => 'Təklifin geri götürülməsi',
    'cancel'           => 'Ləğv et',
    'err_withdraw'     => 'Təklif geri götürülə bilmədi.',
];
