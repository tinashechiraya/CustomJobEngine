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
        Schema::create('job_queues', function (Blueprint $table) {
            $table->text('error_log')->nullable();
            $table->boolean('is_cancelled')->default(false);
        });
    
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('job_queue', function (Blueprint $table) {
            $table->dropColumn('error_log');
            $table->boolean('is_cancelled')->default(false);
        });
    }
};
