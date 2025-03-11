<?php

/*********************************************************************
 * This Code is licensed under the GPL-3.0 License and is Part of a
 * ILIAS Plugin developed by sr solutions ag in Switzerland.
 *
 * https://sr.solutions
 *
 *********************************************************************/

declare(strict_types=1);

namespace srag\Plugins\SrExternalPageContent\Parser;

use srag\Plugins\SrExternalPageContent\Content\Dimension\DimensionBuilder;

/**
 * @author Fabian Schmid <fabian@sr.solutions>
 */
class ParserFactory
{

    private DimensionBuilder $dimension_builder;

    public function __construct(DimensionBuilder $dimension_builder)
    {
        $this->dimension_builder = $dimension_builder;
    }

    public function createParser(string $snippet): Parser
    {
        if (strpos($snippet, "<iframe") !== false) {
            return new iFrameParser($this->dimension_builder);
        }
        return new NullParser();
    }

}
