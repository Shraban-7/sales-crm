<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;

class SystemSetting extends Model
{
    use HasFactory;

    public const CACHE_KEY = 'crm_system_settings_cache';

    /**
     * @var list<string>
     */
    protected $fillable = [
        'key',
        'value',
        'group',
        'type',
        'description',
    ];

    public static function get(string $key, mixed $default = null): mixed
    {
        $all = static::getAllCached();

        if (array_key_exists($key, $all)) {
            return $all[$key];
        }

        return $default;
    }

    public static function set(string $key, mixed $value, string $group = 'general', string $type = 'string'): self
    {
        $setting = static::query()->firstOrNew(['key' => $key]);
        $setting->group = $group;
        $setting->type = $type;

        if ($type === 'boolean') {
            $setting->value = filter_var($value, FILTER_VALIDATE_BOOLEAN) ? '1' : '0';
        } elseif ($type === 'json') {
            $setting->value = is_string($value) ? $value : json_encode($value);
        } else {
            $setting->value = (string) $value;
        }

        $setting->save();
        static::clearCache();

        return $setting;
    }

    /**
     * @return array<string, mixed>
     */
    public static function getAllCached(): array
    {
        return Cache::rememberForever(self::CACHE_KEY, function (): array {
            $settings = [];
            foreach (static::query()->get() as $item) {
                $settings[$item->key] = $item->castValue();
            }

            return $settings;
        });
    }

    public static function clearCache(): void
    {
        Cache::forget(self::CACHE_KEY);
    }

    public function castValue(): mixed
    {
        if ($this->value === null) {
            return null;
        }

        return match ($this->type) {
            'boolean' => filter_var($this->value, FILTER_VALIDATE_BOOLEAN),
            'integer' => (int) $this->value,
            'float' => (float) $this->value,
            'json' => json_decode($this->value, true),
            default => (string) $this->value,
        };
    }
}
