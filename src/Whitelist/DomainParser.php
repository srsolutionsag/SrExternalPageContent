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

use ILIAS\Data\URI;
use srag\Plugins\SrExternalPageContent\Helper\Sanitizer;

/**
 * @author Fabian Schmid <fabian@sr.solutions>
 */
class DomainParser
{
    private Sanitizer $sanitizer;

    public function __construct()
    {
        $this->sanitizer = new Sanitizer();
    }

    public function extractHost(string $url_string): ?string
    {
        $url_string = $this->sanitizer->sanitizeURL($url_string);
        try {
            $uri = new URI($url_string);
        } catch (\Throwable $e) {
            return $e->getMessage();
            return null;
        }

        return $uri->getHost();
    }

    public function extractDomain(string $host): ?string
    {
        $host = $this->sanitizer->sanitizeURL($host);
        $host = explode('.', $host);
        $host = array_slice($host, -2, 2);
        return implode('.', $host);
    }
}
