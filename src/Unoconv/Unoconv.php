<?php

namespace Unoconv;

use Symfony\Component\Process\Process;
use Symfony\Component\Process\ExecutableFinder;

class Unoconv
{

    protected $pathfile;
    protected $binary;

    /**
     *
     * @var \Monolog\Logger
     */
    protected $logger;

    public function __construct($binary, \Monolog\Logger $logger = null)
    {
        $this->binary = $binary;

        if ( ! $logger)
        {
            $logger = new \Monolog\Logger('default');
            $logger->pushHandler(new \Monolog\Handler\NullHandler());
        }

        $this->logger = $logger;
    }

    public function open($pathfile)
    {
        if ( ! file_exists($pathfile))
        {
            $this->logger->addError(sprintf('Request to open %s failed', $pathfile));

            throw new Exception\InvalidFileArgumentException(sprintf('File %s does not exists', $pathfile));
        }

        $this->logger->addInfo(sprintf('Unoconv opens %s', $pathfile));

        $this->pathfile = $pathfile;
    }

    public function saveAs($format, $pathfile, $pageRange = null)
    {
        if ( ! $this->pathfile)
        {
            throw new Exception\LogicException('No file open');
        }

        $pageRangeOpt = preg_match('/\d+-\d+/', $pageRange) ? (' -e PageRange=' . $pageRange) : '';

        $cmd = sprintf(
          '%s --format=%s %s --stdout %s', $this->binary, escapeshellarg($format), $pageRangeOpt, escapeshellarg($this->pathfile)
        );

        try
        {
            $process = new Process($cmd);
            $process->run();
        }
        catch (\RuntimeException $e)
        {
            throw new Exception\RuntimeException('Unable to execute unoconv');
        }

        if ( ! $process->isSuccessful())
        {
            throw new Exception\RuntimeException('Unable to execute unoconv');
        }

        if ( ! is_writable(dirname($pathfile)) || ! file_put_contents($pathfile, $process->getOutput()))
        {
            throw new Exception\RuntimeException('Unable write output file');
        }

        return true;
    }

    public static function load(\Monolog\Logger $logger = null)
    {
        $finder = new ExecutableFinder();

        if (null === $binary = $finder->find('unoconv'))
        {
            throw new Exception\BinaryNotFoundException('Binary not found');
        }

        return new static($binary, $logger);
    }

}
