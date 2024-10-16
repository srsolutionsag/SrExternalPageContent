<?php

/*********************************************************************
 * This Code is licensed under the GPL-3.0 License and is Part of a
 * ILIAS Plugin developed by sr solutions ag in Switzerland.
 *
 * https://sr.solutions
 *
 *********************************************************************/

declare(strict_types=1);

use srag\Plugins\SrExternalPageContent\Helper\Refinery;
use srag\Plugins\SrExternalPageContent\BaseGUI;
use ILIAS\UI\Component\Input\Container\Form\Standard;
use srag\Plugins\SrExternalPageContent\Settings\Settings;

/**
 * @author            Fabian Schmid <fabian@sr.solutions>
 *
 * @ilCtrl_isCalledBy ilSEPCSettingsGUI : ilSrExternalPagePluginDispatcherGUI
 */
class ilSEPCSettingsGUI extends BaseGUI
{
    private array $default_roles;
    private Settings $settings;
    private Refinery $refinery;

    public function __construct()
    {
        parent::__construct();
        $this->settings = $this->dic->settings();
        $this->refinery = $this->dic->refinery();
        $this->default_roles = [2, 4];
    }

    public function executeCommand(): void
    {
        $this->performStandardCommands();
    }

    private function getForm(): Standard
    {
        $factory = $this->ui_factory->input();

        $current_value = $this->settings->get('roles', $this->default_roles);
        $options = $this->getGlobalAndLocalRoles();
        $role_keys = array_keys($options);
        $current_value = array_intersect($current_value, $role_keys);

        return $factory->container()->form()->standard(
            $this->ctrl->getLinkTarget($this, self::CMD_UPDATE),
            [
                $factory->field()->section(
                    [
                        'roles' => $factory->field()->multiSelect(
                            $this->translator->txt('settings_roles'),
                            $options,
                            $this->translator->txt('settings_roles_info')
                        )->withValue(
                            $current_value
                        )->withAdditionalTransformation(
                            $this->refinery->trafo(
                                fn (array $role_ids): array => $this->settings->set('roles', $role_ids)
                            )
                        )
                    ],
                    $this->translator->txt('settings_title')
                ),
            ]
        );
    }

    protected function index(): void
    {
        $this->tpl->setContent(
            $this->ui_renderer->render($this->getForm())
        );
    }

    protected function update(): void
    {
        $form = $this->getForm()->withRequest($this->http->request());
        if ($form->getData() !== null) {
            $this->tpl->setOnScreenMessage('success', $this->translator->txt('saved_successfully'), true);
            $this->ctrl->redirect($this, self::CMD_INDEX);
            return;
        }
        $this->tpl->setContent(
            $this->ui_renderer->render($form)
        );
    }

    //
    // Helpers
    //
    private array $valid_parent_types = ['crs', 'grp', 'root', 'cat'];

    public function translateRoleIds(array $role_ids): array
    {
        $roles = [];
        foreach ($role_ids as $role_id) {
            $role_id = (int) $role_id;
            $roles[$role_id] = ilObject2::_lookupTitle($role_id);
        }
        return $roles;
    }

    public function getGlobalAndLocalRoles(): array
    {
        return $this->getGlobalRoles() + $this->getLocalRoles();
    }

    public function getGlobalRoles(): array
    {
        $roles = [];
        foreach ($this->dic->ilias()->rbac()->review()->getRolesByFilter(ilRbacReview::FILTER_ALL_GLOBAL) as $role) {
            $role_id = (int) $role['obj_id'];
            if ($role_id === 14) {
                continue;
            }
            $roles[$role_id] = $role['title'];
        }

        return $roles;
    }

    public function getLocalRoles(): array
    {
        $roles = [];
        foreach ($this->dic->ilias()->rbac()->review()->getRolesByFilter(ilRbacReview::FILTER_NOT_INTERNAL) as $role) {
            $parent = $this->dic->ilias()->repositoryTree()->getNodeData($role['parent'] ?? 0);
            $parent_type = $parent['type'] ?? '';
            if (!in_array($parent_type, $this->valid_parent_types, true)) {
                continue;
            }

            $roles[(int) $role['obj_id']] = $role['title'];
        }

        return $roles;
    }

}
