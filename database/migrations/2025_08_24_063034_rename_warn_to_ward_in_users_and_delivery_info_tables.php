<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Add ward column and copy data from warn, then drop warn column in users table
        Schema::table('users', function (Blueprint $table) {
            $table->text('ward')->nullable()->after('district');
        });
        
        // Copy data from warn to ward in users table
        DB::statement('UPDATE users SET ward = warn');
        
        // Drop warn column in users table
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn('warn');
        });

        // Add ward column and copy data from warn, then drop warn column in delivery_info table
        Schema::table('delivery_info', function (Blueprint $table) {
            $table->text('ward')->nullable()->after('district');
        });
        
        // Copy data from warn to ward in delivery_info table (if any exists)
        DB::statement('UPDATE delivery_info SET ward = warn WHERE warn IS NOT NULL');
        
        // Drop warn column in delivery_info table
        Schema::table('delivery_info', function (Blueprint $table) {
            $table->dropColumn('warn');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Add warn column back and copy data from ward, then drop ward column in users table
        Schema::table('users', function (Blueprint $table) {
            $table->text('warn')->nullable()->after('district');
        });
        
        DB::statement('UPDATE users SET warn = ward');
        
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn('ward');
        });

        // Add warn column back and copy data from ward, then drop ward column in delivery_info table
        Schema::table('delivery_info', function (Blueprint $table) {
            $table->text('warn')->nullable()->after('district');
        });
        
        DB::statement('UPDATE delivery_info SET warn = ward WHERE ward IS NOT NULL');
        
        Schema::table('delivery_info', function (Blueprint $table) {
            $table->dropColumn('ward');
        });
    }
};
