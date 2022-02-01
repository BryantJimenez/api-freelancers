<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateStripesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('stripes', function (Blueprint $table) {
            $table->id();
            $table->string('stripe_payment_id');
            $table->string('balance_transaction');
            $table->bigInteger('payment_id')->unsigned()->nullable();
            $table->timestamps();

            #Relations
            $table->foreign('payment_id')->references('id')->on('payments')->onDelete('set null')->onUpdate('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('stripes');
    }
}
