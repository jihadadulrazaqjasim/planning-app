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
        Schema::create('tasks', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->longText('description');
            $table->string('image')->nullable();
            $table->date('due_date')->nullable();
            $table->string('current_status')->default('to_do');
            // $table->foreignId('developer_id');
            // $table->foreignId('tester_id');
            $table->foreignId('user_id');
            $table->foreignId('board_id');
            $table->timestamps();

            // $table->foreign('developer_id')->references('id')->on('boards')
            // ->onDelete('cascade');
            // $table->foreign('tester_id')->references('id')->on('boards')
            // ->onDelete('cascade');
            $table->foreign('user_id')->references('id')->on('users')
            ->onDelete('cascade');
            $table->foreign('board_id')->references('id')->on('boards')
            ->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('tasks');
    }
};
