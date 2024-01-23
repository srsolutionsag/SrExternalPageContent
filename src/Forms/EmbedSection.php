<?php

namespace srag\Plugins\SrExternalPageContent\Forms;

use ILIAS\UI\Component\Input\Field\Section;

class EmbedSection extends Base implements FormElement
{
    private const F_EMBED_CONTENT = 'embed_content';

    /**
     * @var array
     */
    protected $properties;

    public function __construct(
        \ilSrExternalPageContentPlugin $plugin,
        array $properties = []
    ) {
        parent::__construct($plugin);
        $this->properties = $properties;
    }

    protected function getSectionTitle(): string
    {
        return $this->plugin->txt('embed_section_title');
    }



    public function getInputs(): array
    {
        $textarea = $this->ui_factory->input()->field()->textarea(
            $this->plugin->txt(self::F_EMBED_CONTENT),
            $this->plugin->txt(self::F_EMBED_CONTENT . '_info'),
        );

        $this->makeInputHTMLAware($textarea);

        return [
            $textarea->withValue($properties[self::F_EMBED_CONTENT] ?? '')
                     ->withAdditionalTransformation(
                         $this->refinery->custom()->transformation(function ($value) use ($properties) {
                             return [self::F_EMBED_CONTENT => $value] + $properties;
                         })
                     ),
        ];
    }

}
