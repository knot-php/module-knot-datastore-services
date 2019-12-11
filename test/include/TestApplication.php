<?php
declare(strict_types=1);

namespace KnotModule\KnotDataStoreService\Test;

use KnotLib\Kernel\Kernel\ApplicationType;
use KnotLib\Module\Application\SimpleApplication;

final class TestApplication extends SimpleApplication
{
    public static function type(): ApplicationType
    {
        return ApplicationType::of(ApplicationType::CLI);
    }
}