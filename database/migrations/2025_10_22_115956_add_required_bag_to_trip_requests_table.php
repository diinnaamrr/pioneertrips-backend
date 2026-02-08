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
        Schema::table('trip_requests', function (Blueprint $table) {
            $table->integer('required_bags')->nullable()->after('required_seats');
            $table->boolean('has_bags')->nullable()->after('required_bags')->default(false);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('trip_requests', function (Blueprint $table) {
            $table->dropColumn('required_bags');
            $table->dropColumn('has_bags');
        });
    }
};
