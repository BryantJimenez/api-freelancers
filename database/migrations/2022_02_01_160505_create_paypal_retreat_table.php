<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreatePaypalRetreatTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('paypal_retreat', function (Blueprint $table) {
            $table->id();
            $table->string('email')->nullable();
            $table->bigInteger('retreat_id')->unsigned()->nullable();
            $table->timestamps();

            #Relations
            $table->foreign('retreat_id')->references('id')->on('retreats')->onDelete('set null')->onUpdate('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('paypal_retreat');
    }
}
