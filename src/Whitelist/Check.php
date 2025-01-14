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
class Check
{
    private WhitelistRepository $repository;
    private DomainParser $parser;

    private array $cache = [];

    public function __construct(WhitelistRepository $repository, DomainParser $parser)
    {
        $this->repository = $repository;
        $this->parser = $parser;
    }

    public function createSilently(string $url): bool
    {
        if ($this->isAllowed($url)) {
            return true;
        }

        $extracted_host = $this->parser->extractHost($url);
        if ($extracted_host === null) {
            return false;
        }

        // possible matches
        $extracted_domain = $this->parser->extractDomain($extracted_host);
        $matches = $this->repository->getPossibleMatchesIncludingInactive($extracted_domain);

        if ($matches !== []) {
            return true;
        }

        $whitelist = $this->repository->blank()->withDomain($extracted_host)->withStatus(Status::STATUS_INACTIVE);

        $this->repository->store($whitelist);

        return true;
    }

    public function isAllowed(string $url): bool
    {
        $extracted_host = $this->parser->extractHost($url);
        if ($extracted_host === null) {
            return false;
        }

        if (isset($this->cache[$extracted_host])) {
            return $this->cache[$extracted_host];
        }

        // possible matches
        $extracted_domain = $this->parser->extractDomain($extracted_host);
        $matches = $this->repository->getPossibleMatches($extracted_domain);

        foreach ($matches as $match) {
            $whitelisted_domain = $match->getDomain(); // may contain wildcards *, which we need to replace for regex
            $whitelisted_domain = str_replace('*', '.*', $whitelisted_domain);
            $whitelisted_domain = '/^' . $whitelisted_domain . '$/';
            if (preg_match($whitelisted_domain, $extracted_host)) {
                return $this->cache[$extracted_host] = true;
            }
        }

        return $this->cache[$extracted_host] = false;
    }

    public function getBest(string $url): ?WhitelistedDomain
    {
        $extracted_host = $this->parser->extractHost($url);
        if ($extracted_host === null) {
            return null;
        }

        // possible matches
        $extracted_domain = $this->parser->extractDomain($extracted_host);
        $matches = $this->repository->getPossibleMatches($extracted_domain);

        return $matches[0] ?? null;
    }
}
