<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

class RestaurantTable extends Model
{
    protected $fillable = ['code', 'capacity', 'zone', 'status', 'qr_token'];

    protected static function booted(): void
    {
        static::creating(function (RestaurantTable $table) {
            if (empty($table->qr_token)) {
                $table->qr_token = static::generateToken();
            }
        });
    }

    public static function generateToken(): string
    {
        do {
            $token = Str::lower(Str::random(24));
        } while (static::query()->where('qr_token', $token)->exists());

        return $token;
    }

    public function ensureQrToken(): string
    {
        if (empty($this->qr_token)) {
            $this->forceFill(['qr_token' => static::generateToken()])->save();
        }

        return (string) $this->qr_token;
    }

    public function refreshQrToken(): string
    {
        $this->forceFill(['qr_token' => static::generateToken()])->save();

        return (string) $this->qr_token;
    }

    public function qrOrderUrl(): string
    {
        return url('/qr/'.$this->ensureQrToken());
    }

    public function qrImageUrl(int $size = 280): string
    {
        return 'https://api.qrserver.com/v1/create-qr-code/?size='.$size.'x'.$size.'&data='.urlencode($this->qrOrderUrl());
    }

    public function orders(): HasMany
    {
        return $this->hasMany(Order::class, 'table_id');
    }

    public function reservations(): HasMany
    {
        return $this->hasMany(Reservation::class, 'table_id');
    }
}
