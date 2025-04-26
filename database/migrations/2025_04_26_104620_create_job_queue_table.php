<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up()
    {
        Schema::create('job_queue', function (Blueprint $table) {
            $table->id();
            $table->string('job_class');
            $table->text('payload')->nullable();
            $table->enum('status', ['pending', 'running', 'failed', 'completed'])->default('pending');
            $table->unsignedInteger('retry_count')->default(0);
            $table->text('error_message')->nullable();
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('job_queue');
    }

};
