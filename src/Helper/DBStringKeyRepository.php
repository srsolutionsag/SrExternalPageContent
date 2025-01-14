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

use srag\Plugins\SrExternalPageContent\Content\UniqueIdGenerator;

/**
 * @author Fabian Schmid <fabian@sr.solutions>
 */
trait DBStringKeyRepository
{
    use DBRepository;

    public function has(string $id): bool
    {
        $set = $this->db->queryF(
            'SELECT ' . $this->getIdName() . ' FROM ' . $this->getTableName() . ' WHERE ' . $this->getIdName(
            ) . ' = %s',
            ['string'],
            [$id]
        );
        return $this->db->numRows($set) > 0;
    }

    public function deleteById(string $id): void
    {
        $this->db->manipulateF(
            'DELETE FROM ' . $this->getTableName() . ' WHERE ' . $this->getIdName() . ' = %s',
            ['string'],
            [$id]
        );
    }

    protected function newId(): string
    {
        static $generator;
        if (!isset($generator)) {
            $generator = new UniqueIdGenerator();
        }
        return $generator->generate();
    }

}
