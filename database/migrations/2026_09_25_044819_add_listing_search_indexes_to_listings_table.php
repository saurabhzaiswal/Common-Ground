<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('listings', function (Blueprint $table): void {
            $table->index(['user_id', 'created_at'], 'listings_user_created_index');
            $table->index(['category_id', 'city', 'created_at'], 'listings_category_city_created_index');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('listings', function (Blueprint $table): void {
            $table->dropIndex('listings_user_created_index');
            $table->dropIndex('listings_category_city_created_index');
        });
    }
};
