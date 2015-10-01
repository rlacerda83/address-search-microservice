<?php

use App\Models\AddressSearch;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class CreateAddressSearchTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create(AddressSearch::getTableName(), function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('name');
            $table->bigInteger('country_id')->unsigned();
            $table->string('model_reference');
            $table->tinyInteger('status')->default(1);
            $table->timestamps();

            $table->index(['name']);
            $table->foreign('country_id')->references('id')->on(\App\Models\Country::getTableName())->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        if (Schema::hasTable(AddressSearch::getTableName())) {
            Schema::drop(AddressSearch::getTableName());
        }
    }
}
