<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreatProductsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('products', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->integer('price');
            $table->string('contact_info');
            $table->date('exp_date');
            $table->integer('days1');
            $table->integer('discount1');
            $table->integer('days2');
            $table->integer('discount2');
            $table->integer('days3');
            $table->integer('discount3');
            $table->string('img_url');
            $table->integer('quantity')->default(1);
            $table->integer('category_id')->foreign('category_id')->references('id')->on('categories')->onDelete('cascade');
            $table->integer('user_id')->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        //
    }
}
