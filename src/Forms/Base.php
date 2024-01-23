<?php

namespace srag\Plugins\SrExternalPageContent\Forms;

use ILIAS\UI\Component\Input\Field\Section;
use ILIAS\UI\Implementation\Component\Input\Field\Input;
use ReflectionClass;
use ILIAS\UI\Component\Input\Field\Group;

abstract class Base implements FormElement
{
    /**
     * @var \ILIAS\Refinery\Factory
     */
    protected $refinery;
    /**
     * @var \ilSrExternalPageContentPlugin
     */
    protected $plugin;

    /**
     * @var \ILIAS\UI\Factory
     */
    protected $ui_factory;

    public function __construct(
        \ilSrExternalPageContentPlugin $plugin
    ) {
        global $DIC;
        $this->ui_factory = $DIC->ui()->factory();
        $this->plugin = $plugin;
        $this->refinery = $DIC->refinery();
    }

    public function getSection(): Section
    {
        return $this->ui_factory->input()->field()->section(
            $this->getInputs(),
            $this->getSectionTitle()
        );
    }

    public function getGroup(): Group
    {
        return $this->ui_factory->input()->field()->group($this->getInputs(), $this->getSectionTitle());
    }

    abstract protected function getSectionTitle(): string;

    /**
     * @description ATTENTION: WE ARE NOW RESETTING ALL TRAFOS ON THE INPUT TO AVOID THE ALREADY GIVEN STRIP_TAGS TRAFO!
     */
    protected function makeInputHTMLAware(\ILIAS\UI\Component\Input\Field\Input $input): void
    {
        $reflection = new ReflectionClass(Input::class);
        $operations_property = $reflection->getProperty('operations');
        $operations_property->setAccessible(true);
        $operations_property->setValue($input, []);
    }

}
