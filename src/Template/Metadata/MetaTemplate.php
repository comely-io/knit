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
use Comely\Knit\Template;

/**
 * Class MetaTemplate
 * @package Comely\Knit\Template\Metadata
 */
class MetaTemplate implements MetaValueInterface
{
    /**
     * MetaTemplate constructor.
     * @param string $templateName
     * @param array $data
     */
    public function __construct(private string $templateName, private array $data)
    {
    }

    /**
     * @return string
     */
    public function template(): string
    {
        return $this->templateName;
    }

    /**
     * @param Template $template
     * @throws MetadataException
     * @throws \Comely\Knit\Exception\TemplateException
     */
    public function assignData(Template $template): void
    {
        foreach ($this->data as $key => $value) {
            if (!is_string($key)) {
                throw new MetadataException('Data assigned to MetaTemplate must be an assoc array');
            }

            $template->assign($key, $value);
        }
    }
}
