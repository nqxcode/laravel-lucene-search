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
            $table->string('name');
            $table->string('description');

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
    }
}
