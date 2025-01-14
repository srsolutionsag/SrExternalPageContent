<?php

/*********************************************************************
 * This Code is licensed under the GPL-3.0 License and is Part of a
 * ILIAS Plugin developed by sr solutions ag in Switzerland.
 *
 * https://sr.solutions
 *
 *********************************************************************/

declare(strict_types=1);

use ILIAS\DI\UIServices;
use ILIAS\Refinery\Factory;
use ILIAS\UI\Component\Input\Container\Form\Form;
use srag\Plugins\SrExternalPageContent\DIC;
use srag\Plugins\SrExternalPageContent\Translator;
use srag\Plugins\SrExternalPageContent\Content\Embeddable;
use srag\Plugins\SrExternalPageContent\Content\iFrame;
use srag\Plugins\SrExternalPageContent\Content\NotEmbeddable;
use srag\Plugins\SrExternalPageContent\Content\NotEmbeddableReasons;
use srag\Plugins\SrExternalPageContent\Forms\FormBuilder;
use srag\Plugins\SrExternalPageContent\Migration\Preview\PreviewDTO;

/**
 * @author            Fabian Schmid <fabian@sr.solution>
 * @ilCtrl_isCalledBy ilSrExternalPageContentPluginGUI: ilPCPluggedGUI
 * @ilCtrl_Calls ilSrExternalPageContentPluginGUI: ilCtrlAwareStorageUploadHandler
 */
class ilSrExternalPageContentPluginGUI extends ilPageComponentPluginGUI
{
    private const MODE_CREATE = self::CMD_CREATE;
    private const MODE_UPDATE = self::CMD_UPDATE;
    private const MODE_PREVIEW = 'preview';
    private const MODE_PRESENTATION = 'presentation';
    private const F_EXTERNAL_CONTENT = 'external_content';
    public const CMD_INSERT = 'insert';
    public const CMD_EDIT = 'edit';
    public const CMD_CREATE = 'create';
    public const CMD_UPDATE = 'update';
    public const CMD_CANCEL = 'cancel';
    public const EMBEDDABLE_ID = 'embeddable_id';
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
     * @var UIServices
     */
    private $ui;
    /**
     * @var Factory
     */
    private $refinery;
    private DIC $dependencies;
    private Translator $translator;

    public function __construct()
    {
        global $DIC, $sepcContainer;

        $this->tpl = $DIC->ui()->mainTemplate();
        $this->user = $DIC->user();
        $this->http = $DIC->http();
        $this->ctrl = $DIC->ctrl();
        $this->ui = $DIC->ui();
        $this->refinery = $DIC->refinery();
        $this->dependencies = $sepcContainer;
        $this->translator = $this->dependencies->translator();

        $this->ui->mainTemplate()->addJavaScript(
            './Customizing/global/plugins/Services/COPage/PageComponent/SrExternalPageContent/assets/js/content.js?version=2'
        );
        $this->ui->mainTemplate()->addCss(
            './Customizing/global/plugins/Services/COPage/PageComponent/SrExternalPageContent/assets/css/content.css?version=2'
        );

        parent::__construct();
    }

    protected function getEmbeddable(
        array $properties,
        bool $ensure_iframe
    ): Embeddable {
        $embeddable_id = $properties[self::EMBEDDABLE_ID] ?? null;
        if ($embeddable_id === null || !$this->dependencies->embeddables()->has((string) $embeddable_id)) {
            if ($ensure_iframe) {
                $embeddable = new iFrame('', $properties['url'] ?? '');
            } else {
                $embeddable = null;
                if (isset($properties['preview'])) {
                    $embeddable = PreviewDTO::wakeup($properties['preview']);
                }

                return $embeddable ?? new NotEmbeddable($properties['url'] ?? '', NotEmbeddableReasons::NOT_FOUND);
            }
        } else {
            $embeddable = $this->dependencies->embeddables()->getById(
                (string) $embeddable_id,
                false
            );
        }
        return $embeddable;
    }

    public function executeCommand(): void
    {
        $cmd = $this->ctrl->getCmd();
        switch ($cmd) {
            case self::CMD_INSERT:
            case self::CMD_EDIT:
            case self::CMD_CREATE:
            case self::CMD_UPDATE:
            case self::CMD_CANCEL:
                $this->setMode($cmd);
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
        $form_builder = new FormBuilder($this->dependencies);

        if (!$edit) {
            $section = $form_builder->buildFor(null);
        } else {
            $section = $form_builder->buildFor(
                $this->getEmbeddable($this->getProperties(), true)
            );
        }

        return $this->ui->factory()->input()->container()->form()
                        ->standard(
                            $this->ctrl->getFormActionByClass(
                                self::class,
                                ($this->isCreationMode()) ? self::MODE_CREATE : self::MODE_UPDATE
                            ),
                            [$section->getSection()]
                        );
    }

    protected function processForm(): void
    {
        $form = $this->initForm($this->mode === self::MODE_UPDATE);
        $form = $form->withRequest($this->http->request());
        $data = $form->getData()[0] ?? null;
        if ($data === null) {
            $this->tpl->setOnScreenMessage('failure', $this->translator->txt('msg_form_invalid'));
            $this->tpl->setContent(
                $this->ui->renderer()->render(
                    $form
                )
            );
            return;
        }

        /** @var Embeddable $data */

        $properties = array_merge(
            $this->getProperties(),
            [self::EMBEDDABLE_ID => $data->getId()]
        );

        if ($this->isCreationMode()) {
            $this->createElement($properties);
        } else {
            $this->updateElement($properties);
        }

        $this->tpl->setOnScreenMessage('success', $this->translator->txt('msg_form_saved'));
        $this->returnToParent();
    }

    protected function isCreationMode(): bool
    {
        if (ilPageComponentPlugin::CMD_INSERT === $this->getMode()) {
            return true;
        }
        return self::MODE_CREATE === $this->ctrl->getCmd();
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
        $embeddable = $this->getEmbeddable($a_properties, false);
        $presentation_mode = $this->isPresentationMode($a_mode);

        return $this->dependencies->renderer()->getFor($embeddable, $presentation_mode)->render(
            $embeddable
        );
    }
}
