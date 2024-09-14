<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('simplecrud_activity_logs', function (Blueprint $table) {
            $table->id();
            $table->timestamps();
            $table->unsignedBigInteger('admin_id')->index();
            $table->string('type', 32)->index();
            $table->string('affected_entity', 32)->nullable()->index();
            $table->unsignedBigInteger('entity_id')->nullable()->index();
            $table->json('diff')->nullable();
            $table->unsignedBigInteger('nr_of_affected_rows')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('simplecrud_activity_logs');
    }
};
