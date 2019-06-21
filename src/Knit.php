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

/**
 * Class Knit
 * @package Comely\Knit
 */
class Knit
{
    /** @var Directories */
    private $dirs;
    /** @var Modifiers */
    private $modifiers;

    /**
     * Knit constructor.
     */
    public function __construct()
    {
        $this->dirs = new Directories();
        $this->modifiers = new Modifiers();
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
}