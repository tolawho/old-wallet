<?php

namespace Tolawho\Wallet\Tests\Unit;

use Tolawho\Wallet\Wallet;
use Tolawho\Wallet\UnacceptedTransactionException;
use Tolawho\Wallet\Tests\TestCase;
use Tolawho\Wallet\Tests\Models\User;

class HasWalletTest extends TestCase
{

    /** @test */
    public function wallet()
    {
        $user = factory(User::class)->create();
        $this->assertInstanceOf(Wallet::class, $user->wallet);
    }

    /** @test */
    public function deposit()
    {
        $user = factory(User::class)->create();
        $this->assertFalse($user->wallet->exists);
        $user->deposit(10);
        $this->assertTrue($user->wallet->exists);
        $this->assertEquals(1, $user->wallet->transactions()->withTrashed()->count());
        $this->assertEquals(1, $user->wallet->transactions->count());
        $this->assertEquals($user->balance, 10);
        $this->assertEquals($user->actualBalance(), 10);
        $user->deposit(100.75);
        $this->assertEquals($user->balance, 110.75);
        $this->assertEquals($user->actualBalance(), 110.75);
        $this->expectException(UnacceptedTransactionException::class);
        $transaction = $user->deposit(-30);
        $this->assertTrue($transaction->trashed());
    }

    /** @test */
    public function fail_deposit()
    {
        $user = factory(User::class)->create();
        $this->assertFalse($user->wallet->exists);
        $transaction = $user->failDeposit(10000);
        $this->assertTrue($transaction->trashed());
        $this->assertTrue($user->wallet->exists);
        $this->assertEquals(1, $user->wallet->transactions()->withTrashed()->count());
        $this->assertEquals(0, $user->wallet->transactions->count());
    }

    /** @test */
    public function withdraw()
    {
        $user = factory(User::class)->create();
        $this->assertFalse($user->wallet->exists);
        $this->expectException(UnacceptedTransactionException::class);
        $user->withdraw(10);
        $this->assertTrue($user->wallet->exists);
        $user->forceWithdraw(10);
        $this->assertEquals($user->balance, -10);
        $this->assertEquals($user->actualBalance(), -10);
        $this->assertEquals(1, $user->wallet->transactions->count());
        $this->assertEquals(2, $user->wallet->transactions()->withTrashed()->count());
    }
}
