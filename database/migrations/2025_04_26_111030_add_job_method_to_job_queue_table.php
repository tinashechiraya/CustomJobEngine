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
        Schema::table('job_queue', function (Blueprint $table) {
            $table->string('job_method')->after('job_class');
        });
    }

    public function down()
    {
        Schema::table('job_queue', function (Blueprint $table) {
            $table->dropColumn('job_method');
        });
    }

};
