<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SystemConfiguration extends Model
{
    use HasFactory;

    public $timestamps = true;

    const CREATED_AT = null;

    protected $fillable = [
        'key',
        'value',
        'type',
        'description',
    ];

    /**
     * Get typed configuration value by key.
     */
    public static function getValue(string $key, mixed $default = null): mixed
    {
        $config = static::where('key', $key)->first();

        if (! $config) {
            return $default;
        }

        return match ($config->type) {
            'integer' => (int) $config->value,
            'decimal' => (float) $config->value,
            'boolean' => filter_var($config->value, FILTER_VALIDATE_BOOLEAN),
            'json' => json_decode($config->value, true),
            default => $config->value,
        };
    }

    /**
     * Set or update configuration key-value.
     */
    public static function setValue(string $key, mixed $value, string $type = 'string', ?string $description = null): self
    {
        $stringValue = match ($type) {
            'boolean' => $value ? '1' : '0',
            'json' => is_string($value) ? $value : json_encode($value),
            default => (string) $value,
        };

        return static::updateOrCreate(
            ['key' => $key],
            [
                'value' => $stringValue,
                'type' => $type,
                'description' => $description,
            ]
        );
    }
}
