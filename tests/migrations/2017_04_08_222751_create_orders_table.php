<?php

use Carbon\Carbon;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class CreateOrdersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('orders', function (Blueprint $table) {
            $table->increments('id');
            $table->string('name')->nullable();
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
        Schema::drop('orders');
    }

    /**
     * Fill database.
     */
    private function seed()
    {
        $now = Carbon::now();

        DB::table('orders')->insert(array(
            'name' => 'First order',
            'created_at' => $now,
            'updated_at' => $now,
        ));

        DB::table('orders')->insert(array(
            'name' => 'Second order',
            'created_at' => $now,
            'updated_at' => $now,
        ));
    }
}
