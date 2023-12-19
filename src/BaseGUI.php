<?php

/*********************************************************************
 * This Code is licensed under the GPL-3.0 License and is Part of a
 * ILIAS Plugin developed by sr solutions ag in Switzerland.
 *
 * https://sr.solutions
 *
 *********************************************************************/

declare(strict_types=1);

namespace srag\Plugins\SrExternalPageContent;

use ILIAS\UI\Renderer;
use ILIAS\UI\Factory;
use ILIAS\HTTP\Wrapper\WrapperFactory;
use ILIAS\HTTP\Services;
use srag\Plugins\SrExternalPageContent\Helper\HTTPState;

/**
 * @author Fabian Schmid <fabian@sr.solutions>
 */
abstract class BaseGUI
{
    use HTTPState;

    public const CMD_INDEX = 'index';
    public const CMD_ADD = 'add';
    public const CMD_CREATE = 'create';
    public const CMD_EDIT = 'edit';
    public const CMD_UPDATE = 'update';
    public const CMD_DELETE = 'delete';
    public const CMD_CONFIRM_DELETE = 'confirmDelete';
    protected \ilTabsGUI $tabs;
    protected Renderer $ui_renderer;
    protected Factory $ui_factory;
    protected \ilToolbarGUI $toolbar;
    protected DIC $dic;
    protected Translator $translator;
    protected WrapperFactory $http_wrapper;
    protected Services $http;
    protected \ilGlobalTemplateInterface $tpl;
    protected \ilCtrlInterface $ctrl;

    public function __construct()
    {
        global $sepcContainer;
        /** @var DIC $sepcContainer */

        $this->dic = $sepcContainer;
        $this->ctrl = $sepcContainer->ilias()->ctrl();
        $this->tpl = $sepcContainer->ilias()->ui()->mainTemplate();
        $this->toolbar = $sepcContainer->ilias()->toolbar();
        $this->http = $sepcContainer->ilias()->http();
        $this->http_wrapper = $sepcContainer->ilias()->http()->wrapper();
        $this->tabs = $sepcContainer->ilias()->tabs();

        $this->ui_factory = $sepcContainer->ilias()->ui()->factory();
        $this->ui_renderer = $sepcContainer->ilias()->ui()->renderer();

        $this->translator = $sepcContainer->translator();
    }

    abstract public function executeCommand(): void;

    protected function performStandardCommands(): void
    {
        $cmd = $this->ctrl->getCmd(self::CMD_INDEX);
        switch ($cmd) {
            case self::CMD_INDEX:
                $this->index();
                break;
            case self::CMD_ADD:
                $this->add();
                break;
            case self::CMD_CREATE:
                $this->create();
                break;
            case self::CMD_EDIT:
                $this->edit();
                break;
            case self::CMD_UPDATE:
                $this->update();
                break;
            case self::CMD_DELETE:
                $this->delete();
                break;
            case self::CMD_CONFIRM_DELETE:
                $this->confirmDelete();
                break;
            default:
                break;
        }
    }

}
