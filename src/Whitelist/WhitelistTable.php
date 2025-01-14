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

use ILIAS\UI\URLBuilderToken;
use ILIAS\UI\Component\Table\Data;
use ILIAS\UI\Component\Table\DataRetrieval;
use ILIAS\Data\Order;
use ILIAS\UI\Component\Table\DataRowBuilder;
use ILIAS\Data\Range;
use Generator;
use srag\Plugins\SrExternalPageContent\DIC;
use srag\Plugins\SrExternalPageContent\BaseUIComponent;
use ILIAS\UI\URLBuilder;
use ILIAS\Data\URI;
use srag\Plugins\SrExternalPageContent\Helper\URIBuilder;

/**
 * @author Fabian Schmid <fabian@sr.solutions>
 */
class WhitelistTable extends BaseUIComponent implements DataRetrieval
{
    use URIBuilder;

    private URI $target_url;
    private const DESCRIPTION_LENGTH = 200;
    private ?URLBuilder $url_builder = null;
    private ?URLBuilderToken $token = null;
    /**
     * @readonly
     */
    protected WhitelistRepository $repository;

    public function __construct(
        DIC $dependecies,
        string $target_url
    ) {
        parent::__construct($dependecies);
        $this->target_url = $this->buildURIfromRelative($target_url);
        $this->repository = $this->dependecies->whitelist();
    }

    protected function getTable(): Data
    {
        $f = $this->factory->table();
        $table = $f->data(
            $this->translator->txt('whitelist'),
            $this->initColumns(),
            $this
        );
        $table = $table->withRequest($this->dependecies->ilias()->http()->request());
        $this->url_builder = new URLBuilder($this->target_url);
        [$this->url_builder, $this->token] = $this->url_builder->acquireParameter(['wl'], 'id');

        return $table->withActions(
            [
                'delete' => $f->action()->single(
                    $this->translator->txt('delete', 'whitelist'),
                    $this->url_builder->withURI($this->target_url->withParameter('cmd', 'confirmDelete')),
                    $this->token,
                )->withAsync(true),
                'edit' => $f->action()->single(
                    $this->translator->txt('edit', 'whitelist'),
                    $this->url_builder->withURI($this->target_url->withParameter('cmd', 'edit')),
                    $this->token,
                )->withAsync(true),
                'toggle' => $f->action()->single(
                    $this->translator->txt('toggle', 'whitelist'),
                    $this->url_builder->withURI($this->target_url->withParameter('cmd', 'toggle')),
                    $this->token,
                )->withAsync(false),
            ]
        );
    }

    public function getURLBuilder(): ?URLBuilder
    {
        return $this->url_builder;
    }

    protected function initColumns(): array
    {
        $f = $this->factory->table()->column();
        return [
            'domain' => $f
                ->text($this->translator->txt('domain', 'whitelist'))
                ->withIsSortable(false),
            'title' => $f
                ->text($this->translator->txt('title', 'whitelist'))
                ->withIsSortable(false),
            'active' => $f
                ->statusIcon($this->translator->txt('active', 'whitelist'))
                ->withIsSortable(false),
            'auto_consent' => $f
                ->statusIcon($this->translator->txt('auto_consent', 'whitelist'))
                ->withIsSortable(false),
            'description' => $f
                ->text($this->translator->txt('description', 'whitelist'))
                ->withIsSortable(false),
        ];
    }

    public function getHTML(): string
    {
        return $this->renderer->render([
            $this->getTable()
        ]);
    }

    public function getRows(
        DataRowBuilder $row_builder,
        array $visible_column_ids,
        Range $range,
        Order $order,
        ?array $filter_data,
        ?array $additional_parameters
    ): Generator {
        $ok = $this->factory->symbol()->icon()->custom(
            'templates/default/images/standard/icon_checked.svg',
            '',
            'small'
        );
        $nok = $this->factory->symbol()->icon()->custom(
            'templates/default/images/standard/icon_unchecked.svg',
            '',
            'small'
        );

        foreach ($this->repository->getAll() as $domain) {
            yield $row_builder->buildDataRow(
                (string) $domain->getId(),
                [
                    'domain' => $domain->getDomain(),
                    'title' => $domain->getTitle(),
                    'active' => $domain->isActive() ? $ok : $nok,
                    'auto_consent' => $domain->isAutoConsent() ? $ok : $nok,
                    // shorten and append ellipsis if the description ist longer than 50 characters
                    'description' => strlen($domain->getDescription() ?? '') > self::DESCRIPTION_LENGTH
                        ? substr($domain->getDescription(), 0, self::DESCRIPTION_LENGTH) . '...'
                        : $domain->getDescription() ?? ''
                ]
            );
        }
    }

    public function getTotalRowCount(?array $filter_data, ?array $additional_parameters): ?int
    {
        return $this->repository->total();
    }

}
