<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Str;

class UserCreditCard extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'type',
        'number',
        'name',
        'expired',
        'cvv'
    ];

    /**
     * Interact with the user's password.
     *
     * @return  \Illuminate\Database\Eloquent\Casts\Attribute
     */
    protected function numberLimit(): Attribute
    {
        return Attribute::make(
            get: fn() => Str::substr($this->number, 12)
        );
    }

    /**
     * Get the user that owns the UserCreditCard
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
