<?php

/*********************************************************************
 * This Code is licensed under the GPL-3.0 License and is Part of a
 * ILIAS Plugin developed by sr solutions ag in Switzerland.
 *
 * https://sr.solutions
 *
 *********************************************************************/

declare(strict_types=1);

namespace srag\Plugins\SrExternalPageContent\Settings;

/**
 * @author Fabian Schmid <fabian@sr.solutions>
 */
class Setting
{
    private string $keyword;
    /**
     * @var mixed
     */
    private $value;

    /**
     * @param mixed $value
     */
    public function __construct(string $keyword, $value)
    {
        $this->keyword = $keyword;
        $this->value = $value;
    }

    public function getKeyword(): string
    {
        return $this->keyword;
    }

    public function getValue()
    {
        return $this->value;
    }

    public function setValue($value): self
    {
        $this->value = $value;
        return $this;
    }

}
