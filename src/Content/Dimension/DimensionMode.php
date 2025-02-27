<?php

/*********************************************************************
 * This Code is licensed under the GPL-3.0 License and is Part of a
 * ILIAS Plugin developed by sr solutions ag in Switzerland.
 *
 * https://sr.solutions
 *
 *********************************************************************/

namespace srag\Plugins\SrExternalPageContent\Content\Dimension;

/**
 * @author Fabian Schmid <fabian@sr.solutions>
 */
interface DimensionMode
{
    public const FIXED = 1;
    public const FIXED_HEIGHT = 2;
    public const ASPECT_RATIO = 3;

    public const AS_16_9 = 16 / 9;
    public const AS_4_3 = 4 / 3;
    public const AS_1_1 = 1;
    public const AS_3_4 = 3 / 4;
    public const AS_9_16 = 9 / 16;

}
