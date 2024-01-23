<?php

namespace srag\Plugins\SrExternalPageContent\Forms;

use ILIAS\UI\Component\Input\Field\Section;

class IFrameSection extends Base implements FormElement
{
    private const F_URL = 'url';

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
        return $this->plugin->txt('iframe_section_title');
    }

    public function getInputs(): array
    {
        $inputs = [];

        $factory = $this->ui_factory->input()->field();

        $inputs[] = $factory
            ->text(
                $this->plugin->txt(self::F_URL),
                $this->plugin->txt(self::F_URL . '_info')
            )->withRequired(true)
            ->withAdditionalTransformation(
                $this->refinery->custom()->constraint(function ($d) {
                    return false;
                }, $this->plugin->txt('msg_validation_failed'))
            );

        $inputs[] = $factory->text(
            $this->plugin->txt('title'),
            $this->plugin->txt('title_info')
        );

        $inputs[] = $factory->numeric(
            $this->plugin->txt('width'),
            $this->plugin->txt('with_info')
        );

        $inputs[] = $factory->numeric(
            $this->plugin->txt('height'),
            $this->plugin->txt('height_info')
        );

        $inputs[] = $factory->numeric(
            $this->plugin->txt('frameborder'),
            $this->plugin->txt('frameborder_info')
        );

        $allow_options = [
            'autoplay',
            'fullscreen',
            'picture-in-picture',
            'accelerometer',
            'clipboard-write',
            'encrypted-media',
            'gyroscope',
            'web-share'
        ];
        $inputs[] = $factory->multiSelect(
            $this->plugin->txt('allow'),
            $allow_options,
            $this->plugin->txt('allow_info')
        );

        return $inputs;
    }

}
