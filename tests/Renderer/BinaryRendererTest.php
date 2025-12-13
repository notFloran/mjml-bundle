<?php

namespace NotFloran\MjmlBundle\Tests\Renderer;

use NotFloran\MjmlBundle\Renderer\BinaryRenderer;
use NotFloran\MjmlBundle\Tests\AbstractTestCase;

class BinaryRendererTest extends AbstractTestCase
{
    public function testBasicRender()
    {
        $renderer = new BinaryRenderer($this->getMjmlBinary(), false, 'strict');
        $html = $renderer->render(file_get_contents(__DIR__.'/../fixtures/basic.mjml'));

        $this->assertStringContains('html', $html);
        $this->assertStringContains('Hello Floran from MJML and Symfony', $html);
    }

    public function testInvalidRender()
    {
        $this->expectException(\RuntimeException::class);

        $renderer = new BinaryRenderer($this->getMjmlBinary(), false, 'strict');
        $renderer->render(file_get_contents(__DIR__.'/../fixtures/invalid.mjml'));
    }

    public function testInvalidRenderWithSkipValidationLevel()
    {
        $renderer = new BinaryRenderer($this->getMjmlBinary(), false, 'skip');
        $html = $renderer->render(file_get_contents(__DIR__.'/../fixtures/invalid.mjml'));

        $this->assertStringContains('html', $html);
    }

    public function testInvalidRenderWithSoftValidationLevel()
    {
        $renderer = new BinaryRenderer($this->getMjmlBinary(), false, 'soft');
        $html = $renderer->render(file_get_contents(__DIR__.'/../fixtures/invalid.mjml'));

        $this->assertStringContains('html', $html);
    }

    public function testBinaryNotFound()
    {
        $this->expectException(\RuntimeException::class);

        $renderer = new BinaryRenderer('mjml-not-found', false, 'strict');
        $renderer->render(file_get_contents(__DIR__.'/../fixtures/basic.mjml'));
    }

    public function testUseNode()
    {
        $renderer = new BinaryRenderer($this->getMjmlBinary(), false, 'strict', $this->getNode());
        $html = $renderer->render(file_get_contents(__DIR__.'/../fixtures/basic.mjml'));

        $this->assertStringContains('html', $html);
    }

    /**
     * @dataProvider mjmlVersionDataProvider
     */
    public function testUseMjmlVersion(int $mjmlVersion)
    {
        $renderer = new BinaryRenderer($this->getMjmlBinary(), false, 'strict', null, $mjmlVersion);
        $html = $renderer->render(file_get_contents(__DIR__.'/../fixtures/basic.mjml'));

        $this->assertStringContains('html', $html);
        $this->assertStringContains('Hello Floran from MJML and Symfony', $html);
    }

    public function mjmlVersionDataProvider()
    {
        yield ['old version' => 3];
        yield ['actual version' => 4];
    }

    /**
     * Allow to use PHPUnit < and > 9
     *
     * @param $needle
     * @param $haystack
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
