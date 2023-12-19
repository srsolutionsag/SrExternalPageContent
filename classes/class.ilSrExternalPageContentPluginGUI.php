<?php

use ILIAS\UI\Implementation\Component\Input\Field\Input;

/**
 * @author            Fabian Schmid <fabian@sr.solution>
 * @ilCtrl_isCalledBy ilSrExternalPageContentPluginGUI: ilPCPluggedGUI
 */
class ilSrExternalPageContentPluginGUI extends ilPageComponentPluginGUI
{

    private const MODE_CREATE = "create";
    private const MODE_UPDATE = 'update';
    private const MODE_PREVIEW = 'preview';
    private const MODE_PRESENTATION = 'presentation';
    private const F_EXTERNAL_CONTENT = 'external_content';
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

    /**
     * @description ATTENTION: WE ARE NOW RESETTING ALL TRAFOS ON THE INPUT TO AVOID THE ALREADY GIVEN STRIP_TAGS TRAFO!
     */
    private function makeInputHTMLAware(\ILIAS\UI\Component\Input\Field\Input $input): void
    {
        $reflection = new ReflectionClass(Input::class);
        $operations_property = $reflection->getProperty('operations');
        $operations_property->setAccessible(true);
        $operations_property->setValue($input, []);
    }

    public function executeCommand()
    {
        $cmd = $this->ctrl->getCmd();
        switch ($cmd) {
            case 'insert':
            case 'edit':
            case 'create':
            case 'update':
            case 'cancel':
                $this->$cmd();
                break;
        }
    }

    public function insert()
    {
        $this->showForm();
    }

    public function edit()
    {
        $this->showForm();
    }

    public function create()
    {
        $this->processForm();
    }

    public function update()
    {
        $this->processForm();
    }

    public function cancel()
    {
        $this->returnToParent();
    }

    protected function showForm(): void
    {
        $this->tpl->setContent(
            $this->ui->renderer()->render(
                $this->initForm()
            )
        );
    }

    protected function initForm(): \ILIAS\UI\Component\Input\Container\Form\Form
    {
        $properties = $this->getProperties();
        $factory = $this->ui->factory()->input()->field();

        $textarea = $factory->textarea(
            $this->plugin->txt(self::F_EXTERNAL_CONTENT),
            $this->plugin->txt(self::F_EXTERNAL_CONTENT . '_info'),
        );

        $this->makeInputHTMLAware($textarea);

        $inputs = [
            $textarea->withValue($properties[self::F_EXTERNAL_CONTENT] ?? '')
                     ->withAdditionalTransformation(
                         $this->refinery->custom()->transformation(function ($value) use ($properties) {
                             return [self::F_EXTERNAL_CONTENT => $value] + $properties;
                         })
                     ),
        ];

        return $this->ui->factory()->input()->container()->form()->standard(
            $this->ctrl->getFormActionByClass(
                self::class,
                ($this->isCreationMode()) ? self::MODE_CREATE : self::MODE_UPDATE
            ),
            $inputs
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

    protected function isPresentationMode($mode) : bool
    {
        return (
            self::MODE_PRESENTATION === $mode ||
            self::MODE_PREVIEW === $mode
        );
    }

    public function getElementHTML($a_mode, array $a_properties, $plugin_version)
    {
        if (!$this->isPresentationMode($a_mode)) {
            return $a_properties[self::F_EXTERNAL_CONTENT]; // TODO show info-block
        }


        return html_entity_decode($a_properties[self::F_EXTERNAL_CONTENT] ?? '');
    }
}
