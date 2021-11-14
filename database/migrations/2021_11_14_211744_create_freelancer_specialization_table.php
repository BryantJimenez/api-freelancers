<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateFreelancerSpecializationTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('freelancer_specialization', function (Blueprint $table) {
            $table->id();
            $table->bigInteger('specialization_id')->unsigned()->nullable();
            $table->bigInteger('freelancer_id')->unsigned()->nullable();
            $table->timestamps();

            #Relations
            $table->foreign('specialization_id')->references('id')->on('specializations')->onDelete('cascade')->onUpdate('cascade');
            $table->foreign('freelancer_id')->references('id')->on('freelancers')->onDelete('cascade')->onUpdate('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('freelancer_specialization');
    }
}
