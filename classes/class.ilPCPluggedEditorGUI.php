<?php

/*********************************************************************
 * This Code is licensed under the GPL-3.0 License and is Part of a
 * ILIAS Plugin developed by sr solutions ag in Switzerland.
 *
 * https://sr.solutions
 *
 *********************************************************************/

declare(strict_types=1);

use ILIAS\COPage\Editor\Components\PageComponentEditor;
use ILIAS\COPage\Editor\Server\UIWrapper;

/**
 * @description       This was an attempt to create a custom editor for the page component which is shown in the tools slate.
 *                    did not work.
 * @author            Fabian Schmid <fabian@sr.solution>
 */
if (!class_exists('ilPCPluggedEditorGUI')) {
    class ilPCPluggedEditorGUI implements PageComponentEditor
    {
        public function getEditorElements(
            UIWrapper $ui_wrapper,
            string $page_type,
            \ilPageObjectGUI $page_gui,
            int $style_id
        ): array {
            return [];
        }

        public function getEditComponentForm(
            UIWrapper $ui_wrapper,
            string $page_type,
            \ilPageObjectGUI $page_gui,
            int $style_id,
            $pcid
        ): string {
            return "";
        }

    }
}
