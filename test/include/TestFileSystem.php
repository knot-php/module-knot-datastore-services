<?php
declare(strict_types=1);

namespace KnotModule\KnotDataStoreService\Test;

use KnotLib\Kernel\FileSystem\FileSystemInterface;
use KnotLib\Kernel\FileSystem\AbstractFileSystem;

final class TestFileSystem extends AbstractFileSystem implements FileSystemInterface
{
    public function getDirectory(int $dir): string
    {
        $map = [
        ];
        return $map[$dir];
    }
}