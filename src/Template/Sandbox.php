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

namespace Comely\Knit\Template;

use Comely\Filesystem\File;
use Comely\Knit\Exception\SandboxException;

/**
 * Class Sandbox
 * @package Comely\Knit\Template
 */
class Sandbox
{
    /** @var array */
    private $data;
    /** @var File */
    private $file;

    /**
     * Sandbox constructor.
     * @param File $compiledFile
     * @param Data $data
     */
    public function __construct(File $compiledFile, Data $data)
    {
        $this->data = $data->array();
        $this->file = $compiledFile;
    }

    /**
     * @return string
     * @throws SandboxException
     */
    public function run(): string
    {
        ob_start();
        /** @noinspection PhpIncludeInspection */
        include($this->file->path());
        $output = ob_get_contents();
        ob_end_clean();

        if (!is_string($output)) {
            throw new SandboxException('Sandbox failed to generate template output');
        }

        if (!isset($comelyKnit)) {
            throw new SandboxException('Bad or incorrectly compiled knit template');
        }

        return $output;
    }
}