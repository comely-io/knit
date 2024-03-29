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

namespace Comely\Knit\Template;

use Comely\Knit\Exception\MetadataException;
use Comely\Knit\Template\Metadata\MetaValueInterface;

/**
 * Class Metadata
 * @package Comely\Knit\Template
 */
class Metadata implements \Iterator
{
    /** @var array */
    private array $data = [];

    /**
     * @param string $key
     * @param MetaValueInterface $value
     * @throws MetadataException
     */
    public function add(string $key, MetaValueInterface $value): void
    {
        $key = strtolower($key);
        if (!preg_match('/^[\w.\-]{2,64}$/', $key)) {
            throw new MetadataException('Invalid assign key for a metadata value');
        }

        $this->data[$key] = $value;
    }

    /**
     * @return void
     */
    public function rewind(): void
    {
        reset($this->data);
    }

    /**
     * @return MetaValueInterface
     */
    public function current(): MetaValueInterface
    {
        return current($this->data);
    }

    /**
     * @return void
     */
    public function next(): void
    {
        next($this->data);
    }

    /**
     * @return string
     */
    public function key(): string
    {
        return key($this->data);
    }

    /**
     * @return bool
     */
    public function valid(): bool
    {
        return !is_null(key($this->data));
    }
}
