<?php

/*********************************************************************
 * This Code is licensed under the GPL-3.0 License and is Part of a
 * ILIAS Plugin developed by sr solutions ag in Switzerland.
 *
 * https://sr.solutions
 *
 *********************************************************************/

declare(strict_types=1);

namespace srag\Plugins\SrExternalPageContent\Forms;

use srag\Plugins\SrExternalPageContent\Content\iFrame;
use srag\Plugins\SrExternalPageContent\Content\Embeddable;
use srag\Plugins\SrExternalPageContent\Content\NotEmbeddable;
use srag\Plugins\SrExternalPageContent\DIC;

class FormBuilder
{
    private DIC $dependencies;

    public function __construct(DIC $dependencies)
    {
        $this->dependencies = $dependencies;
    }

    public function buildFor(?Embeddable $embeddable = null): FormElement
    {
        if ($embeddable === null) {
            return new ContentCreation($this->dependencies);
        }
        if ($embeddable instanceof iFrame) {
            return new IFrameSection($this->dependencies, $embeddable);
        }
        if ($embeddable instanceof NotEmbeddable) {
            return new NotEmbeddableSection($this->dependencies, $embeddable);
        }
        throw new \InvalidArgumentException(
            "No form available for type `" . ($embeddable !== null ? get_class($embeddable) : self::class) . "`"
        );
    }

}
