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
use srag\Plugins\SrExternalPageContent\Whitelist\Check;
use srag\Plugins\SrExternalPageContent\Content\NotEmbeddable;
use srag\Plugins\SrExternalPageContent\Translator;

/**
 * @author Fabian Schmid <fabian@sr.solutions>
 */
class RendererFactory
{
    private Translator $translator;
    protected Check $check;

    public function __construct(
        Check $check,
        Translator $translator
    ) {
        $this->translator = $translator;
        $this->check = $check;
    }

    public function getFor(Embeddable $embeddable, bool $presentation_mode): Renderer
    {
        if (!$presentation_mode) {
            return new EditPlaceholderRenderer($this->translator);
        }

        // check the URL of the embeddable object against whitelist
        if ($embeddable instanceof NotEmbeddable || $this->check->isAllowed($embeddable->getUrl()) === false) {
            return new NotEmbeddableRenderer($this->translator);
        }

        if ($embeddable instanceof iFrame) {
            return new iFrameRenderer($this->translator);
        }

        return new UnknownRenderer($this->translator);
    }
}
