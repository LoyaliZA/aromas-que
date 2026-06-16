<?php

namespace App\Observers;

use App\Models\ClientType;
use Illuminate\Support\Facades\DB;

class ClientTypeObserver
{
    public function saving(ClientType $clientType): void
    {
        if ($clientType->code) {
            $clientType->name = $clientType->code;
        }
    }

    public function saved(ClientType $clientType): void
    {
        if ($clientType->code) {
            ClientType::forgetCodeCache($clientType->code);
        }

        if ($clientType->isDirty('code') && $clientType->getOriginal('code')) {
            ClientType::forgetCodeCache($clientType->getOriginal('code'));
        }

        if ($clientType->isDirty('name') && $clientType->getOriginal('name')) {
            ClientType::forgetNameCache($clientType->getOriginal('name'));
        }

        if ($clientType->isDirty('code') && $clientType->getOriginal('code')) {
            $oldCode = $clientType->getOriginal('code');

            if (\Illuminate\Support\Facades\Schema::hasColumn('customers', 'client_type')) {
                DB::table('customers')->where('client_type', $oldCode)->update(['client_type' => $clientType->code]);
            }
            if (\Illuminate\Support\Facades\Schema::hasColumn('sales_queue', 'client_type')) {
                DB::table('sales_queue')->where('client_type', $oldCode)->update(['client_type' => $clientType->code]);
            }
        }
    }

    public function deleted(ClientType $clientType): void
    {
        if ($clientType->code) {
            ClientType::forgetCodeCache($clientType->code);
        }
    }
}
