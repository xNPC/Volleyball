<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('roster_requests', function (Blueprint $table) {
            $table->string('old_jersey_number', 5)->nullable()->after('jersey_number');
        });
    }

    public function down(): void
    {
        Schema::table('roster_requests', function (Blueprint $table) {
            $table->dropColumn('old_jersey_number');
        });
    }
};
