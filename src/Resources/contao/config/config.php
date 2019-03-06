<?php

declare(strict_types=1);

use Hofff\Contao\DcaNotification\Notification\Types;

$GLOBALS['NOTIFICATION_CENTER']['NOTIFICATION_TYPE']['hofff_dca_notification'] = [
    Types::DCA_NOTIFICATION => [
        'recipients'           => ['raw_*', 'value_*'],
        'email_subject'        => ['label_*', 'raw_*', 'value_*'],
        'email_text'           => ['label_*', 'raw_*', 'value_*'],
        'email_html'           => ['label_*', 'raw_*', 'value_*'],
        'file_name'            => ['label_*', 'raw_*', 'value_*'],
        'file_content'         => ['label_*', 'raw_*', 'value_*'],
        'email_sender_name'    => ['label_*', 'raw_*', 'value_*'],
        'email_sender_address' => ['raw_*', 'value_*'],
        'email_recipient_cc'   => ['raw_*', 'value_*'],
        'email_recipient_bcc'  => ['raw_*', 'value_*'],
        'email_replyTo'        => ['raw_*', 'value_*'],
        'attachment_tokens'    => ['raw_*', 'value_*'],
    ],
];
