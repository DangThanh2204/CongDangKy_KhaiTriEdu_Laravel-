<?php

namespace App\Models;

use App\Models\MongoModel as Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Wallet extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'balance',
        'firefly_identity',
    ];

    protected $casts = [
        'balance' => 'decimal:2',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function transactions()
    {
        return $this->hasMany(WalletTransaction::class);
    }

    public function deposit(float $amount, array $meta = []): WalletTransaction
    {
        $transaction = $this->transactions()->create([
            'type' => 'deposit',
            'amount' => $amount,
            'status' => 'completed',
            'metadata' => $meta,
        ]);

        $this->balance = $this->balance + $amount;
        $this->save();

        return $transaction;
    }

    public function charge(float $amount, array $meta = []): ?WalletTransaction
    {
        if ($this->balance < $amount) {
            return null;
        }

        $transaction = $this->transactions()->create([
            'type' => 'purchase',
            'amount' => $amount,
            'status' => 'completed',
            'metadata' => $meta,
        ]);

        $this->balance = $this->balance - $amount;
        $this->save();

        return $transaction;
    }
}
