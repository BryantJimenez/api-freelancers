<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateProposalsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('proposals', function (Blueprint $table) {
            $table->id();
            $table->date('start');
            $table->date('end')->nullable();
            $table->text('content');
            $table->float('amount', 10, 2)->default(0.00);
            $table->enum('state', [0, 1, 2])->default(2);
            $table->bigInteger('owner_id')->unsigned()->nullable();
            $table->bigInteger('receiver_id')->unsigned()->nullable();
            $table->bigInteger('chat_room_id')->unsigned()->nullable();
            $table->timestamps();
            $table->softDeletes();

            #Relations
            $table->foreign('owner_id')->references('id')->on('users')->onDelete('set null')->onUpdate('cascade');
            $table->foreign('receiver_id')->references('id')->on('users')->onDelete('set null')->onUpdate('cascade');
            $table->foreign('chat_room_id')->references('id')->on('chat_room')->onDelete('cascade')->onUpdate('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('proposals');
    }
}
