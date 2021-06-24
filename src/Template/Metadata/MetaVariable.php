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

namespace Comely\Knit\Template\Metadata;

use Comely\Knit\Exception\MetadataException;

/**
 * Class MetaVariable
 * @package Comely\Knit\Template\Metadata
 */
class MetaVariable implements MetaValueInterface
{
    /** @var string|int|float */
    private string|int|float $value;

    /**
     * MetaVariable constructor.
     * @param $value
     * @throws MetadataException
     */
    public function __construct($value)
    {
        $this->value = match (gettype($value)) {
            "string", "integer", "double" => $value,
            "boolean" => $value ? "true" : "false",
            "NULL" => "null",
            default => throw new MetadataException(sprintf('MetaVariable cannot accept value of type "%s"', gettype($value))),
        };
    }

    /**
     * @return string|int|float
     */
    public function value(): string|int|float
    {
        return $this->value;
    }
}
