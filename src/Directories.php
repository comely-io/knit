<?php
/**
 * This file is a part of "comely-io/knit" package.
 * https://github.com/comely-io/knit
 *
 * Copyright (c) Furqan A. Siddiqui <hello@furqansiddiqui.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code or visit following link:
 * https://github.com/comely-io/knit/blob/master/LICENSE
 */

declare(strict_types=1);

namespace Comely\Knit;

use Comely\Filesystem\Directory;

/**
 * Class Directories
 * @package Comely\Knit
 * @property-read null|Directory $templates
 * @property-read null|Directory $compiler
 * @property-read null|Directory $cache
 */
class Directories
{
    /** @var null|Directory */
    private $templates;
    /** @var null|Directory */
    private $compiler;
    /** @var null|Directory */
    private $cache;

    /**
     * @param $dir
     * @return mixed
     */
    public function __get($dir)
    {
        switch ($dir) {
            case "templates":
            case "compiler":
            case "cache":
                return $this->$dir;
        }

        throw new \DomainException('Cannot get value of inaccessible property');
    }

    /**
     * @param Directory $dir
     * @return Directories
     */
    public function templates(Directory $dir): self
    {
        $this->templates = $dir;
        return $this;
    }

    /**
     * @param Directory $dir
     * @return Directories
     */
    public function compiler(Directory $dir): self
    {
        $this->compiler = $dir;
        return $this;
    }

    /**
     * @param Directory $dir
     * @return Directories
     */
    public function cache(Directory $dir): self
    {
        $this->cache = $dir;
        return $this;
    }
}