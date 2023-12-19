<?php

/*********************************************************************
 * This Code is licensed under the GPL-3.0 License and is Part of a
 * ILIAS Plugin developed by sr solutions ag in Switzerland.
 *
 * https://sr.solutions
 *
 *********************************************************************/

declare(strict_types=1);

namespace srag\Plugins\SrExternalPageContent\Settings;

/**
 * @author Fabian Schmid <fabian@sr.solutions>
 */
class Settings
{
    private SettingsRepository $settings_repository;

    private array $cache = [];

    public function __construct(SettingsRepository $settings_repository)
    {
        $this->settings_repository = $settings_repository;
    }

    /**
     * @param mixed $default
     * @return mixed
     */
    public function get(string $keyword, $default)
    {
        if (isset($this->cache[$keyword])) {
            return $this->cache[$keyword];
        }

        $setting = $this->settings_repository->getByKeyword($keyword);
        $var = $setting !== null ? $setting->getValue() : $default;
        return $this->cache[$keyword] = $var;
    }

    /**
     * @param mixed $value
     * @return mixed
     */
    public function set(string $keyword, $value)
    {
        $setting = $this->settings_repository->getByKeyword($keyword) ?? new Setting($keyword, $value);
        $setting->setValue($value);
        $this->settings_repository->store($setting);
        $this->cache[$keyword] = $value;

        return $value;
    }
}
