<?php

/**
 * @author Fabian Schmid <fabian@sr.solutions>
 */
class ilSrExternalPageContentPlugin extends ilPageComponentPlugin
{
    public const PLUGIN_NAME = "SrExternalPageContent";

    public function getPluginName(): string
    {
        return self::PLUGIN_NAME;
    }

    public function isValidParentType($a_type): bool
    {
        return true;
    }

}

