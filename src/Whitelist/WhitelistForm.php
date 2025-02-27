<?php

/*********************************************************************
 * This Code is licensed under the GPL-3.0 License and is Part of a
 * ILIAS Plugin developed by sr solutions ag in Switzerland.
 *
 * https://sr.solutions
 *
 *********************************************************************/

declare(strict_types=1);

namespace srag\Plugins\SrExternalPageContent\Whitelist;

use ILIAS\UI\Component\Input\Container\Container;
use srag\Plugins\SrExternalPageContent\DIC;
use srag\Plugins\SrExternalPageContent\BaseUIComponent;
use ILIAS\Refinery\Transformation;
use ILIAS\UI\Component\Input\Field\Section;
use ILIAS\UI\Component\Input\Container\Form\Standard;
use Psr\Http\Message\ServerRequestInterface;
use ILIAS\UI\Component\Modal\RoundTrip;
use ILIAS\Data\URI;

/**
 * @author Fabian Schmid <fabian@sr.solutions>
 */
class WhitelistForm extends BaseUIComponent
{
    protected Container $form;
    protected string $post_url;
    protected WhitelistedDomain $domain;
    /**
     * @readonly
     */
    protected WhitelistRepository $repository;

    public function __construct(
        DIC $dependecies,
        string $post_url,
        ?WhitelistedDomain $domain = null
    ) {
        parent::__construct($dependecies);
        $this->repository = $this->dependecies->whitelist();
        $this->post_url = $post_url;
        $this->domain = $domain ?? $this->repository->blank();
        $this->initForm();
    }

    protected function initForm(): void
    {
        $this->form = $this->factory->input()->container()->form()->standard(
            $this->post_url,
            [$this->getSection()]
        )->withAdditionalTransformation(
            $this->travo(fn ($value): WhitelistedDomain => $this->domain)
        );
    }

    private function travo(callable $c): Transformation
    {
        return $this->dependecies->ilias()->refinery()->custom()->transformation($c);
    }

    public function getModal(): RoundTrip
    {
        $title = $this->domain->getId() > 0
            ? $this->translator->txt('edit', 'whitelist')
            : $this->translator->txt('create', 'whitelist');
        return $this->factory
            ->modal()
            ->roundtrip(
                $title,
                [],
                $this->getInputs(),
                $this->post_url,
            )
            ->withAdditionalTransformation(
                $this->travo(fn ($value): WhitelistedDomain => $this->domain)
            );
    }

    public function getInputs(): array
    {
        $f = $this->factory->input()->field();
        return [
            'domain' => $f
                ->text(
                    $this->translator->txt('domain', 'whitelist'),
                    $this->translator->txt('domain_info', 'whitelist')
                )
                ->withRequired(true)
                ->withValue($this->domain->getDomain())
                ->withAdditionalTransformation(
                    $this->travo(
                        function (string $domain): string {
                            $domain = trim($domain);
                            try {
                                return (new URI($domain))->getHost();
                            } catch (\Throwable $e) {
                                return $domain;
                            }
                        }
                    )
                )
                ->withAdditionalTransformation(
                    $this->travo(
                        fn (string $domain): WhitelistedDomain => $this->domain = $this->domain->withDomain($domain)
                    )
                ),
            'status' => $f
                ->checkbox(
                    $this->translator->txt('active', 'whitelist'),
                    $this->translator->txt('active_info', 'whitelist')
                )
                ->withValue($this->domain->isActive())
                ->withAdditionalTransformation(
                    $this->travo(
                        fn (bool $status): WhitelistedDomain => $this->domain = $this->domain->withStatus((int) $status)
                    )
                ),
            'auto_consent' => $f
                ->checkbox(
                    $this->translator->txt('auto_consent', 'whitelist'),
                    $this->translator->txt('auto_consent_info', 'whitelist')
                )
                ->withValue($this->domain->isAutoConsent())
                ->withAdditionalTransformation(
                    $this->travo(
                        fn(bool $auto_consent): WhitelistedDomain => $this->domain = $this->domain->withAutoConsent(
                            (bool) $auto_consent
                        )
                    )
                ),
            'title' => $f
                ->text(
                    $this->translator->txt('title', 'whitelist'),
                    $this->translator->txt('title_info', 'whitelist')
                )
                ->withValue($this->domain->getTitle() ?? '')
                ->withAdditionalTransformation(
                    $this->travo(
                        fn (string $title): WhitelistedDomain => $this->domain = $this->domain->withTitle($title)
                    )
                ),
            'description' => $f
                ->textarea(
                    $this->translator->txt('description', 'whitelist'),
                    $this->translator->txt('description_info', 'whitelist')
                )
                ->withValue($this->domain->getDescription() ?? '')
                ->withAdditionalTransformation(
                    $this->travo(
                        fn (string $description): WhitelistedDomain => $this->domain = $this->domain->withDescription(
                            $description
                        )
                    )
                ),
        ];
    }

    public function getSection(): Section
    {
        return $this->factory->input()->field()->section(
            $this->getInputs(),
            $this->translator->txt('form_header', 'whitelist'),
            $this->translator->txt('form_header_info', 'whitelist')
        );
    }

    public function getHTML(): string
    {
        return $this->renderer->render($this->getForm());
    }

    public function getForm(): Standard
    {
        return $this->form;
    }

    public function process(ServerRequestInterface $request, bool $from_modal = false): ?WhitelistedDomain
    {
        if ($from_modal) {
            $modal = $this->getModal()->withRequest($request);
            $data = $modal->getData();
            if ($data === null) {
                return null;
            }
            $this->domain = $data;
            $this->repository->store($this->domain);

            return $this->domain;
        }

        $this->form = $this->form->withRequest($request);
        $data = $this->form->getData();
        if ($data === null) {
            return null;
        }
        $this->repository->store($this->domain);

        return $this->domain;
    }

}
