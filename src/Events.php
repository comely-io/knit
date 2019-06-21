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

use Comely\Utils\Events\Event;
use Comely\Utils\Events\EventsRegister;

/**
 * Class Events
 * @package Comely\Knit
 */
class Events
{
    /** @var EventsRegister */
    private $register;

    /**
     * Events constructor.
     */
    public function __construct()
    {
        $this->register = new EventsRegister();
    }

    /**
     * @return Event
     */
    public function onTemplatePrepared(): Event
    {
        return $this->register->on("on.template.prepared");
    }
}