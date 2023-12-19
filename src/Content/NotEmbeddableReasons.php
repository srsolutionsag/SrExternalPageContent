<?php

/*********************************************************************
 * This Code is licensed under the GPL-3.0 License and is Part of a
 * ILIAS Plugin developed by sr solutions ag in Switzerland.
 *
 * https://sr.solutions
 *
 *********************************************************************/

declare(strict_types=1);

namespace srag\Plugins\SrExternalPageContent\Content;

class NotEmbeddableReasons
{
    public const NO_URL = 'no_url';
    public const NO_REASON = 'unknown';
    public const NOT_WHITELISTED = 'not_whitelisted';
}
