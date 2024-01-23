<?php

use ILIAS\COPage\Editor\Server\UIWrapper;

/**
 * @description       This was an attempt to create a custom editor for the page component which is shown in the tools slate.
 *                    did not work.
 * @author            Fabian Schmid <fabian@sr.solution>
 */
if (!class_exists('ilPCPluggedEditorGUI')) {
    class ilPCPluggedEditorGUI implements \ILIAS\COPage\Editor\Components\PageComponentEditor
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
