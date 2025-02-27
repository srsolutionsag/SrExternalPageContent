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

/**
 * @author Fabian Schmid <fabian@sr.solutions>
 */
class EditPlaceholderRenderer extends BaseRenderer implements Renderer
{
    public function render(Embeddable $embeddable): string
    {
        $url = $embeddable->getUrl();
        $uri = new URI($url);

        $wrapper = new \ilTemplate(__DIR__ . '/../../templates/default/tpl.edit_placeholder.html', false, false);
        $wrapper->setVariable('INFO', sprintf($this->translator->txt('edit_content_info'), $uri->getHost()));
        $wrapper->setVariable('DIMENSIONS', $this->dimensions->forJS($embeddable->getDimension()));
        $wrapper->setVariable('CONTENT_ID', 'srepc_' . $embeddable->getId());

        return $wrapper->get();
    }
}
