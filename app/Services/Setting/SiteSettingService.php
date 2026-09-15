<?php

namespace App\Services\Setting;

use App\Models\Media;
use App\Models\Setting;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class SiteSettingService
{
    private const CACHE_KEY = 'site-settings.values';

    private ?array $resolved = null;

    private bool $logoResolved = false;

    private ?Media $resolvedLogo = null;

    public function definitions(): array
    {
        return config(
            'site-settings.groups',
            []
        );
    }

    public function defaults(): array
    {
        $defaults = [];

        foreach ($this->definitions() as $group) {
            foreach (
                $group['fields'] ?? []
                as $key => $field
            ) {
                $defaults[$key] =
                    $field['default']
                    ?? null;
            }
        }

        return $defaults;
    }

    public function values(): array
    {
        if (! Schema::hasTable('settings')) {
            return [];
        }

        return Cache::rememberForever(
            self::CACHE_KEY,
            fn () => Setting::query()
                ->pluck('value', 'key')
                ->all()
        );
    }

    public function all(): array
    {
        if ($this->resolved !== null) {
            return $this->resolved;
        }

        return $this->resolved = array_replace(
            $this->defaults(),
            $this->values()
        );
    }

    public function get(
        string $key,
        mixed $default = null
    ): mixed {
        $values = $this->all();

        return array_key_exists($key, $values)
            ? $values[$key]
            : $default;
    }

    public function logo(): ?Media
    {
        if ($this->logoResolved) {
            return $this->resolvedLogo;
        }

        $this->logoResolved = true;

        $logoMediaId = (int) $this->get(
            'logo_media_id',
            0
        );

        if ($logoMediaId <= 0) {
            return null;
        }

        return $this->resolvedLogo =
            Media::query()
                ->whereKey($logoMediaId)
                ->where('type', 'image')
                ->where('is_public', true)
                ->first();
    }

    public function update(array $values): void
    {
        $groups = $this->keyGroupMap();

        DB::transaction(
            function () use (
                $values,
                $groups
            ) {
                foreach ($values as $key => $value) {
                    if (
                        ! array_key_exists(
                            $key,
                            $groups
                        )
                    ) {
                        continue;
                    }

                    Setting::query()->updateOrCreate(
                        [
                            'key' => $key,
                        ],
                        [
                            'group' => $groups[$key],
                            'value' => $value,
                        ]
                    );
                }
            }
        );

        $this->forgetCache();
    }

    public function forgetCache(): void
    {
        Cache::forget(
            self::CACHE_KEY
        );

        $this->resolved = null;

        $this->logoResolved = false;

        $this->resolvedLogo = null;
    }

    private function keyGroupMap(): array
    {
        $map = [];

        foreach (
            $this->definitions()
            as $groupKey => $group
        ) {
            foreach (
                $group['fields'] ?? []
                as $key => $field
            ) {
                $map[$key] = $groupKey;
            }
        }

        return $map;
    }
}
