<?php

/*********************************************************************
 * This Code is licensed under the GPL-3.0 License and is Part of a
 * ILIAS Plugin developed by sr solutions ag in Switzerland.
 *
 * https://sr.solutions
 *
 *********************************************************************/

declare(strict_types=1);

namespace srag\Plugins\SrExternalPageContent\Restore;

use ILIAS\Setup\Objective;
use ILIAS\Setup\Environment;
use ILIAS\Setup\CLI\IOWrapper;

/**
 * @author Fabian Schmid <fabian@sr.solutions>
 */
class RestoreFromHistory implements Objective
{
    private const WAIT_TIME = 60;
    private const PAGE_OBJECT_BACKUP = 'page_object_backup';
    private const WITHOUT_BACKUP_ENV = "WITHOUT_BACKUP";
    private bool $without_backup = true;

    public function getHash(): string
    {
        return md5(self::class);
    }

    public function getLabel(): string
    {
        return "Restore page_content from history and backup table";
    }

    public function isNotable(): bool
    {
        return true;
    }

    public function getPreconditions(Environment $environment): array
    {
        return [
            new \ilDatabaseInitializedObjective()
        ];
    }

    public function achieve(Environment $environment): Environment
    {
        /** @var IOWrapper $io */
        $io = $environment->getResource(Environment::RESOURCE_ADMIN_INTERACTION);
        /** @var \ilDBInterface $db */
        $db = $environment->getResource(Environment::RESOURCE_DATABASE);

        // Cjheck for backup table
        $backup_table_exists = $db->tableExists(self::PAGE_OBJECT_BACKUP);
        $without_backup = (bool) getenv(self::WITHOUT_BACKUP_ENV) || $this->without_backup;

        if (!$backup_table_exists && !$without_backup) {
            $io->error(
                "The table `" . self::PAGE_OBJECT_BACKUP . "` does not exist. We highly recommand to import a backup of the table `page_object` as a new table `"
                . self::PAGE_OBJECT_BACKUP . "`. This table should contain all pages from before the migration of iFrames.\n"
                . "This will help restore pages in case thei do not have any `page_history` entries.\n"
                . "If you want to start the restore anyway, run the migration with ENV variable `" . self::WITHOUT_BACKUP_ENV . "` set to `1`.\n\n"
                . "e.g. \n\nexport " . self::WITHOUT_BACKUP_ENV . "=1 && php setup/cli.php achieve SrExternalPageContent.restoreFromHistory"
            );
            throw new \RuntimeException('The table page_content_backup does not exist. Cannot restore.');
        }

        // Warn user and give time to abort
        $io->confirmExplicit(
            'This will restore all page contents from history. Are you sure you want to continue?', 'YES'
        );
        $io->error(
            'The restore will begin in ' . self::WAIT_TIME . ' seconds. Press Ctrl+C to abort. Please only run this reset if you kwow what you are doing! Create a backup of your database before you continue!'
        );

        $io->startProgress(self::WAIT_TIME);
        for ($i = 0; $i <= self::WAIT_TIME - 1; $i++) {
            sleep(1);
            $io->advanceProgress();
        }
        $io->stopProgress();

        // Start restore
        if ($backup_table_exists) {
            // Variant with backup table
            $q = "UPDATE page_object
         LEFT JOIN page_history AS page_object_history
                   ON page_object_history.page_id = page_object.page_id
                       AND page_object_history.parent_type = page_object.parent_type
                       AND page_object_history.parent_id = page_object.parent_id
                       AND page_object_history.lang = page_object.lang
                       AND nr = (SELECT GREATEST(MAX(nr), 1)
                                 FROM page_history AS page_object_backup_internal
                                 WHERE page_object_backup_internal.page_id = page_object.page_id
                                   AND page_object_backup_internal.parent_type = page_object.parent_type
                                   AND page_object_backup_internal.parent_id = page_object.parent_id
                                   AND page_object_backup_internal.lang = page_object.lang)
         LEFT JOIN page_object_backup AS page_object_backup
                   ON page_object_backup.page_id = page_object.page_id
                       AND page_object_backup.parent_type = page_object.parent_type
                       AND page_object_backup.parent_id = page_object.parent_id
                       AND page_object_backup.lang = page_object.lang

SET page_object.content = COALESCE(page_object_history.content, page_object_backup.content, page_object.content)

WHERE page_object.content LIKE '%PluginName=\"SrExternalPageContent\"%'
  AND page_object.last_change < (SELECT GREATEST(MAX(last_change), MAX(created)) FROM page_object_backup)";
        } else {
            // Variant without backup table
            $q = "UPDATE page_object
         LEFT JOIN page_history AS page_object_history
                   ON page_object_history.page_id = page_object.page_id
                       AND page_object_history.parent_type = page_object.parent_type
                       AND page_object_history.parent_id = page_object.parent_id
                       AND page_object_history.lang = page_object.lang
                       AND nr = (SELECT GREATEST(MAX(nr), 1)
                                 FROM page_history AS page_object_backup_internal
                                 WHERE page_object_backup_internal.page_id = page_object.page_id
                                   AND page_object_backup_internal.parent_type = page_object.parent_type
                                   AND page_object_backup_internal.parent_id = page_object.parent_id
                                   AND page_object_backup_internal.lang = page_object.lang)
SET page_object.content = COALESCE(page_object_history.content, page_object.content)

WHERE page_object.content LIKE '%PluginName=\"SrExternalPageContent\"%'";
        }

        $restored = $db->manipulate($q);

        $io->inform("Restored $restored page contents from history.");
        $io->success(
            "You must run the migrtation `SrExternalPageContent.fullMigration` now to migrate the restored pages again."
        );

        return $environment;
    }

    public function isApplicable(Environment $environment): bool
    {
        return true;
    }

}
