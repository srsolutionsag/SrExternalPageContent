<?php

/*********************************************************************
 * This Code is licensed under the GPL-3.0 License and is Part of a
 * ILIAS Plugin developed by sr solutions ag in Switzerland.
 *
 * https://sr.solutions
 *
 *********************************************************************/

declare(strict_types=1);

namespace srag\Plugins\SrExternalPageContent;

use ILIAS\UI\Factory;
use ILIAS\UI\Renderer;

/**
 * @author Fabian Schmid <fabian@sr.solutions>
 */
abstract class BaseUIComponent
{
    protected DIC $dependecies;
    /**
     * @readonly
     */
    protected Renderer $renderer;
    /**
     * @readonly
     */
    protected Translator $translator;
    /**
     * @readonly
     */
    protected Factory $factory;

    public function __construct(
        DIC $dependecies
    ) {
        $this->dependecies = $dependecies;
        $this->renderer = $this->dependecies->ilias()->ui()->renderer();
        $this->translator = $this->dependecies->translator();
        $this->factory = $this->dependecies->ilias()->ui()->factory();
    }

    abstract public function getHTML(): string;

}
