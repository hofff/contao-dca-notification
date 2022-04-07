<?php

declare(strict_types=1);

namespace Hofff\Contao\DcaNotification\EventListener\Dca;

use Contao\CoreBundle\Config\ResourceFinder;
use Contao\DataContainer;
use Contao\DcaExtractor;
use Doctrine\DBAL\Connection;
use Hofff\Contao\DcaNotification\Notification\DcaNotification;
use Symfony\Contracts\Translation\TranslatorInterface;

use function array_key_exists;
use function sprintf;

final class NotificationDcaListener
{
    private TranslatorInterface $translator;

    private ResourceFinder $resourceFinder;

    private Connection $connection;

    public function __construct(
        TranslatorInterface $translator,
        ResourceFinder $resourceFinder,
        Connection $connection
    ) {
        $this->translator     = $translator;
        $this->resourceFinder = $resourceFinder;
        $this->connection     = $connection;
    }

    public function updateTableSchema(DataContainer $dataContainer): void
    {
        $activeRecord = $dataContainer->activeRecord;
        if ($activeRecord === null || $activeRecord->type !== DcaNotification::TYPE_SUBMIT_NOTIFICATION) {
            return;
        }

        // phpcs:ignore Squiz.NamingConventions.ValidVariableName.MemberNotCamelCaps
        $tableName     = $activeRecord->hofff_dca_notification_table;
        $schemaManager = $this->connection->getSchemaManager();

        if (! $tableName || ! $schemaManager->tablesExist([$tableName])) {
            return;
        }

        $columns = $schemaManager->listTableColumns($tableName);
        if (! isset($columns['hofff_dca_notification_send'])) {
            $this->connection->executeStatement(
                sprintf(
                    'ALTER TABLE %s ADD hofff_dca_notification_send CHAR(1) NOT NULL DEFAULT \'\'',
                    $tableName
                )
            );
        }

        if (isset($columns['hofff_dca_notification_notification'])) {
            return;
        }

        $this->connection->executeQuery(
            sprintf(
                'ALTER TABLE %s ADD hofff_dca_notification_notification INT(10) UNSIGNED NOT NULL DEFAULT \'0\'',
                $tableName
            )
        );
    }

    /** @return string[] */
    public function tableOptions(): array
    {
        $options   = [];
        $processed = [];

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

    private function translateTableName(string $tableName): string
    {
        $key   = 'MOD.' . $tableName;
        $label = $this->translator->trans('MOD.' . $tableName, [], 'contao_modules');

        if ($label === $key) {
            return $tableName;
        }

        return sprintf('%s [%s]', $label, $tableName);
    }
}
