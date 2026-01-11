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
     * @var string|null
     */
    private $node;

    public function __construct(string $bin, bool $minify, string $validationLevel, ?string $node = null)
    {
        $this->bin = $bin;
        $this->minify = $minify;
        $this->validationLevel = $validationLevel;
        $this->node = $node;
    }

    public function render(string $mjmlContent): string
    {
        $command = [];
        if ($this->node) {
            $command[] = $this->node;
        }

        array_push($command, $this->bin, '-i', '-s');

        $strictArgument = '--config.validationLevel';

        array_push($command, $strictArgument, $this->validationLevel);

        if (true === $this->minify) {
            array_push($command, '--config.minify', 'true');
        }

        // Create process
        $process = new Process($command);
        $process->setInput($mjmlContent);
        $process->mustRun();

        return $process->getOutput();
    }
}
