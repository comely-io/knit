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

use Comely\Knit\Exception\TemplateException;

/**
 * Class Data
 * @package Comely\Knit\Template
 */
class Data
{
    /** @var string[] */
    private const RESERVED = ["knit"];

    /** @var array */
    private array $data = [];

    /**
     * Data constructor.
     */
    public function __construct()
    {
        $this->data["knit"] = [
            "now" => time(),
            "get" => $_GET
        ];
    }

    /**
     * @param string $key
     * @param $value
     * @throws TemplateException
     */
    public function push(string $key, $value): void
    {
        $key = strtolower($key); // Case-insensitivity
        if (!preg_match('/^\w+$/', $key)) {
            throw new TemplateException('Trying to assign data with an invalid key');
        }

        if (in_array($key, self::RESERVED)) {
            throw new TemplateException(sprintf('Data cannot be assigned to a reserved key "%s"', $key));
        }

        $valueType = gettype($value);
        switch ($valueType) {
            case "boolean":
            case "integer":
            case "double":
            case "string":
            case "NULL":
                $this->data[$key] = $value;
                return;
            case "array":
            case "object":
                $filtered = json_decode(json_encode($value), true);
                if (!is_array($filtered)) {
                    throw new TemplateException(
                        sprintf('Failed to assign "%s" value to key "%s"', $valueType, $key)
                    );
                }
                $this->data[$key] = $filtered;
                return;
            default:
                throw new TemplateException(
                    sprintf('Cannot assign data type "%s" to key "%s"', $valueType, $key)
                );

        }
    }

    /**
     * @return array
     */
    public function array(): array
    {
        return $this->data;
    }
}
