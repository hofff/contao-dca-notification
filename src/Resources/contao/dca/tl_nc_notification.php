<?php

declare(strict_types=1);

use Hofff\Contao\DcaNotification\EventListener\Dca\NotificationDcaListener;
use Hofff\Contao\DcaNotification\Notification\DcaNotification;

// Config
$GLOBALS['TL_DCA']['tl_nc_notification']['config']['onsubmit_callback'][] = [
    NotificationDcaListener::class,
    'updateTableSchema',
];


// Palettes
$GLOBALS['TL_DCA']['tl_nc_notification']['palettes'][DcaNotification::TYPE_SUBMIT_NOTIFICATION] = '{title_legend},title,type'
    . ';{config_legend},hofff_dca_notification_table';


// Fields
$GLOBALS['TL_DCA']['tl_nc_notification']['fields']['hofff_dca_notification_table'] = [
    'label'            => &$GLOBALS['TL_LANG']['tl_nc_notification']['hofff_dca_notification_table'],
    'inputType'        => 'select',
    'exclude'          => true,
    'options_callback' => [NotificationDcaListener::class, 'tableOptions'],
    'eval'             => [
        'chosen'    => true,
        'mandatory' => true,
        'tl_class'  => 'w50',
    ],
    'sql'              => 'varchar(255) NOT NULL default \'\'',
];
