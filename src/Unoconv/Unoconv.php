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

use Alchemy\BinaryDriver\AbstractBinary;
use Psr\Log\LoggerInterface;
use Unoconv\Exception\RuntimeException;
use Unoconv\Exception\InvalidFileArgumentException;

class Unoconv extends AbstractBinary
{
    const FORMAT_PDF = 'pdf';

    public function transcode($input, $format, $output, $pageRange = null)
    {
        if (!file_exists($input)) {
            $this->logger->error(sprintf('RUnoconv failed to open %s', $input));
            throw new InvalidFileArgumentException(sprintf('File %s does not exists', $input));
        }

        $arguments = array(
            '--format=' . $format,
            '--stdout'
        );

        if (preg_match('/\d+-\d+/', $pageRange)) {
            $arguments[] = '-e';
            $arguments[] = 'PageRange=' . $pageRange;
        }

        $arguments[] = $input;

        $process = $this->factory->create($arguments);

        $this->logger->info(sprintf('Executing unoconv command %s', $process->getCommandline()));

        try {
            $process->run();
        } catch (\RuntimeException $e) {
            throw new RuntimeException('Unable to execute unoconv command', $e->getCode(), $e);
        }

        if (!$process->isSuccessful()) {
            throw new RuntimeException(sprintf(
                'Unable to execute unoconv, command %s failed', $process->getCommandLine()
            ));
        }

        if (!is_writable(dirname($output)) || !file_put_contents($output, $process->getOutput())) {
            throw new RuntimeException(sprintf('Unable to write to output file `%s`', $output));
        }

        return $this;
    }

    public static function create(LoggerInterface $logger = null, $conf = array())
    {
        return static::load('unoconv', $logger, $conf);
    }
}
