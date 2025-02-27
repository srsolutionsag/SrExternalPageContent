<?php

/*********************************************************************
 * This Code is licensed under the GPL-3.0 License and is Part of a
 * ILIAS Plugin developed by sr solutions ag in Switzerland.
 *
 * https://sr.solutions
 *
 *********************************************************************/

declare(strict_types=1);

use ILIAS\Setup\Metrics\Storage;
use ILIAS\Setup\Config;
use ILIAS\Setup\Objective;
use ILIAS\Setup\ObjectiveCollection;
use ILIAS\Refinery\Factory;
use srag\Plugins\SrExternalPageContent\Migration\FullMigration;

/**
 * @author Fabian Schmid <fabian@sr.solutions>
 */
class ilSrExternalPageContentAgent extends ilPluginDefaultAgent
{
    private Factory $refinery;
    private \ILIAS\Data\Factory $data_factory;
    private \ilLanguage $lng;

    public function __construct(
        Factory $refinery,
        \ILIAS\Data\Factory $data_factory,
        \ilLanguage $lng
    ) {
        $this->refinery = $refinery;
        $this->data_factory = $data_factory;
        $this->lng = $lng;
        parent::__construct('SrExternalPageContent');
    }

    public function getStatusObjective(Storage $storage): Objective
    {
        return new \ilDatabaseUpdateStepsMetricsCollectedObjective(
            $storage,
            new ilSrExternalPageContentDBUpdateSteps()
        );
    }

    public function getInstallObjective(Config $config = null): Objective
    {
        $general_update = parent::getInstallObjective($config);

        return new ObjectiveCollection(
            'SrExternalPageContent-Plugin Installation',
            true,
            $general_update,
            ...$this->getObjectives($general_update)
        );
    }

    public function getUpdateObjective(Config $config = null): Objective
    {
        $general_update = parent::getUpdateObjective($config);

        return new ObjectiveCollection(
            'SrExternalPageContent-Plugin Update',
            true,
            $general_update,
            ...$this->getObjectives($general_update)
        );
    }

    /**
     * Helper function to return all additional update objectives
     *
     * @return Objective[]
     */
    public function getObjectives(ObjectiveCollection $precondition): array
    {
        return [
            // db update steps
            new ilSrExternalPageContentUpdateStepsExecutedObjective(
                $precondition,
                new ilSrExternalPageContentDBUpdateSteps()
            ),
        ];
    }

    public function getMigrations(): array
    {
        return [
            new FullMigration()
        ];
    }

    /**
     * Run with `php setup/setup.php achieve XY -vv`
     */
    public function getNamedObjectives(?Config $config = null): array
    {
        return [];
    }
}
