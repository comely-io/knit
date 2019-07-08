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

use Comely\Filesystem\Directory;
use Comely\Filesystem\Exception\PathException;
use Comely\Filesystem\Exception\PathNotExistException;
use Comely\Knit\Exception\CachingException;
use Comely\Knit\Exception\CompilerException;
use Comely\Knit\Exception\SandboxException;
use Comely\Knit\Exception\TemplateException;
use Comely\Knit\Template\Data;
use Comely\Knit\Template\Metadata;
use Comely\Knit\Template\Sandbox;

/**
 * Class Template
 * @package Comely\Knit
 */
class Template
{
    /** @var Knit */
    private $knit;
    /** @var Data */
    private $data;
    /** @var Metadata */
    private $metadata;
    /** @var null|Caching */
    private $caching;
    /** @var Directory */
    private $directory;
    /** @var string */
    private $fileName;
    /** @var string */
    private $filePath;
    /** @var bool */
    private $deleteCompiled;

    /**
     * Template constructor.
     * @param Knit $knit
     * @param Directory $directory
     * @param string $fileName
     * @throws TemplateException
     */
    public function __construct(Knit $knit, Directory $directory, string $fileName)
    {
        try {
            $filePath = $directory->suffix($fileName);
            if (pathinfo($filePath, PATHINFO_EXTENSION) !== "knit") {
                throw new TemplateException('Template files must have ".knit" extension');
            }
        } catch (TemplateException $e) {
            throw $e;
        } catch (\Exception $e) {
            throw new TemplateException('Invalid template file name');
        }

        $this->knit = $knit;
        $this->data = new Data();
        $this->metadata = new Metadata();
        $this->directory = $directory;
        $this->fileName = $fileName;
        $this->filePath = $directory->suffix($fileName);
        $this->deleteCompiled = true;
    }

    /**
     * @return string
     */
    public function name(): string
    {
        return $this->fileName;
    }

    /**
     * @return string
     */
    public function path(): string
    {
        return $this->filePath;
    }

    /**
     * @param bool $bool
     * @return Template
     */
    public function deleteCompiledTemplate(bool $bool): self
    {
        $this->deleteCompiled = $bool;
        return $this;
    }

    /**
     * @return Caching
     * @throws CachingException
     */
    public function caching(): Caching
    {
        if ($this->caching) {
            return $this->caching;
        }

        if (!$this->knit->dirs()->cache) {
            throw new CachingException('Cache directory is not set');
        }

        // Clone Knit's caching instance
        $this->caching = clone $this->knit->caching();
        return $this->caching;
    }

    /**
     * @param string $key
     * @param $value
     * @return Template
     * @throws TemplateException
     */
    public function assign(string $key, $value): self
    {
        $this->data->push($key, $value);
        return $this;
    }

    /**
     * @param string $key
     * @param Metadata\MetaValueInterface $value
     * @return Template
     * @throws Exception\MetadataException
     */
    public function metadata(string $key, Metadata\MetaValueInterface $value): self
    {
        $this->metadata->add($key, $value);
        return $this;
    }

    /**
     * @return null|string
     * @throws CachingException
     */
    private function cached(): ?string
    {
        if (!$this->caching) {
            return null;
        }

        // Get cached file ID and directory
        $cacheFileId = md5($this->filePath);
        $cachedFileName = null;
        $cachingType = null;
        $cachingDirectory = $this->knit->dirs()->cache;
        if (!$cachingDirectory) {
            throw new CachingException('Cache directory is not set');
        }

        // Determine cached file name
        $sessionId = $this->caching->sessionToken;
        if ($this->caching->type === Caching::AGGRESSIVE && $sessionId) {
            $cachingType = Caching::AGGRESSIVE;
            $cachedFileName = sprintf('knit_%s-%s.knit', $cacheFileId, $sessionId);
        } elseif ($this->caching->type === Caching::NORMAL) {
            $cachingType = Caching::NORMAL;
            $cachedFileName = sprintf('knit_%s.php', $cacheFileId);
        }

        if ($cachingType && $cachedFileName) {
            try {
                $cachedFile = $cachingDirectory->file($cachedFileName);

                // Check for expiry
                if ($this->caching->ttl) {
                    $cachedFileTime = $cachedFile->timestamps()->modified();
                    if ((time() - $cachedFileTime) >= $this->caching->ttl) {
                        throw new \RuntimeException('Cached template file has expired');
                    }
                }

                if ($cachingType === Caching::AGGRESSIVE) {
                    // Read cached .knit template
                    try {
                        $cachedTemplate = $cachedFile->read();
                    } catch (PathException $e) {
                        throw new CachingException('Cached file could not be read');
                    }

                    $cachedStart = substr($cachedTemplate, 0, 6);
                    $cachedEnd = substr($cachedTemplate, -6);
                    if (!$cachedStart !== "~knit:" || $cachedEnd !== ":knit~") {
                        throw new CachingException('Bad or incomplete cached knit template');
                    }

                    return substr($cachedTemplate, 6, -6);
                } elseif ($cachingType === Caching::NORMAL) {
                    // Run compiled template in sandbox
                    try {
                        return (new Sandbox($cachedFile, $this->data))->run();
                    } catch (SandboxException $e) {
                        throw new CachingException($e->getMessage());
                    }
                }
            } catch (PathNotExistException $e) {
                // Do nothing if cached file does not exist
            } catch (\Exception $e) {
                // CachingException messages will be triggered as E_USER_WARNING error
                // All other exceptions will be ignored
                if ($e instanceof CachingException) {
                    trigger_error($e->getMessage(), E_USER_WARNING);
                }

                // Check if cache file exists
                if (isset($cachedFile)) {
                    // Not being used indicates this needs to be deleted
                    try {
                        $cachedFile->delete();
                    } catch (PathException $e) {
                        trigger_error('Failed to delete cached template file', E_USER_WARNING);
                        trigger_error($e->getMessage(), E_USER_WARNING);
                    }
                }
            }
        }

        return null;
    }

    /**
     * @return string
     * @throws CompilerException
     * @throws Exception\MetadataException
     * @throws SandboxException
     */
    private function compile(): string
    {
        // Compile knit template
        $compiled = (new Compiler($this->knit, $this->directory, $this->fileName))
            ->compile();

        // Get compiled file
        try {
            $compiledFile = $this->knit->dirs()->compiler->file($compiled->compiledFile);
        } catch (PathException $e) {
            throw new CompilerException(
                sprintf('Failed to located compiled knit template file "%s"', $compiled->compiledFile)
            );
        }

        // Run in sandbox and return output
        $output = (new Sandbox($compiledFile, $this->data))
            ->run();

        // Caching
        if ($this->caching) {
            $cacheFileId = md5($this->filePath);
            $sessionId = $this->caching->sessionToken;
            if ($this->caching->type === Caching::AGGRESSIVE && $sessionId) {
                $cacheFileName = sprintf('knit_%s-%s.knit', $cacheFileId, $sessionId);
                $cacheContents = "~knit:" . $output . ":knit~";
            } elseif ($this->caching->type === Caching::NORMAL) {
                $cacheFileName = sprintf('knit_%s.php', $cacheFileId);
                try {
                    $cacheContents = $compiledFile->read();
                } catch (PathException $e) {
                    trigger_error('Failed to read compiled knit file for cache', E_USER_WARNING);
                }
            }

            // Write cache
            if (isset($cacheFileName, $cacheContents)) {
                $cachingDirectory = $this->knit->dirs()->cache;
                if (!$cachingDirectory) {
                    trigger_error('Failed to cache knit template, caching directory is not set', E_USER_WARNING);
                }

                try {
                    $cachingDirectory->write($cacheFileName, $cacheContents, false, true);
                } catch (PathException $e) {
                    trigger_error('Failed to write knit cache template file', E_USER_WARNING);
                }
            }
        }

        // Metadata
        $this->metadata("timer.compile", new Metadata\MetaVariable($compiled->timer));

        // Delete compiled file
        if ($this->deleteCompiled) {
            try {
                $compiledFile->delete();
            } catch (PathException $e) {
                trigger_error('Failed to delete compiled knit template PHP file', E_USER_WARNING);
            }
        }

        // Return output string
        return $output;
    }

    /**
     * @return string
     * @throws CachingException
     * @throws CompilerException
     * @throws Exception\MetadataException
     * @throws SandboxException
     * @throws TemplateException
     */
    public function knit(): string
    {
        $timer = microtime(true);
        $template = $this->cached() ?? $this->compile() ?? null;
        if (!is_string($template)) {
            throw new TemplateException('Failed to read cached or compile fresh knit template');
        }

        $this->knit->events()->onTemplatePrepared()->trigger([$this]);

        // Process metadata
        foreach ($this->metadata as $key => $value) {
            $metaValue = null;
            if ($value instanceof Metadata\MetaVariable) {
                $metaValue = $value->value();
            } elseif ($value instanceof Metadata\MetaTemplate) {
                try {
                    $metaTemplate = $this->knit->template($value->template());
                    $metaTemplate->caching()->disable(); // Disable caching
                    $value->assignData($metaTemplate);
                    $metaValue = $metaTemplate->knit();
                } catch (TemplateException $e) {
                    $metaValue = sprintf(
                        'An error occurred while parsing meta template "%s". [%s] %s',
                        $value->template(),
                        get_class($e),
                        $e->getMessage()
                    );
                }
            }

            if ($metaValue || is_string($metaValue)) {
                $template = str_replace('%[%' . $key . '%]%', $metaValue, $template);
            }
        }

        // Timer
        $template = str_replace('%[%timer.knit%]%', round((microtime(true) - $timer), 5), $template);

        // Return processed template
        return $template;
    }
}