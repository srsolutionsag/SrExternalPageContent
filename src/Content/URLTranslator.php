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
    private const YOUTUBE_EMBED = 'https://www.youtube.com/embed/';
    private const YOUTUBE_ID = '([a-zA-Z0-9_-]{6,})';

    public function translate(string $original): string
    {
        // https://(www.|m.|music.)youtube.com/watch?v=<ID>[&...]
        if (preg_match('#youtube\.com/watch\?(?:[^\#]*&)?v=' . self::YOUTUBE_ID . '#', $original, $matches)) {
            return self::YOUTUBE_EMBED . $matches[1];
        }

        // https://youtu.be/<ID>[?si=...]
        if (preg_match('#^https?://(?:www\.)?youtu\.be/' . self::YOUTUBE_ID . '#', $original, $matches)) {
            return self::YOUTUBE_EMBED . $matches[1];
        }

        // https://(www.)youtube.com/shorts/<ID> und /live/<ID>
        if (preg_match('#youtube\.com/(?:shorts|live)/' . self::YOUTUBE_ID . '#', $original, $matches)) {
            return self::YOUTUBE_EMBED . $matches[1];
        }

        return $original;
    }
}
