<?php

namespace App\Services\Frontend;

use App\Models\Setting;

class ExperienceSettings
{
    public const DEFAULTS = [
        'palette' => 'emerald',
        'width' => 1180,
        'font_size' => 16,
        'metadata' => true,
        'views' => true,
        'likes' => true,
        'sharing' => true,
        'facebook' => true,
        'whatsapp' => true,
        'telegram' => true,
        'x' => true,
        'copy' => true,
    ];

    public function all(): array
    {
        $raw = Setting::where('key', 'frontend_experience')->value('value');
        $saved = json_decode($raw ?? '{}', true);

        $values = array_replace(
            self::DEFAULTS,
            is_array($saved) ? array_intersect_key($saved, self::DEFAULTS) : []
        );

        $values['palette'] = in_array(
            $values['palette'],
            ['emerald', 'teal', 'slate'],
            true
        ) ? $values['palette'] : 'emerald';

        $values['width'] = in_array((int) $values['width'], [1100, 1180, 1280], true)
            ? (int) $values['width'] : 1180;

        $values['font_size'] = in_array((int) $values['font_size'], [15, 16, 17], true)
            ? (int) $values['font_size'] : 16;

        foreach (self::DEFAULTS as $key => $default) {
            if (is_bool($default)) {
                $values[$key] = filter_var($values[$key], FILTER_VALIDATE_BOOLEAN);
            }
        }

        return $values;
    }

    public function save(array $values): void
    {
        Setting::updateOrCreate(
            ['key' => 'frontend_experience'],
            [
                'group' => 'frontend_experience',
                'value' => json_encode($values, JSON_THROW_ON_ERROR),
            ]
        );
    }
}
