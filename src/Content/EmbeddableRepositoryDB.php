<?php

/*********************************************************************
 * This Code is licensed under the GPL-3.0 License and is Part of a
 * ILIAS Plugin developed by sr solutions ag in Switzerland.
 *
 * https://sr.solutions
 *
 *********************************************************************/

namespace srag\Plugins\SrExternalPageContent\Content;

use srag\Plugins\SrExternalPageContent\Helper\DBStringKeyRepository;
use srag\Plugins\SrExternalPageContent\Content\Dimension\DimensionBuilder;

/**
 * @author Fabian Schmid <fabian@sr.solutions>
 */
class EmbeddableRepositoryDB implements EmbeddableRepository
{
    use DBStringKeyRepository {
        DBStringKeyRepository::__construct as private __dbStringKeyRepositoryConstruct;
    }

    private const TYPE_INT_IFRAME = 1;
    private DimensionBuilder $dimensions;

    public function __construct(\ilDBInterface $db)
    {
        $this->__dbStringKeyRepositoryConstruct($db);
        $this->dimensions = new DimensionBuilder();
    }

    protected function getIdName(): string
    {
        return 'id';
    }

    protected function getTableName(): string
    {
        return 'sr_epc_content';
    }

    public function blankIFrame(): iFrame
    {
        return new iFrame($this->newId(), "", $this->dimensions->default());
    }

    public function store(Embeddable $embeddable): Embeddable
    {
        if ($embeddable instanceof NotEmbeddable) {
            throw new \InvalidArgumentException("NotEmbeddable cannot be stored");
        }

        if ($embeddable->getId() !== '' && $this->has($embeddable->getId())) {
            return $this->update($embeddable);
        }
        return $this->insert($embeddable);
    }

    private function insert(Embeddable $embeddable): Embeddable
    {
        $next_id = $this->newId();
        $this->db->insert($this->getTableName(), [
            "id" => ["text", $next_id],
            "type" => ["integer", $this->classToType($embeddable)],
            "status" => ["integer", 1],
            "url" => ["text", $embeddable->getUrl()],
            "properties" => ["clob", $this->sleep($embeddable->getProperties())],
            "scripts" => ["clob", $this->sleep($embeddable->getScripts())],
            "thumb_rid" => ["text", $embeddable->getThumbnailRid()],
            "dimensions" => ["clob", $this->sleep($this->dimensions->toArray($embeddable->getDimension()))],
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
            "thumb_rid" => ["text", $embeddable->getThumbnailRid()],
            "dimensions" => ["clob", $this->sleep($this->dimensions->toArray($embeddable->getDimension()))],
        ], [
            "id" => ["text", $embeddable->getId()]
        ]);

        return $embeddable;
    }

    public function delete(Embeddable $embeddable): void
    {
        $this->deleteById($embeddable->getId());
    }

    public function getById(string $id, bool $skip_whitlist_check): ?Embeddable
    {
        $q = $this->db->queryF(
            "SELECT * FROM " . $this->getTableName() . " WHERE id = %s",
            ["text"],
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
        $dimension = $this->dimensions->fromArray($this->wakeProperties($set["dimensions"] ?? ''));
        $embeddable = new $class(
            (string) $set["id"],
            $set["url"],
            $dimension,
            $this->wakeProperties($set["properties"]),
            $this->wakeProperties($set["scripts"]),
            $set["thumb_rid"] ?? null
        );

        if ($set["dimensions"] === null) { // can be removed after all existing entries have been updated
            $embeddable->setDimension($this->dimensions->fromLegacyProperties($embeddable));
        }

        return $embeddable;
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
                throw new \InvalidArgumentException("Unknown class " . get_class($class));
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

    public function all(): \Generator
    {
        $q = $this->db->query("SELECT * FROM " . $this->getTableName());
        while ($row = $this->db->fetchAssoc($q)) {
            yield $this->buildFromDBSet($row);
        }
    }

}
