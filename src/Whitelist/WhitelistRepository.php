<?php

/*********************************************************************
 * This Code is licensed under the GPL-3.0 License and is Part of a
 * ILIAS Plugin developed by sr solutions ag in Switzerland.
 *
 * https://sr.solutions
 *
 *********************************************************************/

declare(strict_types=1);

namespace srag\Plugins\SrExternalPageContent\Whitelist;

/**
 * @author Fabian Schmid <fabian@sr.solutions>
 */
interface WhitelistRepository
{
    public function blank(): WhiteListedDomain;

    public function total(): int;

    public function has(int $id): bool;

    public function store(WhitelistedDomain $domain): WhitelistedDomain;

    public function deleteById(int $id): void;

    public function getById(int $id): ?WhitelistedDomain;

    /**
     * @return WhitelistedDomain[]
     */
    public function getPossibleMatches(string $domain): array;

    /**
     * @return WhitelistedDomain[]
     */
    public function getPossibleMatchesIncludingInactive(string $domain): array;

    /**
     * @return WhitelistedDomain[]
     */
    public function getAll(): array;
}
