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

/**
 * @author Fabian Schmid <fabian@sr.solutions>
 */
class URLTranslator
{
    public function translate(string $original): string
    {
        // convert normal youtune URLs to embed URLs
        if (preg_match('/youtube\.com\/watch\?v=([a-zA-Z0-9_-]+)/', $original, $matches)) {
            return 'https://www.youtube.com/embed/' . $matches[1];
        }
        return $original;
    }
}
