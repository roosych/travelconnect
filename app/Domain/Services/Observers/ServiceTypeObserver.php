<?php

namespace App\Domain\Services\Observers;

use App\Domain\Services\Models\ServiceType;
use App\Domain\Services\ServiceCatalog;

/** Любое изменение типа услуги сбрасывает кеш каталога. */
class ServiceTypeObserver
{
    public function saved(ServiceType $type): void
    {
        ServiceCatalog::flush();
    }

    public function deleted(ServiceType $type): void
    {
        ServiceCatalog::flush();
    }
}
