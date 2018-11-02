<?php

return [

    /**
     * Change this to specify the money amount column types
     * If not explicitly set to 'decimal' integer columns are used
     */
    'user_model' => 'App\User',

    /**
     * Change this if you need to extend the default Wallet Model
     */
    'wallet_model' => 'Tolawho\Wallet\Wallet',

    /**
     * Change this if you need to extend the default Transaction Model
     */
    'transaction_model' => 'Tolawho\Wallet\Transaction',

];
