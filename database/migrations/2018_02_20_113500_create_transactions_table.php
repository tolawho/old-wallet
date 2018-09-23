<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateTransactionsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('transactions', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('wallet_id');
            // amount is an float, it could be "dollars" or "cents"
            $table->float('amount', 8, 2);
            $table->string('hash', 30); // hash is a uniqid for each transaction
            $table->string('type', 30); // type can be anything in your app, by default we use "deposit" and "withdraw"
            $table->json('meta')->nullable(); // Add all kind of meta information you need

            $table->timestamps();
            $table->foreign('wallet_id')->references('id')->on('wallets')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('transactions');
    }
}
