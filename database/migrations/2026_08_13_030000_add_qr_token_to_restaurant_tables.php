<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('restaurant_tables', function (Blueprint $table) {
            $table->string('qr_token', 64)->nullable()->unique()->after('status');
        });

        $tables = DB::table('restaurant_tables')->select('id', 'qr_token')->get();
        foreach ($tables as $row) {
            if (! empty($row->qr_token)) {
                continue;
            }
            DB::table('restaurant_tables')->where('id', $row->id)->update([
                'qr_token' => Str::lower(Str::random(24)),
            ]);
        }
    }

    public function down(): void
    {
        Schema::table('restaurant_tables', function (Blueprint $table) {
            $table->dropColumn('qr_token');
        });
    }
};
