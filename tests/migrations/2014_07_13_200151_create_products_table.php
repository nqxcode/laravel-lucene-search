<?php

use Carbon\Carbon;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class CreateProductsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('products', function (Blueprint $table) {
            $table->increments('id');
            $table->string('name')->nullable();
            $table->string('description')->nullable();
            $table->boolean('publish')->default(1);

            $table->timestamps();
        });

        $this->seed();
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::drop('products');
    }

    /**
     * Fill database.
     */
    private function seed()
    {
        $now = Carbon::now();

        DB::table('products')->insert(array(
            'name' => 'big analog clock',
            'description' => 'very big, analog',
            'created_at' => $now,
            'updated_at' => $now,
        ));

        DB::table('products')->insert(array(
            'name' => 'simple analog clock',
            'description' => 'not very big, analog',
            'created_at' => $now,
            'updated_at' => $now,
        ));

        DB::table('products')->insert(array(
            'name' => 'electronic clock',
            'description' => 'very small, electronic',
            'created_at' => $now,
            'updated_at' => $now,
        ));

        DB::table('products')->insert(array(
            'name' => 'acoustic system',
            'description' => 'small and compact',
            'created_at' => $now,
            'updated_at' => $now,
        ));

        DB::table('products')->insert(array(
            'name' => 'monitor',
            'description' => 'sensor, compact, small',
            'created_at' => $now,
            'updated_at' => $now,
        ));

        DB::table('products')->insert(array(
            'name' => 'not published product',
            'description' => 'not published product',
            'created_at' => $now,
            'updated_at' => $now,
            'publish' => 0,
        ));

        DB::table('products')->insert(array(
            'name' => 'тестовое название',
            'description' => 'тестовое описание и не только со стоп-словами',
            'created_at' => $now,
            'updated_at' => $now,
            'publish' => 1,
        ));
    }
}
