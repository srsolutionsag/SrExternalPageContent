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
class iFrame extends BaseEmbeddable implements Embeddable
{

    protected string $title;
    protected int $height;
    protected int $width;
    protected int $frameborder;
    protected array $allow;
    protected string $referrerpolicy;
    protected bool $allowfullscreen;
    protected bool $responsive;

    public function __construct(
        string $id,
        string $url,
        Dimension $dimension = null,
        array $properties = [],
        array $scripts = [],
        ?string $thumbnail_rid = null
    ) {
        parent::__construct($id, $url, $dimension, $properties, $scripts, $thumbnail_rid);
        $this->title = $properties['title'] ?? '';
        $this->frameborder = (int) ($properties['frameborder'] ?? 0);
        $this->allow = $properties['allow'] ?? [];
        $this->referrerpolicy = $properties['referrerpolicy'] ?? '';
        $this->allowfullscreen = isset($properties['allowfullscreen']);
    }

    public function getProperties(): array
    {
        return array_merge(parent::getProperties(), [
            'title' => $this->getTitle(),
            'frameborder' => $this->getFrameborder(),
            'allow' => $this->getAllow(),
            'referrerpolicy' => $this->getReferrerpolicy(),
            'allowfullscreen' => $this->isAllowfullscreen(),
        ]);
    }

    public function getTitle(): string
    {
        return $this->title;
    }

    public function getFrameborder(): int
    {
        return $this->frameborder;
    }

    public function getAllow(): array
    {
        return $this->allow;
    }

    public function getReferrerpolicy(): string
    {
        return $this->referrerpolicy;
    }

    public function isAllowfullscreen(): bool
    {
        return $this->allowfullscreen;
    }

    public function setTitle(string $title): iFrame
    {
        $this->title = $title;
        return $this;
    }

    public function setFrameborder(int $frameborder): iFrame
    {
        $this->frameborder = $frameborder;
        return $this;
    }

    public function setAllow(array $allow): iFrame
    {
        $this->allow = $allow;
        return $this;
    }

    public function setReferrerpolicy(string $referrerpolicy): iFrame
    {
        $this->referrerpolicy = $referrerpolicy;
        return $this;
    }

    public function setAllowfullscreen(bool $allowfullscreen): iFrame
    {
        $this->allowfullscreen = $allowfullscreen;
        return $this;
    }

}
