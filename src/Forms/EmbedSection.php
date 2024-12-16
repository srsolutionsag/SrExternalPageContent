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

use srag\Plugins\SrExternalPageContent\Content\Embeddable;
use srag\Plugins\SrExternalPageContent\Content\NotEmbeddable;
use ILIAS\Refinery\Transformation;

class EmbedSection extends Base implements FormElement
{
    private const F_EMBED_CONTENT = 'embed_content';
    private ?Embeddable $embeddable = null;

    protected function getSectionTitle(): string
    {
        return $this->translator->txt('embed_section_title');
    }

    public function getInputs(): array
    {
        $textarea = $this->ui_factory->input()->field()->textarea(
            $this->translator->txt(self::F_EMBED_CONTENT),
            $this->translator->txt(self::F_EMBED_CONTENT . '_info'),
        );

        $this->makeInputHTMLAware($textarea);

        return [
            $textarea->withValue($properties[self::F_EMBED_CONTENT] ?? '')
                     ->withAdditionalTransformation(
                         $this->refinery->constraint(
                             fn ($value): bool => !$this->parser->createParser($value)->parse(
                                 $value
                             ) instanceof NotEmbeddable,
                             $this->translator->txt('embed_content_invalid_content')
                         )
                     )
                     ->withAdditionalTransformation(
                         $this->refinery->trafo(
                             fn ($value): Embeddable => $this->embeddable = $this->parser->createParser($value)->parse(
                                 $value
                             )
                         )
                     )
                     ->withAdditionalTransformation(
                         $this->refinery->constraint(
                             function (Embeddable $value): bool {
                                 $silent_creation = $this->dependencies->settings()->get('silent_creation', false);
                                 if ($silent_creation) {
                                     return $this->whitelist_check->createSilently($value->getUrl());
                                 }

                                 return $this->whitelist_check->isAllowed($value->getUrl());
                             },
                             $this->translator->txt('embed_content_invalid_url')
                         )
                     )->withAdditionalTransformation($this->getFinalTransformation())
        ];
    }

    protected function getFinalTransformation(): Transformation
    {
        return $this->refinery->trafo(
            fn ($value): Embeddable => $this->embeddable_repository->store($this->embeddable)
        );
    }

    public function getEmebeddable(): ?Embeddable
    {
        return $this->embeddable;
    }

}
