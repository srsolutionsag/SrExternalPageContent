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
class NotEmbeddable implements Embeddable
{
    private string $url;
    private string $reason = NotEmbeddableReasons::NO_REASON; // switch to enum later
    private string $additional_infos = '';

    public function __construct(
        string $url,
        string $reason = NotEmbeddableReasons::NO_REASON,
        string $additional_infos = ''
    ) {
        $this->url = $url;
        $this->reason = $reason;
        $this->additional_infos = $additional_infos;
    }

    public function getUrl(): string
    {
        return $this->url;
    }

    public function getReason(): string
    {
        return $this->reason;
    }

    public function getAdditionalInfos(): string
    {
        return $this->additional_infos;
    }

    public function getProperties(): array
    {
        return [];
    }

    public function getId(): string
    {
        return '';
    }

    public function withId(string $id): Embeddable
    {
        return $this;
    }

    public function getScripts(): array
    {
        return [];
    }

    public function getWidth(): int
    {
        return 640;
    }

    public function getHeight(): int
    {
        return 240;
    }

    public function isResponsive(): bool
    {
        return true;
    }

}
