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
            $table->string('owner_id')->nullable();
            $table->string('name');
            $table->text('description')->nullable();
            $table->json('search');

            $table->index(['name', 'owner_id']);

            $table->timestamps();
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
