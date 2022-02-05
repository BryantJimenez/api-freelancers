<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateRetreatsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('retreats', function (Blueprint $table) {
            $table->id();
            $table->string('subject');
            $table->float('amount', 10, 2)->default(0.00)->unsigned();
            $table->enum('method', [1])->default(1);
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
        Schema::dropIfExists('retreats');
    }
}
