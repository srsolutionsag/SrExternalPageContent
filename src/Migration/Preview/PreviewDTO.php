<?php

/*********************************************************************
 * This Code is licensed under the GPL-3.0 License and is Part of a
 * ILIAS Plugin developed by sr solutions ag in Switzerland.
 *
 * https://sr.solutions
 *
 *********************************************************************/

declare(strict_types=1);

namespace srag\Plugins\SrExternalPageContent\Migration\Preview;

use srag\Plugins\SrExternalPageContent\Content\iFrame;
use srag\Plugins\SrExternalPageContent\Content\iFramePreview;
use srag\Plugins\SrExternalPageContent\DIC;

/**
 * @author Fabian Schmid <fabian@sr.solutions>
 */
class PreviewDTO
{
    public static function sleep(iFrame $iframe): string
    {
        $encoded = json_encode([
            'id' => $iframe->getId(),
            'url' => $iframe->getUrl(),
            'properties' => $iframe->getProperties(),
            'scripts' => $iframe->getScripts()
        ]);

        return bin2hex($encoded);
    }

    public static function wakeup(string $iframe_data): ?iFrame
    {
        global $sepcContainer;
        /** @var DIC $sepcContainer */

        try {
            $iframe_data = json_decode(hex2bin($iframe_data), true, 512, JSON_THROW_ON_ERROR);
        } catch (\Throwable $e) {
            return null;
        }
        return new iFramePreview(
            $iframe_data['id'],
            $iframe_data['url'],
            $sepcContainer->dimensions()->default(),
            $iframe_data['properties'],
            $iframe_data['scripts']
        );
    }

}
