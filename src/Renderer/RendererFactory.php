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
use srag\Plugins\SrExternalPageContent\Settings\Settings;
use srag\Plugins\SrExternalPageContent\Content\iFramePreview;

/**
 * @author Fabian Schmid <fabian@sr.solutions>
 */
class RendererFactory
{
    private Translator $translator;
    private Settings $settings;
    private Check $check;

    public function __construct(
        Check $check,
        Translator $translator,
        Settings $settings
    ) {
        $this->translator = $translator;
        $this->check = $check;
        $this->settings = $settings;
    }

    public function getFor(Embeddable $embeddable, bool $presentation_mode): Renderer
    {
        // we always want to render the iFramePreview
        if ($embeddable instanceof iFramePreview) {
            return new iFrameRenderer($this->translator, $this->check, $this->settings);
        }

        // check the URL of the embeddable object against whitelist
        if ($embeddable instanceof NotEmbeddable || $this->check->isAllowed($embeddable->getUrl()) === false) {
            return new NotEmbeddableRenderer($this->translator, $this->check, $this->settings);
        }

        if (!$presentation_mode) {
            return new EditPlaceholderRenderer($this->translator, $this->check, $this->settings);
        }
        
        if ($embeddable instanceof iFrame) {
            return new iFrameRenderer($this->translator, $this->check, $this->settings);
        }

        return new UnknownRenderer($this->translator, $this->check, $this->settings);
    }
}
