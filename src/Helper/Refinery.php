<?php

/*********************************************************************
 * This Code is licensed under the GPL-3.0 License and is Part of a
 * ILIAS Plugin developed by sr solutions ag in Switzerland.
 *
 * https://sr.solutions
 *
 *********************************************************************/

declare(strict_types=1);

namespace srag\Plugins\SrExternalPageContent\Helper;

use ILIAS\Refinery\Factory;
use ILIAS\Refinery\Constraint;
use ILIAS\Refinery\Transformation;

/**
 * @author Fabian Schmid <fabian@sr.solutions>
 */
class Refinery
{
    private Factory $refinery_factory;

    public function __construct(Factory $refinery_factory)
    {
        $this->refinery_factory = $refinery_factory;
    }

    public function constraint(callable $fn, string $error): Constraint
    {
        return $this->refinery_factory->custom()->constraint($fn, $error);
    }

    public function trafo(callable $fn): Transformation
    {
        return $this->refinery_factory->custom()->transformation($fn);
    }
}
