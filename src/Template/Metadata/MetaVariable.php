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

namespace Comely\Knit\Template\Metadata;

use Comely\Knit\Exception\MetadataException;

/**
 * Class MetaVariable
 * @package Comely\Knit\Template\Metadata
 */
class MetaVariable implements MetaValueInterface
{
    /** @var string|int|bool */
    private $value;

    /**
     * MetaVariable constructor.
     * @param $value
     * @throws MetadataException
     */
    public function __construct($value)
    {
        $valueType = gettype($value);
        switch ($valueType) {
            case "string":
            case "integer":
            case "double":
                $this->value = $value;
                break;
            case "boolean":
                $this->value = $value ? "true" : "false";
                break;
            case "NULL":
                $this->value = "null";
                break;
            default:
                throw new MetadataException(sprintf('MetaVariable cannot accept value of type "%s"', $valueType));
        }
    }

    /**
     * @return bool|int|string
     */
    public function value()
    {
        return $this->value;
    }
}