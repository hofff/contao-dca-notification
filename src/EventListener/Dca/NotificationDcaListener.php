<?php

declare(strict_types=1);

namespace Hofff\Contao\DcaNotification\EventListener\Dca;

use Contao\CoreBundle\Config\ResourceFinder;
use Contao\DataContainer;
use Contao\DcaExtractor;
use Doctrine\DBAL\Connection;
use Hofff\Contao\DcaNotification\Notification\Types;
use Netzmacht\Contao\Toolkit\Dca\Listener\AbstractListener;
use Netzmacht\Contao\Toolkit\Dca\Manager;
use Symfony\Component\Finder\SplFileInfo;
use Symfony\Component\Translation\TranslatorInterface;
use function array_key_exists;

final class NotificationDcaListener extends AbstractListener
{
    /** @var string */
    protected static $name = 'tl_nc_notification';

    /** @var TranslatorInterface */
    private $translator;

    /** @var ResourceFinder */
    private $resourceFinder;

    /** @var Connection */
    private $connection;

    public function __construct(
        Manager $dcaManager,
        TranslatorInterface $translator,
        ResourceFinder $resourceFinder,
        Connection $connection
    ) {
        parent::__construct($dcaManager);

        $this->translator     = $translator;
        $this->resourceFinder = $resourceFinder;
        $this->connection     = $connection;
    }

    public function updateTableSchema(DataContainer $dataContainer) : void
    {
        $activeRecord = $dataContainer->activeRecord;

        if ($activeRecord->type !== Types::DCA_NOTIFICATION) {
            return;
        }

        $tableName     = $activeRecord->hofff_dca_notification_table;
        $schemaManager = $this->connection->getSchemaManager();

        if (! $tableName || ! $schemaManager->tablesExist([$tableName])) {
            return;
        }

        $columns = $schemaManager->listTableColumns($tableName);
        if (! isset($columns['hofff_dca_notification_send'])) {
            $this->connection->executeQuery(
                'ALTER TABLE %s ADD hofff_dca_notification_send CHAR(1) NOT NULL DEFAULT \'\''
            );
        }

        if (isset($columns['hofff_dca_notification_notification'])) {
            return;
        }

        $this->connection->executeQuery(
            'ALTER TABLE %s ADD hofff_dca_notification_notification INT(10) UNSIGNED NOT NULL DEFAULT \'0\''
        );
    }

    /** @return string[] */
    public function dcaOptions() : array
    {
        $options   = [];
        $processed = [];

        /** @var SplFileInfo $file */
        foreach ($this->resourceFinder->findIn('dca')->depth(0)->files()->name('*.php') as $file) {
            $tableName = $file->getBasename('.php');

            if (array_key_exists($tableName, $processed)) {
                continue;
            }

            $processed[$tableName] = null;

            $tableName = $file->getBasename('.php');
            $extract   = DcaExtractor::getInstance($tableName);
            if (! $extract->isDbTable()) {
                continue;
            }

            $options[$tableName] = $this->translateTableName($tableName);
        }

        return $options;
    }

    private function translateTableName(string $tableName) : string
    {
        $key   = 'MOD.' . $tableName;
        $label = $this->translator->trans('MOD.' . $tableName, [], 'contao_modules');

        if ($label === $key) {
            return $tableName;
        }

        return $key;
    }
}
