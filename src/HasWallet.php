<?php

namespace Depsimon\Wallet;

use Exception;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

trait HasWallet
{
    /**
     * Retrieve the balance of this user's wallet
     */
    public function getBalanceAttribute()
    {
        return $this->wallet->balance;
    }

    /**
     * Retrieve the wallet of this user
     */
    public function wallet()
    {
        return $this->hasOne(Wallet::class)->withDefault([
            'address' => $this->walletAddress(),
            'balance' => 0.0
        ]);
    }

    /**
     * Retrieve all transactions of this user
     */
    public function transactions()
    {
        return $this->hasManyThrough(
            config('wallet.transaction_model', Transaction::class),
            config('wallet.wallet_model', Wallet::class)
        )->latest();
    }

    /**
     * Determine if the user can withdraw the given amount
     * @param  integer $amount
     * @return boolean
     */
    public function canWithdraw($amount)
    {
        return $this->balance >= $amount;
    }

    /**
     * Move credits to this account
     * @param  integer $amount
     * @param  string  $type
     * @param  array   $meta
     * @return Depsimon\Wallet\Transaction
     */
    public function deposit($amount, $type = 'deposit', $meta = [], $forceFail = false)
    {
        $accepted = $amount >= 0 && !$forceFail ? true : false;

        try {
            DB::beginTransaction();
            if ($accepted) {
                $this->wallet->balance += $amount;

                $this->wallet->save();
            } elseif (!$this->wallet->exists) {
                $this->wallet->save();
            }

            $transaction = $this->wallet->transactions()
                ->create([
                    'amount' => $amount,
                    'hash' => substr((string) Str::uuid(), 4, 9),
                    'type' => $type,
                    'meta' => $meta
                ]);

            if (!$accepted && !$forceFail) {
                throw new Exception(sprintf('The transaction(%s) has not been accepted', $type));
                logger($transaction);
            }

            DB::commit();
            return $transaction;
        } catch (Exception $e) {
            DB::rollBack();
            logger($e);
            return false;
        }
    }

    /**
     * Fail to move credits to this account
     * @param  integer $amount
     * @param  string  $type
     * @param  array   $meta
     * @return Depsimon\Wallet\Transaction
     */
    public function failDeposit($amount, $type = 'deposit', $meta = [])
    {
        return $this->deposit($amount, $type, $meta, true);
    }

    /**
     * Attempt to move credits from this account
     * @param  integer $amount
     * @param  string  $type
     * @param  array   $meta
     * @param  boolean $shouldAccept
     * @return Depsimon\Wallet\Transaction
     */
    public function withdraw($amount, $type = 'withdraw', $meta = [], $shouldAccept = true)
    {
        $accepted = $shouldAccept ? $this->canWithdraw($amount) : true;
        try {
            DB::beginTransaction();
            if ($accepted) {
                $this->wallet->balance -= $amount;
                $this->wallet->save();
            } elseif (!$this->wallet->exists) {
                $this->wallet->save();
            }

            $transaction = $this->wallet->transactions()
                ->create([
                    'amount' => $amount,
                    'hash' => substr((string) Str::uuid(), 4, 9),
                    'type' => $type,
                    'meta' => $meta,
                ]);

            if (!$accepted) {
                throw new Exception(sprintf('The transaction(%s) has not been accepted', $type));
                logger($transaction);
            }

            DB::commit();
            return $transaction;
        } catch (Exception $e) {
            DB::rollback();
            logger($e);
            return false;
        }
    }

    /**
     * Move credits from this account
     * @param  integer $amount
     * @param  string  $type
     * @param  array   $meta
     */
    public function forceWithdraw($amount, $type = 'withdraw', $meta = [])
    {
        return $this->withdraw($amount, $type, $meta, false);
    }

    /**
     * Returns the actual balance for this wallet.
     * Might be different from the balance property if the database is manipulated
     * @return float balance
     */
    public function actualBalance()
    {
        $credits = $this->wallet->transactions()->whereIn('type', ['deposit', 'refund', 'receive'])->sum('amount');

        $debits = $this->wallet->transactions()->whereIn('type', ['withdraw', 'payout', 'send'])->sum('amount');

        return $credits - $debits;
    }

    protected function walletAddress()
    {
        return str_replace('-', '', substr((string) Str::uuid(), 4, 23));
    }
}
