<?php

/*********************************************************************
 * This Code is licensed under the GPL-3.0 License and is Part of a
 * ILIAS Plugin developed by sr solutions ag in Switzerland.
 *
 * https://sr.solutions
 *
 *********************************************************************/

declare(strict_types=1);

namespace srag\Plugins\SrExternalPageContent\Settings;

use srag\Plugins\SrExternalPageContent\Helper\DBStringKeyRepository;

/**
 * @author Fabian Schmid <fabian@sr.solutions>
 */
class SettingsRepositoryDB implements SettingsRepository
{
    use DBStringKeyRepository {
        __construct as private __constructDBStringKeyRepository;
    }

    private ValuePacker $value_packer;

    public function __construct(\ilDBInterface $db)
    {
        $this->__constructDBStringKeyRepository($db);
        $this->value_packer = new ValuePacker();
    }

    protected function getIdName(): string
    {
        return 'keyword';
    }

    protected function getTableName(): string
    {
        return 'sr_epc_settings';
    }

    public function store(Setting $domain): Setting
    {
        if ($this->has($domain->getKeyword())) {
            return $this->update($domain);
        }
        return $this->insert($domain);
    }

    private function insert(Setting $setting): Setting
    {
        $packed_value = $this->value_packer->pack($setting->getValue());

        $this->db->insert($this->getTableName(), [
            'keyword' => ['text', $setting->getKeyword()],
            'value' => ['clob', $packed_value],
        ]);

        return $setting;
    }

    private function update(Setting $setting): Setting
    {
        $packed_value = $this->value_packer->pack($setting->getValue());

        $this->db->update(
            $this->getTableName(),
            ['value' => ['clob', $packed_value]],
            ['keyword' => ['text', $setting->getKeyword()]]
        );

        return $setting;
    }

    public function getById(string $keyword): ?Setting
    {
        $set = $this->db->queryF(
            'SELECT * FROM ' . $this->getTableName() . ' WHERE ' . $this->getIdName() . ' = %s',
            ['text'],
            [$keyword]
        );

        while ($row = $this->db->fetchAssoc($set)) {
            return new Setting($row['keyword'], $this->value_packer->unpack($row['value']));
        }

        return null;
    }

}
