<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;

class Setting extends Model
{
    use HasFactory;

    protected $table = 'settings';

    protected $fillable = [
        'key',
        'value',
    ];

    /**
     * Valores padrao equivalentes a DEFAULTS em src/lib/settingsHelper.jsx do front,
     * usados pelo SettingSeeder e como fallback caso uma chave nao exista.
     */
    public const DEFAULTS = [
        'site_title' => '5º Acampamento de Jovens Ignis',
        'site_subtitle' => 'Paróquia São Pedro Apóstolo',
        'event_date' => '2026-08-02',
        'event_location' => 'Paróquia São Pedro Apóstolo',
        'max_spots' => '150',
        'registration_value' => '150',
        'pix_key' => '',
        'pix_name' => '',
        'coordinator_email' => '',
        'registration_open' => 'true',
        'hero_image_url' => '',
        'instagram_url' => 'https://www.instagram.com/ignis_acamp',
    ];

    /** Retorna todas as settings como um array associativo key => value (com defaults). */
    public static function allAsArray(): array
    {
        $stored = static::query()->pluck('value', 'key')->toArray();

        return array_merge(self::DEFAULTS, $stored);
    }

    public static function get(string $key, ?string $default = null): ?string
    {
        return static::query()->where('key', $key)->value('value')
            ?? $default
            ?? self::DEFAULTS[$key]
            ?? null;
    }

    public static function set(string $key, string $value): void
    {
        static::query()->updateOrCreate(['key' => $key], ['value' => $value]);
        Cache::forget('settings.all');
    }
}
