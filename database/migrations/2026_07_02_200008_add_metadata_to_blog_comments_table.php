<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('blog_comments', function (Blueprint $table) {
            $table->string('ip_address', 45)->nullable()->after('status')->index();
            $table->text('user_agent')->nullable()->after('ip_address');
            $table->string('referrer', 2048)->nullable()->after('user_agent');
        });
    }

    public function down(): void
    {
        Schema::table('blog_comments', function (Blueprint $table) {
            $table->dropIndex(['ip_address']);
            $table->dropColumn(['ip_address', 'user_agent', 'referrer']);
        });
    }
};
