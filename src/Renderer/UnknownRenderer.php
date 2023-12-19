<?php

/*********************************************************************
 * This Code is licensed under the GPL-3.0 License and is Part of a
 * ILIAS Plugin developed by sr solutions ag in Switzerland.
 *
 * https://sr.solutions
 *
 *********************************************************************/

declare(strict_types=1);

namespace srag\Plugins\SrExternalPageContent\Renderer;

use srag\Plugins\SrExternalPageContent\Content\Embeddable;

/**
 * @author Fabian Schmid <fabian@sr.solutions>
 */
class UnknownRenderer extends BaseRenderer
{
    public function render(Embeddable $embeddable): string
    {
        return $this->wrap($embeddable, 'unable to render embeddable: ' . get_class($embeddable));
    }
}
