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
interface SettingsRepository
{
    public function total(): int;

    public function has(string $keyword): bool;

    public function store(Setting $domain): Setting;

    public function deleteById(string $keyword): void;

    public function getById(string $keyword): ?Setting;
}
