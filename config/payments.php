<?php

return [

    // Базовая (рабочая) валюта системы — в неё нормализуются все платежи для агрегации.
    'base_currency' => env('PAYMENTS_BASE_CURRENCY', 'AZN'),

    // Разрешать ли платёж сверх остатка. По умолчанию нет: сумма сделки известна
    // из брони, переплата невозможна.
    'allow_overpayment' => (bool) env('PAYMENTS_ALLOW_OVERPAYMENT', false),

];
