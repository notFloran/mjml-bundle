<?php

namespace NotFloran\MjmlBundle\Renderer;

use Symfony\Component\Process\Process;

final class BinaryRenderer implements RendererInterface
{
    /**
     * @var string
     */
    private $bin;

    /**
     * @var bool
     */
    private $minify;

    /**
     * @var string
     */
    private $validationLevel;

    /**
     * @var int|null
     */
    private $mjmlVersion;

    /**
     * @var string|null
     */
    private $node;

    public function __construct(string $bin, bool $minify, string $validationLevel, ?string $node = null, ?int $mjmlVersion = null)
    {
        $this->bin = $bin;
        $this->minify = $minify;
        $this->validationLevel = $validationLevel;
        $this->node = $node;
        $this->mjmlVersion = $mjmlVersion;
    }

    public function getMjmlVersion(): int
    {
        if (null === $this->mjmlVersion) {
            $command = [];
            if ($this->node) {
                $command[] = $this->node;
            }

            array_push($command, $this->bin, '--version');

            $process = new Process($command);
            $process->mustRun();

            $this->mjmlVersion = 4;
            if (1 === preg_match('/mjml-core:\s*(\d+)\./', $process->getOutput(), $matches)) {
                $this->mjmlVersion = (int) $matches[1];
            }
        }

        return $this->mjmlVersion;
    }

    public function render(string $mjmlContent): string
    {
        $version = $this->getMjmlVersion();

        $command = [];
        if ($this->node) {
            $command[] = $this->node;
        }

        array_push($command, $this->bin, '-i', '-s');

        $strictArgument = '-l';
        if ($version >= 4) {
            $strictArgument = '--config.validationLevel';
        }

        array_push($command, $strictArgument, $this->validationLevel);

        if (true === $this->minify) {
            if ($version >= 4) {
                array_push($command, '--config.minify', 'true');
            } else {
                $command[] = '-m';
            }
        }

        // Create process
        $process = new Process($command);
        $process->setInput($mjmlContent);
        $process->mustRun();

        return $process->getOutput();
    }
}
