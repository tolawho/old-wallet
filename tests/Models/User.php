<?php

namespace Tolawho\Wallet\Tests\Models;

use Tolawho\Wallet\HasWallet;
use Illuminate\Foundation\Auth\User as AuthUser;

class User extends AuthUser
{
    use HasWallet;
}
