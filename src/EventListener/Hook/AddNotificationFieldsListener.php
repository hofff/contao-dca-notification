<?php

declare(strict_types=1);

namespace Hofff\Contao\DcaNotification\EventListener\Hook;

use Contao\CoreBundle\DataContainer\PaletteManipulator;
use Hofff\Contao\DcaNotification\EventListener\Dca\DataContainerSendingNotificationDcaListener;
use Hofff\Contao\DcaNotification\Notification\DcaNotification;
use Netzmacht\Contao\Toolkit\Dca\Definition;
use Netzmacht\Contao\Toolkit\Dca\Manager as DcaManager;
use Symfony\Component\Translation\TranslatorInterface;
use function is_string;

final class AddNotificationFieldsListener
{
    /** @var DcaManager */
    private $dcaManager;

    /** @var TranslatorInterface */
    private $translator;

    /** @var DcaNotification */
    private $dcaNotification;

    public function __construct(
        DcaManager $dcaManager,
        DcaNotification $dcaNotification,
        TranslatorInterface $translator
    ) {
        $this->dcaManager      = $dcaManager;
        $this->dcaNotification = $dcaNotification;
        $this->translator      = $translator;
    }

    public function onLoadDataContainer(string $tableName) : void
    {
        if (! $this->dcaNotification->supports($tableName)) {
            return;
        }

        $definition = $this->dcaManager->getDefinition($tableName);

        $this->addFieldsToDefinition($definition);
        $this->addSubPaletteToDefinition($definition);
        $this->addLegendToExistingPalettes($definition);
    }

    protected function addFieldsToDefinition(Definition $definition) : void
    {
        $definition->modify(
            ['fields'],
            function (array $fields) : array {
                $fields['hofff_dca_notification_send']         = $this->notificationSendDca();
                $fields['hofff_dca_notification_notification'] = $this->notificationDca();

                return $fields;
            }
        );

        $definition->modify(
            ['config', 'onsubmit_callback'],
            static function ($callbacks) : array {
                $callbacks   = $callbacks ?: [];
                $callbacks[] = [DataContainerSendingNotificationDcaListener::class, 'onSubmit'];

                return $callbacks;
            }
        );
    }

    protected function addSubPaletteToDefinition(Definition $definition) : void
    {
        $definition->set(['subpalettes', 'hofff_dca_notification_send'], 'hofff_dca_notification_notification');

        $definition->modify(
            ['palettes', '__selector__'],
            static function ($config) {
                $config   = $config ?: [];
                $config[] = 'hofff_dca_notification_send';

                return $config;
            }
        );
    }

    private function addLegendToExistingPalettes(Definition $definition) : void
    {
        $manipulator = PaletteManipulator::create()
            ->addLegend('hofff_dca_notification_legend', null)
            ->addField(
                'hofff_dca_notification_send',
                'hofff_dca_notification_legend',
                PaletteManipulator::POSITION_APPEND
            );

        foreach ((array) $definition->get(['palettes'], []) as $name => $config) {
            if (! is_string($config)) {
                continue;
            }

            $manipulator->applyToPalette($name, $definition->getName());
        }
    }


    /** @return mixed[] */
    private function notificationSendDca() : array
    {
        return [
            'label'     => [
                $this->translator->trans(
                    'hofff_dca_notification.hofff_dca_notification_send.0',
                    [],
                    'contao_hofff_dca_notification'
                ),
                $this->translator->trans(
                    'hofff_dca_notification.hofff_dca_notification_send.1',
                    [],
                    'contao_hofff_dca_notification'
                ),
            ],
            'inputType' => 'checkbox',
            'exclude'   => true,
            'eval'      => [
                'submitOnChange' => true,
                'tl_class'       => 'w50',
            ],
            'sql'       => 'char(1) NOT NULL default \'\'',
        ];
    }

    /** @return mixed[] */
    private function notificationDca() : array
    {
        return [
            'label'            => [
                $this->translator->trans(
                    'hofff_dca_notification.hofff_dca_notification_notification.0',
                    [],
                    'contao_hofff_dca_notification'
                ),
                $this->translator->trans(
                    'hofff_dca_notification.hofff_dca_notification_notification.1',
                    [],
                    'contao_hofff_dca_notification'
                ),
            ],
            'inputType'        => 'select',
            'exclude'          => true,
            'options_callback' => [
                DataContainerSendingNotificationDcaListener::class,
                'notificationOptions',
            ],
            'eval'             => [
                'tl_class'           => 'w50',
                'includeBlankOption' => true,
                'mandatory'          => true,
            ],
            'sql'              => 'int(10) UNSIGNED NOT NULL default \'0\'',
        ];
    }
}
