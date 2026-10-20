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
        Schema::table('users', function (Blueprint $table) {
            $table->string('personal_data_consent_ip', 45)->nullable()->after('personal_data_consent_at');
            $table->string('personal_data_consent_user_agent', 512)->nullable()->after('personal_data_consent_ip');
            $table->timestamp('personal_data_consent_doc_version')->nullable()->after('personal_data_consent_user_agent');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn([
                'personal_data_consent_ip',
                'personal_data_consent_user_agent',
                'personal_data_consent_doc_version',
            ]);
        });
    }
};
