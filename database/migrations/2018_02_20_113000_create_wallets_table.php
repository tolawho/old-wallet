<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateWalletsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('wallets', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('owner_id')->nullable();
            $table->string('owner_type')->nullable();
            $table->uuid('address')->unique();

            $balanceColumnType = config('wallet.column_type');
            if ($balanceColumnType == 'decimal') {
                $table->decimal('balance', 12, 4)->default(0); // amount is an decimal, it could be "dollars" or "cents"
            } elseif ($balanceColumnType == 'float') {
                $table->float('balance', 8, 2); // amount is an float, it could be "dollars" or "cents"
            } else {
                $table->integer('balance');
            }

            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('wallets');
    }
}
