<?php

declare(strict_types=1);

namespace Hofff\Contao\DcaNotification\EventListener\Hook;

use Doctrine\DBAL\Connection;
use Hofff\Contao\DcaNotification\EventListener\Dca\DcaSendingNotificationDcaListener;
use Netzmacht\Contao\Toolkit\Dca\Manager as DcaManager;
use PDO;
use Symfony\Component\Translation\TranslatorInterface;
use function array_flip;
use function array_key_exists;

final class AddNotificationFieldsListener
{
    private const QUERY_SUPPORTED_TABLES = <<<'SQL'
SELECT DISTINCT hofff_dca_notification_table
FROM tl_nc_notification
WHERE hofff_dca_notification_table != :empty
SQL;

    /** @var DcaManager */
    private $dcaManager;

    /** @var Connection */
    private $connection;

    /** @var TranslatorInterface */
    private $translator;

    public function __construct(DcaManager $dcaManager, Connection $connection, TranslatorInterface $translator)
    {
        $this->dcaManager = $dcaManager;
        $this->connection = $connection;
        $this->translator = $translator;
    }

    public function onLoadDataContainer(string $dataContainerName): void
    {
        if (!$this->supports($dataContainerName)) {
            return;
        }

        $definition = $this->dcaManager->getDefinition($dataContainerName);
        $definition->modify(
            ['fields'],
            function (array $fields): array {
                $fields['hofff_dca_notification_send']         = $this->notificationSendDca();
                $fields['hofff_dca_notification_notification'] = $this->notificationDca();

                return $fields;
            }
        );

        $definition->modify(
            ['config', 'onsubmit_callback'],
            function ($callbacks): array {
                $callbacks   = $callbacks ?: [];
                $callbacks[] = [DcaSendingNotificationDcaListener::class, 'onSubmit'];

                return $callbacks;
            }
        );

        $definition->set(['subpalettes', 'hofff_dca_notification_send'], 'hofff_dca_notification_notification');

        $definition->modify(
            ['palettes', '__selector__'],
            function ($config) {
                $config   = $config ?: [];
                $config[] = 'hofff_dca_notification_send';

                return $config;
            }
        );
    }

    private function supports(string $dataContainerName): bool
    {
        static $supportedTables = null;

        if ($supportedTables === null) {
            $supportedTables = $this->getSupportedTables();
        }

        return array_key_exists($dataContainerName, $supportedTables);
    }

    /** @return int[] */
    private function getSupportedTables(): array
    {
        $statement = $this->connection->prepare(self::QUERY_SUPPORTED_TABLES);
        $statement->bindValue('empty', '');
        $statement->execute();

        return array_flip($statement->fetchAll(PDO::FETCH_COLUMN));
    }

    /** @return mixed[] */
    private function notificationSendDca(): array
    {
        return [
            'label'     => [
                $this->translator->trans('hofff_dca_notification_send.0', [], 'contao_hofff_dca_notification'),
                $this->translator->trans('hofff_dca_notification_send.1', [], 'contao_hofff_dca_notification'),
            ],
            'inputType' => 'checkbox',
            'exclude'   => true,
            'eval'      => [
                'submitOnchange' => true,
                'tl_class'       => 'w50',
            ],
            'sql'       => 'char(1) NOT NULL default \'\'',
        ];
    }

    /** @return mixed[] */
    private function notificationDca(): array
    {
        return [
            'label'            => [
                $this->translator->trans('hofff_dca_notification_notification.0', [], 'contao_hofff_dca_notification'),
                $this->translator->trans('hofff_dca_notification_notification.1', [], 'contao_hofff_dca_notification'),
            ],
            'inputType'        => 'select',
            'exclude'          => true,
            'options_callback' => [
                DcaSendingNotificationDcaListener::class,
                'notificationOptions',
            ],
            'eval'             => [
                'submitOnchange' => true,
                'tl_class'       => 'w50',
            ],
            'sql'              => 'int(10) UNSIGNED NOT NULL default \'0\'',
        ];
    }
}
