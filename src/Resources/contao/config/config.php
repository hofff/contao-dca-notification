<?php

declare(strict_types=1);

use Hofff\Contao\DcaNotification\Notification\DcaNotification;

$GLOBALS['NOTIFICATION_CENTER']['NOTIFICATION_TYPE']['hofff_dca_notification'] = [
    DcaNotification::TYPE_SUBMIT_NOTIFICATION => [
        'recipients'           => ['raw_*', 'value_*', 'admin_email'],
        'email_subject'        => ['label_*', 'raw_*', 'value_*', 'admin_email'],
        'email_text'           => ['label_*', 'raw_*', 'value_*', 'admin_email'],
        'email_html'           => ['label_*', 'raw_*', 'value_*', 'admin_email'],
        'file_name'            => ['label_*', 'raw_*', 'value_*', 'admin_email'],
        'file_content'         => ['label_*', 'raw_*', 'value_*', 'admin_email'],
        'email_sender_name'    => ['label_*', 'raw_*', 'value_*', 'admin_email'],
        'email_sender_address' => ['raw_*', 'value_*', 'admin_email'],
        'email_recipient_cc'   => ['raw_*', 'value_*', 'admin_email'],
        'email_recipient_bcc'  => ['raw_*', 'value_*', 'admin_email'],
        'email_replyTo'        => ['raw_*', 'value_*', 'admin_email'],
        'attachment_tokens'    => ['raw_*', 'value_*'],
    ],
];
