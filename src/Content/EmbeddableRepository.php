<?php

/*********************************************************************
 * This Code is licensed under the GPL-3.0 License and is Part of a
 * ILIAS Plugin developed by sr solutions ag in Switzerland.
 *
 * https://sr.solutions
 *
 *********************************************************************/

namespace srag\Plugins\SrExternalPageContent\Content;

/**
 * @author Fabian Schmid <fabian@sr.solutions>
 */
interface EmbeddableRepository
{
    public function total(): int;

    public function blankIFrame(): iFrame;

    public function has(string $id): bool;

    public function store(Embeddable $embeddable): Embeddable;

    public function deleteById(string $id): void;

    public function delete(Embeddable $embeddable): void;

    public function getById(string $id, bool $skip_whitlist_check): ?Embeddable;
    public function cloneById(string $id): ?Embeddable;

    public function all(): \Generator;

}
