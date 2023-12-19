<?php

/*********************************************************************
 * This Code is licensed under the GPL-3.0 License and is Part of a
 * ILIAS Plugin developed by sr solutions ag in Switzerland.
 *
 * https://sr.solutions
 *
 *********************************************************************/

declare(strict_types=1);

namespace srag\Plugins\SrExternalPageContent\Renderer;

use srag\Plugins\SrExternalPageContent\Content\Embeddable;
use srag\Plugins\SrExternalPageContent\Content\iFrame;
use ILIAS\Data\URI;

/**
 * @author Fabian Schmid <fabian@sr.solutions>
 */
class iFrameRenderer extends BaseRenderer implements Renderer
{
    public function render(Embeddable $embeddable): string
    {
        if (!$embeddable instanceof iFrame) {
            throw new \InvalidArgumentException('Embeddable must be an instance of iFrame');
        }

        $url = $embeddable->getUrl();
        $uri = new URI($url);

        $tpl = new \ilTemplate(__DIR__ . '/../../templates/default/tpl.iframe.html', false, false);
        $tpl->setVariable('URL', $url);
        $tpl->setVariable('TITLE', $embeddable->getTitle());
        $tpl->setVariable('FRAMEBORDER', $embeddable->getFrameborder());
        $tpl->setVariable('ALLOWFULLSCREEN', $embeddable->isAllowfullscreen());
        $tpl->setVariable('CONTENT_ID', 'srepc_' . $embeddable->getId());

        return $this->wrap($embeddable, $tpl->get());
    }
}
