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
trait DBStringKeyRepository
{
    use DBRepository;

    public function has(string $keyword): bool
    {
        $set = $this->db->queryF(
            'SELECT ' . $this->getKeyName() . ' FROM ' . $this->getTableName() . ' WHERE ' . $this->getKeyName(
            ) . ' = %s',
            ['string'],
            [$keyword]
        );
        return $this->db->numRows($set) > 0;
    }

    public function deleteByKeyword(string $keyword): void
    {
        $this->db->manipulateF(
            'DELETE FROM ' . $this->getTableName() . ' WHERE ' . $this->getKeyName() . ' = %s',
            ['string'],
            [$id]
        );
    }

}
