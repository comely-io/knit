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

namespace Comely\Knit\Compiler;

/**
 * Class CompiledTemplate
 * @package Comely\Knit\Compiler
 */
class CompiledTemplate
{
    /** @var string */
    public $compiledFile;
    /** @var string */
    public $templateName;
    /** @var int */
    public $timeStamp;
    /** @var float */
    public $timer;
}