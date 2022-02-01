<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreatePaymentsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('payments', function (Blueprint $table) {
            $table->id();
            $table->string('subject');
            $table->float('total', 10, 2)->default(0.00)->unsigned();
            $table->float('fee', 10, 2)->default(0.00)->unsigned();
            $table->float('balance', 10, 2)->default(0.00)->unsigned();
            $table->enum('method', [1, 2])->default(1);
            $table->enum('type', [1, 2])->default(1);
            $table->enum('state', [0, 1, 2])->default(2);
            $table->string('currency')->default('USD');
            $table->bigInteger('user_id')->unsigned()->nullable();
            $table->timestamps();
            $table->softDeletes();

            #Relations
            $table->foreign('user_id')->references('id')->on('users')->onDelete('set null')->onUpdate('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('payments');
    }
}
