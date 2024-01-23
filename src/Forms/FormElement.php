<?php

namespace srag\Plugins\SrExternalPageContent\Forms;

use ILIAS\UI\Component\Input\Field\Section;
use ILIAS\UI\Component\Input\Field\Group;

interface FormElement
{
    public function getSection(): Section;
    public function getGroup(): Group;
    public function getInputs(): array;
}
