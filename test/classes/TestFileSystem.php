<?php
declare(strict_types=1);

namespace knotphp\module\knotdatastoreservices\test\classes;

use knotlib\kernel\filesystem\FileSystemInterface;
use knotlib\kernel\filesystem\AbstractFileSystem;

final class TestFileSystem extends AbstractFileSystem implements FileSystemInterface
{
    public function getDirectory(string $dir): string
    {
        return '';
    }
}