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

/**
 * @author Fabian Schmid <fabian@sr.solutions>
 */
interface Embeddable
{
    public function getUrl(): string;

    public function getProperties(): array;

    public function getScripts(): array;

    public function getId(): string;

    public function withId(string $id): Embeddable;

    public function getWidth(): int;

    public function getHeight(): int;

    public function isResponsive(): bool;

    public function getThumbnailRid(): ?string;
}
