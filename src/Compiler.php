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
use Comely\Filesystem\Exception\PathOpException;
use Comely\Filesystem\File;
use Comely\Knit\Compiler\CompiledTemplate;
use Comely\Knit\Compiler\Parser;
use Comely\Knit\Compiler\Parser\Variables;
use Comely\Knit\Exception\CompilerException;
use Comely\Knit\Exception\ParseException;

/**
 * Class Compiler
 * @package Comely\Knit
 */
class Compiler
{
    /** @var Knit */
    private $knit;
    /** @var Directory */
    private $directory;
    /** @var File */
    private $file;
    /** @var string */
    private $fileName;
    /** @var string */
    private $eolChar;

    /**
     * Compiler constructor.
     * @param Knit $knit
     * @param Directory $directory
     * @param string $fileName
     * @throws CompilerException
     */
    public function __construct(Knit $knit, Directory $directory, string $fileName)
    {
        if (!$directory->permissions()->read()) {
            throw new CompilerException(sprintf('Template "%s" directory is not readable', $fileName));
        }

        try {
            $file = $directory->file($fileName);
            if (!$file->permissions()->read()) {
                throw new CompilerException(sprintf('Template file "%s" is not readable', $fileName));
            }
        } catch (PathException $e) {
            throw new CompilerException(sprintf('Template file "%s" not found', $fileName));
        }

        $this->knit = $knit;
        $this->directory = $directory;
        $this->file = $file;
        $this->fileName = $fileName;
        $this->eolChar = PHP_EOL;
    }

    /**
     * @param Variables|null $variables
     * @return string
     * @throws CompilerException
     */
    public function parse(?Variables $variables = null): string
    {
        try {
            return (new Parser($this->knit, $this->file->read(), $variables))
                ->parse();
        } catch (PathOpException $e) {
            throw new CompilerException(
                sprintf('An error occurred while reading template file "%s"', $this->fileName)
            );
        } catch (\Exception $e) {
            if ($e instanceof CompilerException) {
                throw $e;
            }

            if ($e instanceof ParseException) {
                throw new CompilerException(
                    sprintf(
                        'Parsing error "%s" in template file "%s" on line %d near "%s"',
                        $e->getMessage(),
                        $this->fileName,
                        $e->line(),
                        $e->token()
                    )
                );
            }

            throw new CompilerException(
                sprintf('[%s][%d] %s', get_class($e), $e->getCode(), $e->getMessage())
            );
        }
    }

    /**
     * @return CompiledTemplate
     * @throws CompilerException
     */
    public function compile(): CompiledTemplate
    {
        $compilerDirectory = $this->knit->dirs()->compiler;
        if (!$compilerDirectory) {
            throw new CompilerException('Knit compiler directory not set');
        } elseif (!$compilerDirectory->permissions()->write()) {
            throw new CompilerException('Knit compiler directory not writable');
        }

        $timer = microtime(true); // Start timer

        // new CompiledTemplate instance
        $compiledTemplate = new CompiledTemplate();
        $compiledTemplate->templateName = $this->fileName;
        $compiledTemplate->timeStamp = time();
        $compiledTemplate->timer = microtime(true) - $timer;

        // Compile parsed template into PHP code
        $compile = '<?php' . $this->eolChar;
        $compile .= sprintf('$comelyKnit = "%s";%s', Knit::VERSION, $this->eolChar);
        $compile .= sprintf('$comelyKnitParseTimer = "%s";%s', $compiledTemplate->timer, $this->eolChar);
        $compile .= sprintf('$comelyKnitTimeStamp = %s;%s?>', $compiledTemplate->timeStamp, $this->eolChar);
        $compile .= $this->parse(); // Parse

        // Compile file name
        $compiledTemplate->compiledFile = sprintf(
            'knit_%s_%d.php',
            md5($this->fileName),
            mt_rand(0, 1000)
        );

        // Write
        try {
            $compilerDirectory->write($compiledTemplate->compiledFile, $compile, false, true);
        } catch (PathException $e) {
            throw new CompilerException('Failed to write compiled knit template file');
        }

        return $compiledTemplate;
    }
}