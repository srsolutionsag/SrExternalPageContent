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

class ContentCreation extends Base implements FormElement
{
    protected function getSectionTitle(): string
    {
        return $this->translator->txt('form_title');
    }

    public function getInputs(): array
    {
        $embed = new EmbedSection($this->dependencies);
        $iframe = new IFrameSection(
            $this->dependencies,
            new iFrame(0, '')
        );
        $ff = $this->ui_factory->input();
        return [
            $ff->field()
               ->switchableGroup(
                   [
                       'embed' => $embed->getGroup(),
                       'iframe' => $iframe->getGroup()
                   ],
                   $this->getSectionTitle()
               )
               ->withValue('embed')
               ->withAdditionalTransformation(
                   $this->reduceToFirstEmbeddable()
               )
        ];
    }

}
