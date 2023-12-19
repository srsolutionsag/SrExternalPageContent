<?php

/*********************************************************************
 * This Code is licensed under the GPL-3.0 License and is Part of a
 * ILIAS Plugin developed by sr solutions ag in Switzerland.
 *
 * https://sr.solutions
 *
 *********************************************************************/

declare(strict_types=1);

namespace srag\Plugins\SrExternalPageContent\Helper;

/**
 * @author Fabian Schmid <fabian@sr.solutions>
 */
class Sanitizer
{
    public function sanitizeEncoding(string $string): string
    {
        return mb_convert_encoding($string, 'UTF-8', mb_detect_encoding($string) ?: 'ASCII');
    }

    public function sanitizeURL(string $string): string
    {
        $string = mb_convert_encoding($string, 'HTML-ENTITIES', 'UTF-8');
        $string = mb_convert_encoding($string, 'ASCII', 'UTF-8');
        return (string) filter_var($string, FILTER_SANITIZE_URL);
    }

}
