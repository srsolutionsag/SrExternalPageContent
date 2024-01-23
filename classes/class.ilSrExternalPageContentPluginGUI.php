<?php

use ILIAS\UI\Implementation\Component\Input\Field\Input;
use ILIAS\UI\Component\Input\Container\Form\Form;
use srag\Plugins\SrExternalPageContent\Forms\ContentCreation;
use srag\Plugins\SrExternalPageContent\Forms\IFrameSection;

/**
 * @author            Fabian Schmid <fabian@sr.solution>
 * @ilCtrl_isCalledBy ilSrExternalPageContentPluginGUI: ilPCPluggedGUI
 */
class ilSrExternalPageContentPluginGUI extends ilPageComponentPluginGUI
{

    private const MODE_CREATE = self::CMD_CREATE;
    private const MODE_UPDATE = self::CMD_UPDATE;
    private const MODE_PREVIEW = 'preview';
    private const MODE_PRESENTATION = 'presentation';
    private const F_EXTERNAL_CONTENT = 'external_content';
    const CMD_INSERT = 'insert';
    const CMD_EDIT = 'edit';
    const CMD_CREATE = 'create';
    const CMD_UPDATE = 'update';
    const CMD_CANCEL = 'cancel';
    /**
     * @var ilGlobalTemplateInterface
     */
    private $tpl;
    /**
     * @var ilObjUser
     */
    private $user;
    /**
     * @var \ILIAS\DI\HTTPServices
     */
    private $http;
    /**
     * @var ilCtrl
     */
    private $ctrl;
    /**
     * @var \ILIAS\DI\UIServices
     */
    private $ui;
    /**
     * @var \ILIAS\Refinery\Factory
     */
    private $refinery;

    public function __construct()
    {
        global $DIC;

        $this->tpl = $DIC->ui()->mainTemplate();
        $this->user = $DIC->user();
        $this->http = $DIC->http();
        $this->ctrl = $DIC->ctrl();
        $this->ui = $DIC->ui();
        $this->refinery = $DIC->refinery();

        parent::__construct();
    }

    public function executeCommand()
    {
        $cmd = $this->ctrl->getCmd();
        switch ($cmd) {
            case self::CMD_INSERT:
            case self::CMD_EDIT:
            case self::CMD_CREATE:
            case self::CMD_UPDATE:
            case self::CMD_CANCEL:
                $this->$cmd();
                break;
        }
    }

    public function insert(): void
    {
        $this->showForm();
    }

    public function edit(): void
    {
        $this->showForm(true);
    }

    public function create(): void
    {
        $this->processForm();
    }

    public function update(): void
    {
        $this->processForm();
    }

    public function cancel(): void
    {
        $this->returnToParent();
    }

    protected function showForm(bool $edit = false): void
    {
        $this->tpl->setContent(
            $this->ui->renderer()->render(
                $this->initForm($edit)
            )
        );
    }

    protected function initForm(bool $edit = false): Form
    {
        $properties = $this->getProperties();

        if ($edit) {
            $section = new ContentCreation(
                $this->plugin,
                $properties
            );
        } else {
            $section = new IFrameSection(
                $this->plugin,
                $properties
            );
        }

        return $this->ui->factory()->input()->container()->form()->standard(
            $this->ctrl->getFormActionByClass(
                self::class,
                ($this->isCreationMode()) ? self::MODE_CREATE : self::MODE_UPDATE
            ),
            [$section->getSection()]
        )->withAdditionalTransformation(
            $this->refinery->custom()->transformation(function ($value) use ($properties) {
                return $value;
            })
        );
    }

    protected function processForm(): void
    {
        $form = $this->initForm();
        $form = $form->withRequest($this->http->request());
        $data = $form->getData();
        if ($data === null) {
            $this->tpl->setOnScreenMessage('failure', $this->plugin->txt('msg_form_invalid'));
            $this->tpl->setContent(
                $this->ui->renderer()->render(
                    $form
                )
            );
            return;
        }

        if ($this->isCreationMode()) {
            $this->createElement($data[0]);
        } else {
            $this->updateElement($data[0]);
        }

        $this->tpl->setOnScreenMessage('success', $this->plugin->txt('msg_form_saved'));
        $this->returnToParent();
    }

    protected function isCreationMode(): bool
    {
        return (
            ilPageComponentPlugin::CMD_INSERT === $this->getMode() ||
            self::MODE_CREATE === $this->ctrl->getCmd()
        );
    }

    protected function isPresentationMode($mode): bool
    {
        return (
            self::MODE_PRESENTATION === $mode ||
            self::MODE_PREVIEW === $mode
        );
    }

    public function getElementHTML($a_mode, array $a_properties, $plugin_version): string
    {
        if (!$this->isPresentationMode($a_mode)) {
            return $a_properties[self::F_EXTERNAL_CONTENT]; // TODO show info-block
        }

        return html_entity_decode($a_properties[self::F_EXTERNAL_CONTENT] ?? '');
    }
}
