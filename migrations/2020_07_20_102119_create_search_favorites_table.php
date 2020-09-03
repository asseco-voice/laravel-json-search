<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateSearchFavoritesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('search_favorites', function (Blueprint $table) {
            $table->id();
            $table->timestamps();

            $table->string('user_id')->nullable();
            $table->string('name');
            $table->text('description');
            $table->json('search');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('search_favorites');
    }
}
