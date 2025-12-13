<?php

namespace NotFloran\MjmlBundle\Tests\Twig;

use NotFloran\MjmlBundle\Renderer\BinaryRenderer;
use NotFloran\MjmlBundle\Tests\AbstractTestCase;
use NotFloran\MjmlBundle\Twig\Extension;
use Twig\Environment;
use Twig\Loader\ArrayLoader as TwigArrayLoader;
use Twig\TemplateWrapper;

class ExtensionTest extends AbstractTestCase
{
    public function testRenderUsingTwigExtension()
    {
        $mjmlBody = file_get_contents(__DIR__.'/../fixtures/basic.mjml');
        $twigTemplate = <<<TWIG
{% mjml %}
$mjmlBody
{% endmjml %}
TWIG;

        $template = $this->getTemplate($twigTemplate);

        $html = $template->render([]);
        $this->assertStringContains('html', $html);
        $this->assertStringContains('Hello Floran from MJML and Symfony', $html);
    }

    private function getTemplate($template): TemplateWrapper
    {
        $mjmlRenderer = new BinaryRenderer($this->getMjmlBinary(), false, 'strict');

        $loader = new TwigArrayLoader(['index' => $template]);
        $twig = new Environment($loader, ['debug' => true, 'cache' => false]);
        $twig->addExtension(new Extension($mjmlRenderer));

        return $twig->load('index');
    }

    /**
     * Allow to use PHPUnit < and > 9.
     *
     * @param $needle
     * @param $haystack
     *
     * @return void
     */
    public function assertStringContains($needle, $haystack)
    {
        if (method_exists($this, 'assertStringContainsString')) {
            parent::assertStringContainsString($needle, $haystack);

            return;
        }

        $this->assertContains($needle, $haystack);
    }
}
