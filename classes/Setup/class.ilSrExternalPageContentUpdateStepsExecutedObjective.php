<?php

/*********************************************************************
 * This Code is licensed under the GPL-3.0 License and is Part of a
 * ILIAS Plugin developed by sr solutions ag in Switzerland.
 *
 * https://sr.solutions
 *
 *********************************************************************/

use ILIAS\Setup\Environment;
use ILIAS\Setup\ObjectiveCollection;

/**
 * @author Fabian Schmid <fabian@sr.solutions>
 */
class ilSrExternalPageContentUpdateStepsExecutedObjective extends ilDatabaseUpdateStepsExecutedObjective
{
    private ObjectiveCollection $precondition;

    public function __construct(ObjectiveCollection $precondition, ilDatabaseUpdateSteps $steps)
    {
        $this->precondition = $precondition;
        parent::__construct($steps);
    }

    public function getPreconditions(Environment $environment): array
    {
        return array_merge([$this->precondition], parent::getPreconditions($environment));
    }

}
