<?php

declare(strict_types=1);

namespace Hofff\Contao\DcaNotification\EventListener\Hook;

use Hofff\Contao\DcaNotification\Notification\DcaNotification;
use Symfony\Contracts\Translation\TranslatorInterface;

final class TranslateNotificationLegendListener
{
    private DcaNotification $dcaNotification;

    private TranslatorInterface $translator;

    public function __construct(DcaNotification $dcaNotification, TranslatorInterface $translator)
    {
        $this->dcaNotification = $dcaNotification;
        $this->translator      = $translator;
    }

    /** @SuppressWarnings(PHPMD.Superglobals) */
    public function onLoadLanguageFile(string $name, string $language): void
    {
        if (! $this->dcaNotification->supports($name)) {
            return;
        }

        $GLOBALS['TL_LANG'][$name]['hofff_dca_notification_legend'] = $this->translator->trans(
            'hofff_dca_notification.hofff_dca_notification_legend',
            [],
            'contao_hofff_dca_notification',
            $language
        );
    }
}
