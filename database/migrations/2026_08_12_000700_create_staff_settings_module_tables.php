<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('roles', function (Blueprint $table) {
            $table->id();
            $table->string('name')->unique();
            $table->string('slug')->unique();
            $table->string('description')->nullable();
            $table->json('permissions')->nullable();
            $table->boolean('is_system')->default(false);
            $table->timestamps();
        });

        Schema::table('users', function (Blueprint $table) {
            $table->foreignId('role_id')->nullable()->after('id')->constrained('roles')->nullOnDelete();
            $table->string('phone', 40)->nullable()->after('email');
            $table->string('job_title', 120)->nullable()->after('phone');
            $table->string('status', 20)->default('active')->after('job_title'); // active|inactive
            $table->date('hired_on')->nullable()->after('status');
            $table->text('notes')->nullable()->after('hired_on');
        });

        Schema::create('site_settings', function (Blueprint $table) {
            $table->id();
            $table->string('restaurant_name')->default('Bynnas Restora');
            $table->string('tagline')->nullable();
            $table->string('phone', 40)->nullable();
            $table->string('email', 160)->nullable();
            $table->string('address_line1')->nullable();
            $table->string('address_line2')->nullable();
            $table->string('city')->nullable();
            $table->string('opening_hours')->nullable();
            $table->string('currency_symbol', 8)->default('৳');
            $table->string('timezone')->default('Asia/Dhaka');
            $table->boolean('reservations_enabled')->default(true);
            $table->boolean('online_ordering_enabled')->default(true);
            $table->text('footer_note')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('site_settings');

        Schema::table('users', function (Blueprint $table) {
            $table->dropConstrainedForeignId('role_id');
            $table->dropColumn(['phone', 'job_title', 'status', 'hired_on', 'notes']);
        });

        Schema::dropIfExists('roles');
    }
};
