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
use ILIAS\Data\URI;
use srag\Plugins\SrExternalPageContent\Content\NotEmbeddable;

/**
 * @author Fabian Schmid <fabian@sr.solutions>
 */
class NotEmbeddableRenderer extends BaseRenderer implements Renderer
{
    public function render(Embeddable $embeddable): string
    {
        $url = $embeddable->getUrl();
        try {
            $uri = new URI($url);
            $host = $uri->getHost();
        } catch (\Throwable $e) {
            $host = $url;
        }

        // content wrapper, we will move that later if there are other renderers
        $wrapper = new \ilTemplate(__DIR__ . '/../../templates/default/tpl.placeholder.html', false, false);
        $reason = $this->translator->txt('reason_unknown');
        if ($embeddable instanceof NotEmbeddable) {
            $reason = $this->translator->txt('reason_' . $embeddable->getReason());
        }
        $wrapper->setVariable('INFO', sprintf($this->translator->txt('not_embeddable_info'), $host, $reason));
        $wrapper->setVariable('WIDTH', $embeddable->getWidth());
        $wrapper->setVariable('HEIGHT', $embeddable->getHeight());
        $wrapper->setVariable('RESPONSIVE', $embeddable->isResponsive());
        $wrapper->setVariable('CONTENT_ID', 'srepc_' . $embeddable->getId());

        return $wrapper->get();
    }
}
