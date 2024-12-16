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

use srag\Plugins\SrExternalPageContent\Helper\DBIntKeyRepository;

/**
 * @author Fabian Schmid <fabian@sr.solutions>
 */
class WhitelistRepositoryDB implements WhitelistRepository
{
    use DBIntKeyRepository;

    protected function getKeyName(): string
    {
        return 'id';
    }

    protected function getTableName(): string
    {
        return 'sr_epc_whitelist';
    }

    public function blank(): WhiteListedDomain
    {
        return new WhitelistedDomain(0, '', Status::STATUS_ACTIVE, '', '');
    }

    protected function insert(WhitelistedDomain $domain): WhitelistedDomain
    {
        $next_id = $this->db->nextId($this->getTableName());
        $this->db->insert($this->getTableName(), [
            'id' => ['integer', $next_id],
            'domain' => ['text', $domain->getDomain()],
            'status' => ['integer', $domain->getStatus()],
            'title' => ['text', $domain->getTitle()],
            'description' => ['text', $domain->getDescription()],
        ]);

        return $domain->withId($next_id);
    }

    protected function update(WhitelistedDomain $domain): WhitelistedDomain
    {
        $this->db->update($this->getTableName(), [
            'domain' => ['text', $domain->getDomain()],
            'status' => ['integer', $domain->getStatus()],
            'title' => ['text', $domain->getTitle()],
            'description' => ['text', $domain->getDescription()],
        ], [
            'id' => ['integer', $domain->getId()],
        ]);

        return $domain;
    }

    public function store(WhitelistedDomain $domain): WhitelistedDomain
    {
        if ($domain->getId() === 0 || !$this->has($domain->getId())) {
            return $this->insert($domain);
        }
        return $this->update($domain);
    }

    public function getById(int $id): ?WhitelistedDomain
    {
        $set = $this->db->queryF('SELECT * FROM ' . $this->getTableName() . ' WHERE id = %s', ['integer'], [$id]);
        $data = $this->db->fetchAssoc($set);

        if ($data === null || $data === []) {
            return null;
        }

        return $this->buildFromDBSet($data);
    }

    public function getPossibleMatches(string $domain): array
    {
        $set = $this->db->queryF(
            'SELECT * FROM ' . $this->getTableName() . ' WHERE domain LIKE %s AND status = 1',
            ['text'],
            ['%' . $domain . '%']
        );
        $domains = [];
        while ($data = $this->db->fetchAssoc($set)) {
            $domains[] = $this->buildFromDBSet($data);
        }

        return $domains;
    }

    public function getPossibleMatchesIncludingInactive(string $domain): array
    {
        $set = $this->db->queryF(
            'SELECT * FROM ' . $this->getTableName() . ' WHERE domain LIKE %s',
            ['text'],
            ['%' . $domain . '%']
        );
        $domains = [];
        while ($data = $this->db->fetchAssoc($set)) {
            $domains[] = $this->buildFromDBSet($data);
        }

        return $domains;
    }

    private function buildFromDBSet(array $set): WhitelistedDomain
    {
        return new WhitelistedDomain(
            (int) $set['id'],
            $set['domain'],
            (int) $set['status'],
            empty($set['title']) ? null : $set['title'],
            empty($set['description']) ? null : $set['description']
        );
    }

    /**
     * @return WhitelistedDomain[]
     */
    public function getAll(): array
    {
        $set = $this->db->query('SELECT * FROM ' . $this->getTableName());
        $domains = [];
        while ($data = $this->db->fetchAssoc($set)) {
            $domains[] = $this->buildFromDBSet($data);
        }

        return $domains;
    }
}
