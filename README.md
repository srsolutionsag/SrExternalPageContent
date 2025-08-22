# SrExternalPageContent ILIAS Plugin

Add external content to ILIAS content pages.

This is an OpenSource project by sr solutions ag,
CH-Burgdorf (https://sr.solutions)

This project is licensed under the GPL-3.0-only license


# Important information about migration

The migration of content in versions prior to 22 August 2025 contained a serious error. This error may have resulted in not only pages with iFrames being migrated, but also other pages being overwritten with new content. The reason for this is an incorrect assumption about how the page editor in ILAIS stores data in the database.
It is necessary to restore the page content using a combination of the page history and a backup of the `page_object` table, if possible. This backup table must be named `page_object_backup` and should contain the status prior to the migration.

Procedure:

```bash
php setup/cli.php achieve SrExternalPageContent.restoreFromHistory
```



## Requirements

* ILIAS 8.0 - 8.999
* PHP >=7.4

## Installation

Start at your ILIAS root directory

```bash
mkdir -p Customizing/global/plugins/Services/COPage/PageComponent/
cd Customizing/global/plugins/Services/COPage/PageComponent/
git clone https://github.com/srsolutionsag/SrExternalPageContent.git SrExternalPageContent
```

Update, activate and config the plugin using the ILIAS setup.

# ILIAS Plugin SLA

We love and live the philosophy of Open Source Software! Most of our
developments, which we develop on behalf of customers or in our own work, we
make publicly available to all interested parties free of charge
at https://github.com/srsolutionsag.

Do you use one of our plugins professionally? Secure the timely availability of
this plugin also for future ILIAS versions by signing an SLA. Find out more
about this at https://sr.solutions/plugins.

Please note that we only guarantee support and release maintenance for
institutions that sign an SLA.
