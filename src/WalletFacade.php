<?php

namespace Tolawho\Wallet;

use Illuminate\Support\Facades\Facade;

/**
 * @see \Tolawho\Wallet\Wallet
 */
class WalletFacade extends Facade
{
    /**
     * Get the registered name of the component.
     *
     * @return string
     */
    protected static function getFacadeAccessor()
    {
        return 'wallet';
    }
}
