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
        Schema::table('carpool_routes', function (Blueprint $table) {
            $table->integer('bags_available')->nullable()->after('seats_available');
            $table->integer('bags_seats_available')->nullable()->after('bags_available');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('carpool_routes', function (Blueprint $table) {
            $table->dropColumn('bags_available');
            $table->dropColumn('bags_seats_available');
        });
    }
};
