<?php

/*********************************************************************
 * This Code is licensed under the GPL-3.0 License and is Part of a
 * ILIAS Plugin developed by sr solutions ag in Switzerland.
 *
 * https://sr.solutions
 *
 *********************************************************************/

declare(strict_types=1);

use srag\Plugins\SrExternalPageContent\BaseGUI;
use srag\Plugins\SrExternalPageContent\Whitelist\WhitelistTable;
use srag\Plugins\SrExternalPageContent\Whitelist\WhitelistForm;
use srag\Plugins\SrExternalPageContent\Whitelist\WhitelistedDomain;
use ILIAS\UI\Component\Modal\InterruptiveItem;
use srag\Plugins\SrExternalPageContent\Whitelist\WhitelistRepository;

/**
 * @author            Fabian Schmid <fabian@sr.solutions>
 *
 * @ilCtrl_isCalledBy ilSEPCWhitelistConfigGUI : ilSrExternalPagePluginDispatcherGUI
 */
class ilSEPCWhitelistConfigGUI extends BaseGUI
{
    private WhitelistRepository $repository;

    public function __construct()
    {
        parent::__construct();
        $this->repository = $this->dic->whitelist();
    }

    public function executeCommand(): void
    {
        $this->performStandardCommands();
    }

    protected function index(): void
    {
        $this->toolbar->addComponent(
            $this->dic->ilias()->ui()->factory()->button()->standard(
                $this->translator->txt("whitelist_add"),
                $this->ctrl->getLinkTarget($this, self::CMD_ADD)
            )
        );

        $table = new WhitelistTable(
            $this->dic,
            $this->ctrl->getLinkTarget($this, self::CMD_INDEX)
        );
        $this->tpl->setContent($table->getHTML());
    }

    protected function add(): void
    {
        $form = new WhitelistForm(
            $this->dic,
            $this->ctrl->getLinkTarget($this, self::CMD_CREATE)
        );
        $this->tpl->setContent($form->getHTML());
    }

    protected function create(): void
    {
        $form = new WhitelistForm(
            $this->dic,
            $this->ctrl->getLinkTarget($this, self::CMD_CREATE)
        );
        if (!$form->process($this->http->request()) instanceof WhitelistedDomain) {
            $this->tpl->setContent($form->getHTML());
            return;
        }
        $this->tpl->setOnScreenMessage('success', $this->translator->txt('whitelist_added'), true);
        $this->ctrl->redirect($this, self::CMD_INDEX);
    }

    /**
     * @return never
     */
    protected function confirmDelete(): void
    {
        $confirmation = $this->ui_factory->modal()->interruptive(
            $this->translator->txt('confirm_delete', 'whitelist'),
            $this->translator->txt('confirm_delete_info', 'whitelist'),
            $this->ctrl->getLinkTarget($this, self::CMD_DELETE)
        );

        $confirmation = $confirmation->withAffectedItems(
            array_map(
                fn (WhitelistedDomain $domain): InterruptiveItem => $this->ui_factory->modal()->interruptiveItem(
                    (string) $domain->getId(),
                    $domain->getDomain()
                ),
                $this->resolveDomainsFromRequest()
            )
        );

        $this->outAndEnd(
            $this->http,
            $this->ui_renderer->renderAsync($confirmation)
        );
    }

    protected function delete(): void
    {
        foreach ($this->resolveDomainsFromRequest() as $domain) {
            $this->repository->deleteById($domain->getId());
        }
        $this->tpl->setOnScreenMessage('success', $this->translator->txt('msg_confirm_deleted', 'whitelist'), true);
        $this->ctrl->redirect($this, self::CMD_INDEX);
    }

    protected function edit(): void
    {
        $item = $this->resolveDomainsFromRequest()[0];
        $this->ctrl->setParameter($this, 'wl_id', (string) $item->getId());
        $form = new WhitelistForm(
            $this->dic,
            $this->ctrl->getLinkTarget($this, self::CMD_UPDATE),
            $item
        );

        $this->outAndEnd(
            $this->http,
            $this->ui_renderer->renderAsync($form->getModal())
        );
    }

    protected function update(): void
    {
        $item = $this->resolveDomainsFromRequest()[0];
        $form = new WhitelistForm(
            $this->dic,
            $this->ctrl->getLinkTarget($this, self::CMD_UPDATE),
            $item
        );

        if (!$form->process($this->http->request(), true) instanceof WhitelistedDomain) {
            $this->tpl->setContent($form->getHTML());
            return;
        }

        $this->tpl->setOnScreenMessage('success', $this->translator->txt('whitelist_updated'), true);
        $this->ctrl->redirect($this, self::CMD_INDEX);
    }

    /**
     * @return WhitelistedDomain[]
     */
    private function resolveDomainsFromRequest(): array
    {
        return array_map(
            fn ($item): ?WhitelistedDomain => $this->repository->getById((int) $item),
            $this->resolveItemsFromRequest('wl_id')
        );
    }

}
