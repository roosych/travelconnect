<?php

namespace App\Domain\Services\Observers;

use App\Domain\Services\Models\ServiceAttribute;
use App\Domain\Services\ServiceCatalog;

/** Любое изменение атрибута сбрасывает кеш каталога. */
class ServiceAttributeObserver
{
    public function saved(ServiceAttribute $attribute): void
    {
        ServiceCatalog::flush();
    }

    public function deleted(ServiceAttribute $attribute): void
    {
        ServiceCatalog::flush();
    }
}
