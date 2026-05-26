<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddVolleyballTypeToTournaments extends Migration
{
    public function up()
    {
        Schema::table('tournaments', function (Blueprint $table) {
            $table->enum('volleyball_type', ['indoor', 'beach'])->default('indoor')->after('name');
        });
    }

    public function down()
    {
        Schema::table('tournaments', function (Blueprint $table) {
            $table->dropColumn('volleyball_type');
        });
    }
}
