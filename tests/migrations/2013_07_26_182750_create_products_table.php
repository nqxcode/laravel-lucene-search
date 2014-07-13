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

        $now = Carbon::now();

        foreach (range(0, 30) as $i) {
            DB::table('products')->insert(array(
                'name' => 'clock',
                'description' => 'very cool',
                'created_at' => $now,
                'updated_at' => $now,
            ));
        }

        foreach (range(0, 10) as $i) {
            DB::table('products')->insert(array(
                'name' => 'battery',
                'description' => 'very big',
                'created_at' => $now,
                'updated_at' => $now,
            ));
        }
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
}