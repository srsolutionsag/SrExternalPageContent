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
trait Hasher
{
    protected function hash(string $string): string
    {
        // zip
        $string = gzdeflate($string, 9);
        $string = rtrim(str_replace(['+', '/'], ['-', '_'], base64_encode($string)), '=');

        return $string;
    }

    protected function unhash(string $string): string
    {
        $string = base64_decode(str_replace(['-', '_'], ['+', '/'], $string . '=='));
        $string = gzinflate($string);

        return $string;
    }

}
