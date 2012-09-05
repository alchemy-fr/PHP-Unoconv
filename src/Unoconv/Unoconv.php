<?php

/*
 * This file is part of PHP-Unoconv.
 *
 * (c) Alchemy <info@alchemy.fr>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Unoconv;

use Monolog\Logger;
use Monolog\Handler\NullHandler;
use Symfony\Component\Process\Process;
use Symfony\Component\Process\ExecutableFinder;
use Unoconv\Exception\InvalidFileArgumentException;
use Unoconv\Exception\LogicException;
use Unoconv\Exception\RuntimeException;

class Unoconv
{
    protected $pathfile;
    protected $binary;

    /**
     *
     * @var \Monolog\Logger
     */
    protected $logger;

    const FORMAT_PDF = 'pdf';

    public function __construct($binary, Logger $logger = null)
    {
        if (!is_executable($binary)) {
            throw new RuntimeException(sprintf('`%s` is not executable', $binary));
        }

        $this->binary = $binary;

        if (!$logger) {
            $logger = new Logger('default');
            $logger->pushHandler(new NullHandler());
        }

        $this->logger = $logger;
    }

    public function open($pathfile)
    {
        if (!file_exists($pathfile)) {
            $this->logger->addError(sprintf('Request to open %s failed', $pathfile));

            throw new InvalidFileArgumentException(sprintf('File %s does not exists', $pathfile));
        }

        $this->logger->addInfo(sprintf('Unoconv opens %s', $pathfile));

        $this->pathfile = $pathfile;

        return $this;
    }

    public function saveAs($format, $pathfile, $pageRange = null)
    {
        if (!$this->pathfile) {
            throw new LogicException('No file open');
        }

        $pageRangeOpt = preg_match('/\d+-\d+/', $pageRange) ? (' -e PageRange=' . $pageRange) : '';

        $cmd = sprintf(
            '%s --format=%s %s --stdout %s', $this->binary, escapeshellarg($format), $pageRangeOpt, escapeshellarg($this->pathfile)
        );

        $this->logger->addInfo(sprintf('executing command %s', $cmd));

        try {
            $process = new Process($cmd);
            $process->run();
        } catch (\RuntimeException $e) {
            throw new RuntimeException('Unable to execute unoconv');
        }

        if (!$process->isSuccessful()) {
            throw new RuntimeException('Unable to execute unoconv');
        }

        if (!is_writable(dirname($pathfile)) || !file_put_contents($pathfile, $process->getOutput())) {
            throw new RuntimeException('Unable write output file');
        }

        return $this;
    }

    public function close()
    {
        $this->pathfile = null;

        return $this;
    }

    public static function load(Logger $logger = null)
    {
        $finder = new ExecutableFinder();

        if (null === $binary = $finder->find('unoconv')) {
            throw new RuntimeException('Binary not found');
        }

        return new static($binary, $logger);
    }
}
