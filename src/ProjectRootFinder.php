<?php

declare(strict_types=1);

namespace Duyler\Config;

use LogicException;

final class ProjectRootFinder
{
    /**
     * @throws LogicException
     */
    public function find(string $rootFile, string $startDir): string
    {
        $dir = $startDir;
        $previousDir = '';

        while (!is_file($dir . '/' . $rootFile)) {
            $previousDir = $dir;
            $dir = dirname($dir);

            if (!is_dir($dir) || $dir === $previousDir) {
                throw new LogicException(sprintf('Cannot auto-detect project dir using root file "%s"', $rootFile));
            }
        }

        return rtrim($dir, '/') . '/';
    }
}
