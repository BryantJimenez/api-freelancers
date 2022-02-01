<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateProjectsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('projects', function (Blueprint $table) {
            $table->id();
            $table->date('start');
            $table->date('end');
            $table->text('content');
            $table->float('amount', 10, 2)->default(0.00);
            $table->enum('state', [0, 1, 2])->default(2);
            $table->enum('pay_state', [0, 1, 2])->default(2);
            $table->bigInteger('user_id')->unsigned()->nullable();
            $table->bigInteger('employer_id')->unsigned()->nullable();
            $table->bigInteger('proposal_id')->unsigned()->nullable();
            $table->bigInteger('payment_id')->unsigned()->nullable();
            $table->timestamps();
            $table->softDeletes();

            #Relations
            $table->foreign('user_id')->references('id')->on('users')->onDelete('set null')->onUpdate('cascade');
            $table->foreign('employer_id')->references('id')->on('users')->onDelete('set null')->onUpdate('cascade');
            $table->foreign('proposal_id')->references('id')->on('proposals')->onDelete('set null')->onUpdate('cascade');
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
        Schema::dropIfExists('projects');
    }
}
