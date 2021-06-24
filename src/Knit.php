<?php
/*
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

use Comely\Knit\Exception\CachingException;
use Comely\Knit\Exception\TemplateException;

/**
 * Class Knit
 * @package Comely\Knit
 */
class Knit
{
    /** string Version (Major.Minor.Release) */
    const VERSION = "3.0.0";
    /** int Version (Major * 10000 + Minor * 100 + Release) */
    const VERSION_ID = 30000;

    /** @var Caching */
    private Caching $caching;
    /** @var Directories */
    private Directories $dirs;
    /** @var Modifiers */
    private Modifiers $modifiers;
    /** @var Events */
    private Events $events;

    /**
     * Knit constructor.
     */
    public function __construct()
    {
        $this->caching = new Caching();
        $this->dirs = new Directories();
        $this->modifiers = new Modifiers();
        $this->events = new Events();
    }

    /**
     * @return Directories
     */
    public function dirs(): Directories
    {
        return $this->dirs;
    }

    /**
     * @return Modifiers
     */
    public function modifiers(): Modifiers
    {
        return $this->modifiers;
    }

    /**
     * @return Caching
     * @throws CachingException
     */
    public function caching(): Caching
    {
        if (!$this->dirs->cache) {
            throw new CachingException('Cache directory is not set');
        }

        return $this->caching;
    }

    /**
     * @return Events
     */
    public function events(): Events
    {
        return $this->events;
    }

    /**
     * @param string $fileName
     * @return Template
     * @throws TemplateException
     */
    public function template(string $fileName): Template
    {
        $templatesDirectory = $this->dirs()->templates;
        if (!$templatesDirectory) {
            throw new TemplateException('Knit base templates directory not set');
        }

        return new Template($this, $templatesDirectory, $fileName);
    }
}
