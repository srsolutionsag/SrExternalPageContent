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

use srag\Plugins\SrExternalPageContent\DIC;

/**
 * @author Fabian Schmid <fabian@sr.solutions>
 */
trait DBRepository
{
    private \ilDBInterface $db;

    public function __construct(\ilDBInterface $db)
    {
        global $sepcContainer;
        /** @var DIC $sepcContainer */
        $this->db = $db;
    }

    public function total(): int
    {
        return $this->db->query('SELECT COUNT(' . $this->getIdName() . ') FROM ' . $this->getTableName())->numRows();
    }

    abstract protected function getIdName(): string;

    abstract protected function getTableName(): string;

}
