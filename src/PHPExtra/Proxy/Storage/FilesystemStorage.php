<?php

/**
 * Copyright (c) 2014 Jacek Kobus <kobus.jacek@gmail.com>
 * See the file LICENSE.txt for copying permission.
 */

namespace PHPExtra\Proxy\Storage;

use Doctrine\Common\Cache\FilesystemCache;

/**
 * Uses doctrine filesystem storage
 * Default directory to store files will be taken from sys_get_temp_dir() if none provided
 *
 * @author Jacek Kobus <kobus.jacek@gmail.com>
 */
class FilesystemStorage extends DoctrineCacheStorage
{
    /**
     * @param string $directory
     */
    function __construct($directory = null)
    {
        if ($directory === null) {
            $directory = sys_get_temp_dir();
        }

        $driver = new FilesystemCache($directory);
        parent::__construct($driver);
    }

} 