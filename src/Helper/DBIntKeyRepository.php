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

/**
 * @author Fabian Schmid <fabian@sr.solutions>
 */
trait DBIntKeyRepository
{
    use DBRepository;

    public function total(): int
    {
        return $this->db->query('SELECT COUNT(' . $this->getIdName() . ') FROM ' . $this->getTableName())->numRows();
    }

    public function has(int $id): bool
    {
        $set = $this->db->queryF(
            'SELECT ' . $this->getIdName() . ' FROM ' . $this->getTableName() . ' WHERE ' . $this->getIdName(
            ) . ' = %s',
            ['integer'],
            [$id]
        );
        return $this->db->numRows($set) > 0;
    }

    public function deleteById(int $id): void
    {
        $this->db->manipulateF(
            'DELETE FROM ' . $this->getTableName() . ' WHERE ' . $this->getIdName() . ' = %s',
            ['integer'],
            [$id]
        );
    }

}
