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

use srag\Plugins\SrExternalPageContent\Translator;
use srag\Plugins\SrExternalPageContent\Content\Embeddable;
use ILIAS\Data\URI;

/**
 * @author Fabian Schmid <fabian@sr.solutions>
 */
abstract class BaseRenderer
{
    public function __construct(protected Translator $translator)
    {
    }

    protected function wrap(Embeddable $embeddable, string $content): string
    {
        $url = $embeddable->getUrl();
        $uri = new URI($url);

        // content wrapper, we will move that later if there are other renderers
        $wrapper = new \ilTemplate(__DIR__ . '/../../templates/default/tpl.content_wrapper.html', false, false);
        $wrapper->setVariable('INFO', sprintf($this->translator->txt('before_load_info'), $uri->getHost()));
        $wrapper->setVariable('BUTTON_TEXT', $this->translator->txt('before_load_button'));
        $wrapper->setVariable('CONTENT', $content);
        $wrapper->setVariable('WIDTH', $embeddable->getWidth());
        $wrapper->setVariable('HEIGHT', $embeddable->getHeight());
        $wrapper->setVariable('RESPONSIVE', $embeddable->isResponsive());
        $wrapper->setVariable('CONSENT', '1');
        $wrapper->setVariable('CONSENTED', '0');
        $wrapper->setVariable('CONTENT_ID', 'srepc_' . $embeddable->getId());

        foreach ($embeddable->getScripts() as $script) {
            $wrapper->setCurrentBlock('script');
            $wrapper->setVariable('SCRIPT', $script);
            $wrapper->parseCurrentBlock();
        }

        return $wrapper->get();
    }

}
