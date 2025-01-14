<?php

/*********************************************************************
 * This Code is licensed under the GPL-3.0 License and is Part of a
 * ILIAS Plugin developed by sr solutions ag in Switzerland.
 *
 * https://sr.solutions
 *
 *********************************************************************/

declare(strict_types=1);

namespace srag\Plugins\SrExternalPageContent\Whitelist;

/**
 * @author Fabian Schmid <fabian@sr.solutions>
 */
class WhitelistedDomain
{
    protected bool $auto_consent;
    protected int $id;
    protected string $domain;
    protected int $status;
    protected ?string $title = null;
    protected ?string $description = null;

    public function __construct(
        int $id,
        string $domain,
        int $status,
        bool $auto_consent,
        ?string $title = null,
        ?string $description = null
    ) {
        $this->status = $status;
        $this->auto_consent = $auto_consent;
        $this->id = $id;
        $this->domain = $domain;
        $this->title = $title;
        $this->description = $description;
    }

    public function isAutoConsent(): bool
    {
        return $this->auto_consent;
    }

    public function withAutoConsent(bool $auto_consent): WhitelistedDomain
    {
        $new = clone $this;
        $new->auto_consent = $auto_consent;
        return $new;
    }

    public function getDomain(): string
    {
        return $this->domain;
    }

    public function getTitle(): ?string
    {
        return $this->title;
    }

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function withDomain(string $url): WhitelistedDomain
    {
        $new = clone $this;
        $new->domain = $url;
        return $new;
    }

    public function withTitle(string $title): WhitelistedDomain
    {
        $new = clone $this;
        $new->title = $title;
        return $new;
    }

    public function withDescription(string $description): WhitelistedDomain
    {
        $new = clone $this;
        $new->description = $description;
        return $new;
    }

    public function getId(): int
    {
        return $this->id;
    }

    public function withId(int $id): WhitelistedDomain
    {
        $new = clone $this;
        $new->id = $id;
        return $new;
    }

    public function isActive(): bool
    {
        return $this->status === Status::STATUS_ACTIVE;
    }

    public function getStatus(): int
    {
        return $this->status;
    }

    public function withStatus(int $status): WhitelistedDomain
    {
        $new = clone $this;
        $new->status = $status;
        return $new;
    }
}
