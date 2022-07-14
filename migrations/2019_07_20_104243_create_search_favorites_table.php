<?php

use Asseco\BlueprintAudit\App\MigrationMethodPicker;
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
            if (config('asseco-search.migrations.uuid')) {
                $table->uuid('id')->primary();
            } else {
                $table->id();
            }

            $table->string('model');
            $table->string('owner_id')->nullable();
            $table->string('name');
            $table->text('description')->nullable();
            $table->json('search');

            $table->index(['name', 'owner_id']);

            MigrationMethodPicker::pick($table, config('asseco-search.migrations.timestamps'));
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
