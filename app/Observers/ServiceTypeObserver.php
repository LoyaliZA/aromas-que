<?php

namespace App\Observers;

use App\Models\ServiceType;
use Illuminate\Support\Facades\DB;

class ServiceTypeObserver
{
    public function saved(ServiceType $serviceType): void
    {
        ServiceType::forgetNameCache($serviceType->name);

        if ($serviceType->isDirty('name') && $serviceType->getOriginal('name')) {
            $oldName = $serviceType->getOriginal('name');
            ServiceType::forgetNameCache($oldName);

            if (\Illuminate\Support\Facades\Schema::hasColumn('sales_queue', 'service_type')) {
                DB::table('sales_queue')->where('service_type', $oldName)->update(['service_type' => $serviceType->name]);
            }
        }
    }

    public function deleted(ServiceType $serviceType): void
    {
        ServiceType::forgetNameCache($serviceType->name);
    }
}
