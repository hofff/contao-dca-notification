<?php

declare(strict_types=1);

namespace Hofff\Contao\DcaNotification\Notification;

use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Exception;

use function array_flip;
use function array_key_exists;

final class DcaNotification
{
    public const TYPE_SUBMIT_NOTIFICATION = 'hofff_dca_notification_submit';

    private const QUERY_SUPPORTED_TABLES = <<<'SQL'
SELECT DISTINCT hofff_dca_notification_table
FROM tl_nc_notification
WHERE type = :type AND hofff_dca_notification_table != :empty 
SQL;

    private Connection $connection;

    public function __construct(Connection $connection)
    {
        $this->connection = $connection;
    }

    public function supports(string $tableName): bool
    {
        static $supportedTables = null;

        if ($supportedTables === null) {
            $supportedTables = $this->getSupportedTables();
        }

        return array_key_exists($tableName, $supportedTables);
    }

    /** @return int[]|array<string,int> */
    private function getSupportedTables(): array
    {
        try {
            $result = $this->connection->executeQuery(
                self::QUERY_SUPPORTED_TABLES,
                [
                    'type'  => self::TYPE_SUBMIT_NOTIFICATION,
                    'empty' => '',
                ]
            );
        } catch (Exception $e) {
            return [];
        }

        return array_flip($result->fetchFirstColumn());
    }
}
