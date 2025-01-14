<?php

/*********************************************************************
 * This Code is licensed under the GPL-3.0 License and is Part of a
 * ILIAS Plugin developed by sr solutions ag in Switzerland.
 *
 * https://sr.solutions
 *
 *********************************************************************/

declare(strict_types=1);

/**
 * @author            Fabian Schmid <fabian@sr.solution>
 * @ilCtrl_isCalledBy ilSrExternalPagePluginUploadHandlerGUI: ilUIPluginRouterGUI
 */
class ilSrExternalPagePluginUploadHandlerGUI extends ilCtrlAwareStorageUploadHandler
{
    public function __construct()
    {
        parent::__construct(new ilSrExternalPageContentPluginStakeholder());
    }

    public function getUploadURL(): string
    {
        return $this->ctrl->getLinkTargetByClass([ilUIPluginRouterGUI::class, static::class], self::CMD_UPLOAD);
    }


    public function getExistingFileInfoURL(): string
    {
        return $this->ctrl->getLinkTargetByClass([ilUIPluginRouterGUI::class, static::class], self::CMD_INFO);
    }


    public function getFileRemovalURL(): string
    {
        return $this->ctrl->getLinkTargetByClass([ilUIPluginRouterGUI::class, static::class], self::CMD_REMOVE);
    }
}
