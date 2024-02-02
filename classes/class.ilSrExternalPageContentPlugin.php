<?php

require_once __DIR__ . "/../vendor/autoload.php";

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

    /**
     * @description During development we just return the requested variable in CamelCase back
     */
    public function txt(string $a_var): string
    {
        if($a_var === 'cmd_insert') {
            return parent::txt($a_var);
        }
        return (str_replace(' ', ' ', ucwords(str_replace('_', ' ', $a_var))));
    }

}

