<?php

declare(strict_types=1);

namespace Hofff\Contao\DcaNotification\Notification;

use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Exception\InvalidFieldNameException;
use PDO;
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

    /** @var Connection */
    private $connection;

    public function __construct(Connection $connection)
    {
        $this->connection = $connection;
    }

    public function supports(string $tableName) : bool
    {
        static $supportedTables = null;

        if ($supportedTables === null) {
            $supportedTables = $this->getSupportedTables();
        }

        return array_key_exists($tableName, $supportedTables);
    }

    /** @return int[] */
    private function getSupportedTables() : array
    {
        try {
            $statement = $this->connection->prepare(self::QUERY_SUPPORTED_TABLES);
            $statement->bindValue('type', self::TYPE_SUBMIT_NOTIFICATION);
            $statement->bindValue('empty', '');
            $statement->execute();
        } catch (InvalidFieldNameException $e) {
            return [];
        }

        return array_flip($statement->fetchAll(PDO::FETCH_COLUMN));
    }
}
