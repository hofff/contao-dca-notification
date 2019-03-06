<?php

declare(strict_types=1);

namespace Hofff\Contao\DcaNotification\ContaoManager;

use Contao\CoreBundle\ContaoCoreBundle;
use Contao\ManagerPlugin\Bundle\Config\BundleConfig;
use Contao\ManagerPlugin\Bundle\Parser\ParserInterface;
use Hofff\Contao\DcaNotification\HofffContaoDcaNotificationBundle;
use Netzmacht\Contao\Toolkit\Bundle\NetzmachtContaoToolkitBundle;

final class Plugin
{
    /** @return BundleConfig[] */
    public function getBundles(ParserInterface $parser) : array
    {
        return [BundleConfig::create(HofffContaoDcaNotificationBundle::class)
            ->setLoadAfter(
                [ContaoCoreBundle::class, NetzmachtContaoToolkitBundle::class]
            ),
        ];
    }
}
