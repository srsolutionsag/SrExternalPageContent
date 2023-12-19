<?php

/*********************************************************************
 * This Code is licensed under the GPL-3.0 License and is Part of a
 * ILIAS Plugin developed by sr solutions ag in Switzerland.
 *
 * https://sr.solutions
 *
 *********************************************************************/

namespace srag\Plugins\SrExternalPageContent\Content;

use srag\Plugins\SrExternalPageContent\Whitelist\Check;

/**
 * @author Fabian Schmid <fabian@sr.solutions>
 */
class EmbeddableRepositoryWapper implements EmbeddableRepository
{
    private URLTranslator $translator;
    private Check $check;

    private EmbeddableRepository $repository;

    public function __construct(
        EmbeddableRepository $repository,
        Check $check,
        URLTranslator $translator
    ) {
        $this->translator = $translator;
        $this->check = $check;
        $this->repository = $repository;
    }

    public function total(): int
    {
        return $this->repository->total();
    }

    public function has(int $id): bool
    {
        return $this->repository->has($id);
    }

    public function store(Embeddable $embeddable): Embeddable
    {
        return $this->repository->store($embeddable);
    }

    public function deleteById(int $id): void
    {
        $this->repository->deleteById($id);
    }

    public function delete(Embeddable $embeddable): void
    {
        $this->repository->delete($embeddable);
    }

    public function getById(int $id, bool $skip_whitlist_check): ?Embeddable
    {
        $embeddable = $this->repository->getById($id, $skip_whitlist_check);
        if (($embeddable !== null) && ($skip_whitlist_check || $this->check->isAllowed($embeddable->getUrl()))) {
            $embeddable->setUrl($this->translator->translate($embeddable->getUrl()));

            return $embeddable;
        }
        return new NotEmbeddable(
            $embeddable->getUrl(),
            NotEmbeddableReasons::NOT_WHITELISTED
        );
    }

}
