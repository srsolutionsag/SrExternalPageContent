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
abstract class BaseEmbeddable implements Embeddable
{
    protected array $scripts = [];
    protected string $id;
    protected string $url;
    protected array $properties = [];
    protected ?string $thumbnail_rid = null;

    public function __construct(
        string $id,
        string $url,
        array $properties = [],
        array $scripts = [],
        ?string $thumbnail_rid = null
    ) {
        $this->scripts = $scripts;
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

    public function setScripts(array $scripts): BaseEmbeddable
    {
        $this->scripts = $scripts;
        return $this;
    }

    public function setUrl(string $url): BaseEmbeddable
    {
        $this->url = $url;
        return $this;
    }

    public function setProperties(array $properties): BaseEmbeddable
    {
        $this->properties = $properties;
        return $this;
    }

    public function getThumbnailRid(): ?string
    {
        return $this->thumbnail_rid;
    }

    public function setThumbnailRid(?string $thumbnail_rid): BaseEmbeddable
    {
        $this->thumbnail_rid = $thumbnail_rid;
        return $this;
    }
}
