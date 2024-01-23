<?php

namespace srag\Plugins\SrExternalPageContent\Forms;

use ILIAS\UI\Component\Input\Field\Section;

class ContentCreation extends Base implements FormElement
{
    private const F_EXTERNAL_CONTENT = 'external_content';

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
        return $this->plugin->txt('form_title');
    }

    public function getSection(): Section
    {
        return $this->ui_factory->input()->field()->section(
            $this->getInputs(),
            $this->getSectionTitle()
        );
    }

    public function getInputs(): array
    {
        $embed = new EmbedSection($this->plugin);
        $iframe = new IFrameSection($this->plugin);

        $inputs = [
            $this->ui_factory->input()->field()->switchableGroup(
                [
                    'embed' => $embed->getGroup(),
                    'iframe' => $iframe->getGroup()
                ],
                $this->getSectionTitle()
            )->withValue('embed')
        ];
        return $inputs;
    }

}
