<?php

/*********************************************************************
 * This Code is licensed under the GPL-3.0 License and is Part of a
 * ILIAS Plugin developed by sr solutions ag in Switzerland.
 *
 * https://sr.solutions
 *
 *********************************************************************/

declare(strict_types=1);

namespace srag\Plugins\SrExternalPageContent\Content;

use srag\Plugins\SrExternalPageContent\Content\Dimension\Dimension;

/**
 * @author Fabian Schmid <fabian@sr.solutions>
 */
interface Embeddable
{
    public function getUrl(): string;

    public function getProperties(): array;

    public function getScripts(): array;

    public function getId(): string;

    /**
     * @return static
     */
    public function withId(string $id): Embeddable;

    public function getThumbnailRid(): ?string;

    public function getDimension(): Dimension;
}
