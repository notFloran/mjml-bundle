<?php

namespace NotFloran\MjmlBundle\Renderer;

use Mjml\Mjml;
use Mjml\Exception\RenderException;

final class MjmlPhpRenderer implements RendererInterface
{
    public function render(string $mjmlContent): string
    {
        try {
            $mjml = new Mjml(["disable_comments" => true]);

            $result = $mjml->render($mjmlContent);

            return $result->getBody();
        } catch (RenderException $e) {
            throw new \RuntimeException('MJML rendering exception: ' . $e->getMessage(), 0, $e);
        }
    }
}
