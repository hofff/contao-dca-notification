<?php

declare(strict_types=1);

namespace Hofff\Contao\DcaNotification\EventListener\Dca;

use Contao\Database\Result;
use Contao\DataContainer;
use Contao\Model;
use Doctrine\DBAL\Connection;
use Hofff\Contao\DcaNotification\Notification\DcaNotification;
use Netzmacht\Contao\Toolkit\Data\Model\RepositoryManager;
use Netzmacht\Contao\Toolkit\Dca\Manager as DcaManager;
use Netzmacht\Contao\Toolkit\Dca\Options\OptionsBuilder;
use NotificationCenter\Model\Notification;

final class DataContainerSendingNotificationDcaListener
{
    /** @var DcaManager */
    private $dcaManager;

    /** @var RepositoryManager */
    private $repositoryManager;

    /** @var Connection */
    private $connection;

    public function __construct(DcaManager $dcaManager, RepositoryManager $repositoryManager, Connection $connection)
    {
        $this->dcaManager        = $dcaManager;
        $this->repositoryManager = $repositoryManager;
        $this->connection        = $connection;
    }

    /** @param DataContainer|object $dataContainer */
    public function onSubmit($dataContainer) : void
    {
        if (! $dataContainer instanceof DataContainer) {
            return;
        }

        $activeRecord = $dataContainer->activeRecord;

        if (! $activeRecord->hofff_dca_notification_send || ! $activeRecord->hofff_dca_notification_notification) {
            return;
        }

        $this->resetSendValue($dataContainer->table, (int) $dataContainer->id);
        $this->sendNotification($dataContainer);
    }

    /**
     * @param DataContainer|object|null $dataContainer
     *
     * @return string[]
     */
    public function notificationOptions($dataContainer) : array
    {
        $repository = $this->repositoryManager->getRepository(Notification::class);

        if ($dataContainer instanceof DataContainer && $dataContainer->table) {
            $collection = $repository->findBy(
                ['.type=?', '.hofff_dca_notification_table=?'],
                [DcaNotification::TYPE_SUBMIT_NOTIFICATION, $dataContainer->table],
                ['.order' => 'title']
            );
        } else {
            $collection = $repository->findAll(['.order' => 'title']);
        }

        return OptionsBuilder::fromCollection($collection, 'title')->getOptions();
    }

    private function sendNotification(DataContainer $dataContainer) : void
    {
        $repository   = $this->repositoryManager->getRepository(Notification::class);
        $notification = $repository->find((int) $dataContainer->activeRecord->hofff_dca_notification_notification);

        if (! $notification instanceof Notification || $notification->type !== DcaNotification::TYPE_SUBMIT_NOTIFICATION) {
            return;
        }

        $notification->send($this->buildTokens($dataContainer));
    }

    private function resetSendValue(string $table, int $recordId) : void
    {
        $this->connection->update($table, ['hofff_dca_notification_send' => ''], ['id' => $recordId]);
    }

    /** @return mixed[] */
    private function buildTokens(DataContainer $dataContainer) : array
    {
        $formatter = $this->dcaManager->getFormatter($dataContainer->table);
        $tokens    = ['admin_email' => $GLOBALS['TL_ADMIN_EMAIL']];

        if ($dataContainer->activeRecord instanceof Result || $dataContainer->activeRecord instanceof Model) {
            $row = $dataContainer->activeRecord->row();
        } else {
            $row = (array) $dataContainer->activeRecord;
        }

        foreach ($row as $key => $value) {
            $tokens['label_' . $key] = $formatter->formatFieldLabel($key);
            $tokens['raw_' . $key]   = $value;
            $tokens['value_' . $key] = $formatter->formatValue($key, $value, $dataContainer);
        }

        return $tokens;
    }
}
