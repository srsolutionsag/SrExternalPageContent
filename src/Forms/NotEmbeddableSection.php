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

use srag\Plugins\SrExternalPageContent\DIC;
use ILIAS\Refinery\Transformation;
use srag\Plugins\SrExternalPageContent\Content\Embeddable;
use srag\Plugins\SrExternalPageContent\Content\NotEmbeddable;

class NotEmbeddableSection extends Base implements FormElement
{
    protected NotEmbeddable $embeddable;
    private const F_URL = 'url';

    public function __construct(
        DIC $dependencies,
        NotEmbeddable $embeddable
    ) {
        $this->embeddable = $embeddable;
        parent::__construct($dependencies);
    }

    protected function getSectionTitle(): string
    {
        return $this->translator->txt('not_embeddable_section_title');
    }

    public function getInputs(): array
    {
        return [];
    }

    protected function getFinalTransformation(): Transformation
    {
        return $this->refinery->trafo(
            fn($value): Embeddable => $this->embeddable
        );
    }

}
