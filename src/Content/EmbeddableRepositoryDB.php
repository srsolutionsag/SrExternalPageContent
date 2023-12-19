<?php

/*********************************************************************
 * This Code is licensed under the GPL-3.0 License and is Part of a
 * ILIAS Plugin developed by sr solutions ag in Switzerland.
 *
 * https://sr.solutions
 *
 *********************************************************************/

namespace srag\Plugins\SrExternalPageContent\Content;

use srag\Plugins\SrExternalPageContent\Helper\DBIntKeyRepository;

/**
 * @author Fabian Schmid <fabian@sr.solutions>
 */
class EmbeddableRepositoryDB implements EmbeddableRepository
{
    use DBIntKeyRepository;

    private const TYPE_INT_IFRAME = 1;

    protected function getKeyName(): string
    {
        return 'id';
    }

    protected function getTableName(): string
    {
        return 'sr_epc_content';
    }

    public function store(Embeddable $embeddable): Embeddable
    {
        if ($embeddable instanceof NotEmbeddable) {
            throw new \InvalidArgumentException("NotEmbeddable cannot be stored");
        }

        if ($embeddable->getId() > 0 && $this->has($embeddable->getId())) {
            return $this->update($embeddable);
        }
        return $this->insert($embeddable);
    }

    private function insert(Embeddable $embeddable): Embeddable
    {
        $next_id = $this->db->nextId($this->getTableName());
        $this->db->insert($this->getTableName(), [
            "id" => ["integer", $next_id],
            "type" => ["integer", $this->classToType($embeddable)],
            "status" => ["integer", 1],
            "url" => ["text", $embeddable->getUrl()],
            "properties" => ["clob", $this->sleep($embeddable->getProperties())],
            "scripts" => ["clob", $this->sleep($embeddable->getScripts())],
        ]);

        return $embeddable->withId($next_id);
    }

    private function update(Embeddable $embeddable): Embeddable
    {
        $this->db->update($this->getTableName(), [
            "type" => ["integer", $this->classToType($embeddable)],
            "status" => ["integer", 1],
            "url" => ["text", $embeddable->getUrl()],
            "properties" => ["clob", $this->sleep($embeddable->getProperties())],
            "scripts" => ["clob", $this->sleep($embeddable->getScripts())],
        ], [
            "id" => ["integer", $embeddable->getId()]
        ]);

        return $embeddable;
    }

    public function delete(Embeddable $embeddable): void
    {
        $this->deleteById($embeddable->getId());
    }

    public function getById(int $id, bool $skip_whitlist_check): ?Embeddable
    {
        $q = $this->db->queryF(
            "SELECT * FROM " . $this->getTableName() . " WHERE id = %s",
            ["integer"],
            [$id]
        );
        $result = $this->db->fetchAssoc($q);

        if ($result === null || $result === []) {
            return null;
        }
        return $this->buildFromDBSet($result);
    }

    private function buildFromDBSet(array $set): Embeddable
    {
        $class = $this->typeToClass($set["type"]);
        /** @var Embeddable $instance */
        return new $class(
            (int) $set["id"],
            $set["url"],
            $this->wakeProperties($set["properties"]),
            $this->wakeProperties($set["scripts"])
        );
    }

    private function typeToClass(int $type): string
    {
        switch ($type) {
            case self::TYPE_INT_IFRAME:
                return iFrame::class;
            default:
                throw new \InvalidArgumentException("Unknown type $type");
        }
    }

    private function classToType(Embeddable $class): int
    {
        switch (get_class($class)) {
            case iFrame::class:
                return self::TYPE_INT_IFRAME;
            default:
                throw new \InvalidArgumentException("Unknown class $class");
        }
    }

    protected function sleep(array $properties): string
    {
        return json_encode($properties);
    }

    protected function wakeProperties(string $properties): array
    {
        return json_decode($properties, true) ?? [];
    }

}
