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

use ILIAS\UI\Factory;
use ILIAS\UI\Component\Input\Field\Section;
use ReflectionClass;
use ILIAS\UI\Component\Input\Field\Group;
use ILIAS\UI\Component\Input\Container\Form\FormInput;
use srag\Plugins\SrExternalPageContent\DIC;
use srag\Plugins\SrExternalPageContent\Translator;
use srag\Plugins\SrExternalPageContent\Helper\Refinery;
use srag\Plugins\SrExternalPageContent\Parser\ParserFactory;
use srag\Plugins\SrExternalPageContent\Whitelist\WhitelistRepository;
use srag\Plugins\SrExternalPageContent\Whitelist\Check;
use srag\Plugins\SrExternalPageContent\Content\EmbeddableRepository;
use ILIAS\Refinery\Transformation;
use srag\Plugins\SrExternalPageContent\Content\Embeddable;

abstract class Base implements FormElement
{
    protected EmbeddableRepository $embeddable_repository;
    protected Check $whitelist_check;
    protected DIC $dependencies;
    protected Translator $translator;
    protected Refinery $refinery;
    protected ParserFactory $parser;
    protected WhitelistRepository $whitelist_repository;

    protected Factory $ui_factory;

    public function __construct(
        DIC $dependencies
    ) {
        $this->dependencies = $dependencies;
        $this->translator = $dependencies->translator();
        $this->ui_factory = $dependencies->ilias()->ui()->factory();
        $this->refinery = $dependencies->refinery();
        $this->parser = $dependencies[ParserFactory::class];
        $this->whitelist_repository = $dependencies[WhitelistRepository::class];
        $this->whitelist_check = $dependencies[Check::class];
        $this->embeddable_repository = $dependencies[EmbeddableRepository::class];
    }

    public function getSection(): Section
    {
        return $this->ui_factory->input()->field()->section(
            $this->getInputs(),
            $this->getSectionTitle()
        )->withAdditionalTransformation(
            $this->reduceToFirstEmbeddable()
        );
    }

    public function getGroup(): Group
    {
        return $this->ui_factory->input()->field()->group(
            $this->getInputs(),
            $this->getSectionTitle()
        )->withAdditionalTransformation(
            $this->reduceToFirstEmbeddable()
        );
    }

    abstract protected function getSectionTitle(): string;

    protected function getFinalTransformation(): Transformation
    {
        return $this->refinery->trafo(
            fn ($data) => $data
        );
    }

    /**
     * @description ATTENTION: WE ARE NOW RESETTING ALL TRAFOS ON THE INPUT TO AVOID THE ALREADY GIVEN STRIP_TAGS TRAFO!
     */
    protected function makeInputHTMLAware(FormInput $input): void
    {
        $reflection = new ReflectionClass(\ILIAS\UI\Implementation\Component\Input\Field\FormInput::class);
        $operations_property = $reflection->getProperty('operations');
        $operations_property->setAccessible(true);
        $operations_property->setValue($input, []);
    }

    protected function reduceToFirstEmbeddable(): Transformation
    {
        return $this->refinery->trafo(
            function ($data): ?Embeddable {
                if ($data instanceof Embeddable) {
                    return $data;
                }
                // we must look through the array and find the first Embeddable
                $looper = static function ($value) use (&$looper): ?Embeddable {
                    if ($value instanceof Embeddable) {
                        return $value;
                    }
                    if (is_array($value)) {
                        foreach ($value as $item) {
                            $result = $looper($item);
                            if ($result instanceof Embeddable) {
                                return $result;
                            }
                        }
                    }
                    return null;
                };
                return $looper($data);
            }
        );
    }

}
