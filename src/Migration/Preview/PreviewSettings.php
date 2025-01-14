<?php

/*********************************************************************
 * This Code is licensed under the GPL-3.0 License and is Part of a
 * ILIAS Plugin developed by sr solutions ag in Switzerland.
 *
 * https://sr.solutions
 *
 *********************************************************************/

declare(strict_types=1);

namespace srag\Plugins\SrExternalPageContent\Migration\Preview;

/**
 * @author Fabian Schmid <fabian@sr.solutions>
 */
class PreviewSettings
{
    private string $encapsulation = "<pre>%s</pre>";
    private string $line_ending = "<br>";

    public function __construct(
        string $encapsulation = "%s",
        string $line_ending = ""
    ) {
        $this->encapsulation = $encapsulation;
        $this->line_ending = $line_ending;
    }

    public function getEncapsulation(): string
    {
        return $this->encapsulation;
    }

    public function getLineEnding(): string
    {
        return $this->line_ending;
    }

}
