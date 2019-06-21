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
 * Class Caching
 * @package Comely\Knit
 * @property-read int $type
 * @property-read int $ttl
 * @property-read null|string $sessionToken
 */
class Caching
{
    public const NONE = 0x0a;
    public const NORMAL = 0x14;
    public const AGGRESSIVE = 0x1e;

    /** @var int */
    private $type;
    /** @var int */
    private $ttl;
    /** @var string|null */
    private $sessionToken;

    /**
     * Caching constructor.
     */
    public function __construct()
    {
        $this->disable();
        $this->ttl = 0;
    }

    /**
     * @param string $prop
     * @return mixed
     */
    public function __get(string $prop)
    {
        switch ($prop) {
            case "type":
            case "ttl":
            case "sessionToken":
                return $this->$prop;
        }

        throw new \DomainException('Cannot get value of inaccessible property');
    }

    /**
     * @param int $secs
     * @return Caching
     */
    public function ttl(int $secs): self
    {
        if ($secs >= 0) {
            $this->ttl = $secs;
            return $this;
        }

        throw new \InvalidArgumentException('Invalid caching TTL value');
    }

    /**
     * @return Caching
     */
    public function disable(): self
    {
        $this->type = self::NONE;
        $this->sessionToken = null;
        return $this;
    }

    /**
     * @return Caching
     */
    public function enable(): self
    {
        $this->disable();
        $this->type = self::NORMAL;
        return $this;
    }

    /**
     * @param string $sessionIdOrToken
     * @return Caching
     */
    public function aggressive(string $sessionIdOrToken): self
    {
        if (!preg_match('/^\w+$/', $sessionIdOrToken)) {
            throw new \InvalidArgumentException('Invalid session ID or token for Knit caching');
        }

        $this->disable();
        $this->type = self::AGGRESSIVE;
        $this->sessionToken = $sessionIdOrToken;
        return $this;
    }
}