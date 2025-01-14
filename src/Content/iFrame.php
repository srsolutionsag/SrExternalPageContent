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
class iFrame extends BaseEmbeddable implements Embeddable
{
    public const DEFAULT_WIDTH = 160 * 3;
    public const DEFAULT_HEIGHT = 90 * 3;

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
        array $properties = [],
        array $scripts = [],
        ?string $thumbnail_rid = null
    ) {
        parent::__construct($id, $url, $properties, $scripts, $thumbnail_rid);
        $this->title = $properties['title'] ?? '';
        $this->height = (int) ($properties['height'] ?? self::DEFAULT_HEIGHT);
        $this->width = (int) ($properties['width'] ?? self::DEFAULT_WIDTH);
        $this->frameborder = (int) ($properties['frameborder'] ?? 0);
        $this->allow = $properties['allow'] ?? [];
        $this->referrerpolicy = $properties['referrerpolicy'] ?? '';
        $this->allowfullscreen = (bool) ($properties['allowfullscreen'] ?? false);
        $this->responsive = (bool) ($properties['responsive'] ?? true);
    }

    public function getProperties(): array
    {
        return array_merge(parent::getProperties(), [
            'title' => $this->getTitle(),
            'height' => $this->getHeight(),
            'width' => $this->getWidth(),
            'frameborder' => $this->getFrameborder(),
            'allow' => $this->getAllow(),
            'referrerpolicy' => $this->getReferrerpolicy(),
            'allowfullscreen' => $this->isAllowfullscreen(),
            'responsive' => $this->isResponsive()
        ]);
    }

    public function getTitle(): string
    {
        return $this->title;
    }

    public function getHeight(): int
    {
        return $this->height;
    }

    public function getWidth(): int
    {
        return $this->width;
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

    public function isResponsive(): bool
    {
        return $this->responsive;
    }

    public function setTitle(string $title): iFrame
    {
        $this->title = $title;
        return $this;
    }

    public function setHeight(int $height): iFrame
    {
        $this->height = $height;
        return $this;
    }

    public function setWidth(int $width): iFrame
    {
        $this->width = $width;
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

    public function setResponsive(bool $responsive): iFrame
    {
        $this->responsive = $responsive;
        return $this;
    }

}
