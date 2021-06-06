<?php
declare(strict_types=1);

namespace knotphp\module\knotdatastoreservices\test\classes;

use knotlib\kernel\kernel\ApplicationType;
use knotlib\module\application\SimpleApplication;

final class TestApplication extends SimpleApplication
{
    public static function type(): ApplicationType
    {
        return ApplicationType::of(ApplicationType::CLI);
    }
}