<?php

namespace App\Support;

/**
 * Публичный PNR-код для адресуемых документов (R-7K2P9Q).
 *
 * Модель задаёт префикс через $publicCodePrefix. Код генерится при создании,
 * хранится целиком в колонке public_code и служит ключом роутинга (URL по коду,
 * а не по последовательному id — чтобы не светить объёмы).
 */
trait HasPublicCode
{
    /** Алфавит без неоднозначных символов I, L, O, U (Crockford-подобный). */
    private const PUBLIC_CODE_ALPHABET = '0123456789ABCDEFGHJKMNPQRSTVWXYZ';

    /** Длина случайной части кода. */
    private const PUBLIC_CODE_LENGTH = 6;

    public static function bootHasPublicCode(): void
    {
        static::creating(function ($model) {
            if (empty($model->public_code)) {
                $model->public_code = $model->generatePublicCode();
            }
        });
    }

    public function generatePublicCode(): string
    {
        $alphabet = self::PUBLIC_CODE_ALPHABET;
        $max = strlen($alphabet) - 1;

        do {
            $body = '';
            for ($i = 0; $i < self::PUBLIC_CODE_LENGTH; $i++) {
                $body .= $alphabet[random_int(0, $max)];
            }
            $code = $this->publicCodePrefix.'-'.$body;
        } while (static::where('public_code', $code)->exists());

        return $code;
    }

    public function getRouteKeyName(): string
    {
        return 'public_code';
    }
}
