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
abstract class BaseEmbeddable implements Embeddable
{
    protected Dimension $dimension;
    protected array $scripts = [];
    protected string $id;
    protected string $url;
    protected array $properties = [];
    protected ?string $thumbnail_rid = null;

    public function __construct(
        string $id,
        string $url,
        Dimension $dimension,
        array $properties = [],
        array $scripts = [],
        ?string $thumbnail_rid = null
    ) {
        $this->scripts = $scripts;
        $this->dimension = $dimension;
        $this->id = $id;
        $this->properties = $properties;
        $this->url = $url;
        $this->thumbnail_rid = $thumbnail_rid;
    }

    public function getUrl(): string
    {
        return $this->url;
    }

    public function getId(): string
    {
        return $this->id;
    }

    /**
     * @return Embeddable
     */
    public function withId(string $id): Embeddable
    {
        $clone = clone $this;
        $clone->id = $id;
        return $clone;
    }

    public function getProperties(): array
    {
        return $this->properties;
    }

    public function getScripts(): array
    {
        return $this->scripts;
    }

    /**
     * @return Embeddable
     */
    public function setScripts(array $scripts): Embeddable
    {
        $this->scripts = $scripts;
        return $this;
    }

    /**
     * @return static
     */
    public function setUrl(string $url): Embeddable
    {
        $this->url = $url;
        return $this;
    }

    /**
     * @return static
     */
    public function setProperties(array $properties): Embeddable
    {
        $this->properties = $properties;
        return $this;
    }

    public function getThumbnailRid(): ?string
    {
        return $this->thumbnail_rid;
    }

    /**
     * @return static
     */
    public function setThumbnailRid(?string $thumbnail_rid): Embeddable
    {
        $this->thumbnail_rid = $thumbnail_rid;
        return $this;
    }

    public function getDimension(): Dimension
    {
        return $this->dimension;
    }

    /**
     * @return static
     */
    public function setDimension(Dimension $dimension): Embeddable
    {
        $this->dimension = $dimension;
        return $this;
    }

}
