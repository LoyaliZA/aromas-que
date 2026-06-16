<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasColumn('client_types', 'queue_priority')) {
            return;
        }

        DB::table('client_types')
            ->whereNotNull('queue_priority')
            ->update([
                'sort_order' => DB::raw('queue_priority'),
            ]);

        Schema::table('client_types', function (Blueprint $table) {
            $table->dropColumn('queue_priority');
        });
    }

    public function down(): void
    {
        if (Schema::hasColumn('client_types', 'queue_priority')) {
            return;
        }

        Schema::table('client_types', function (Blueprint $table) {
            $table->unsignedSmallInteger('queue_priority')->nullable()->after('sort_order');
        });

        DB::table('client_types')
            ->where('prioritize_in_queue', true)
            ->update([
                'queue_priority' => DB::raw('sort_order'),
            ]);
    }
};
