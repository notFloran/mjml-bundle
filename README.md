# MjmlBundle

[![Latest Stable Version](https://poser.pugx.org/notfloran/mjml-bundle/v/stable.svg)](https://packagist.org/packages/notfloran/mjml-bundle)
[![Latest Unstable Version](https://poser.pugx.org/notfloran/mjml-bundle/v/unstable.svg)](https://packagist.org/packages/notfloran/mjml-bundle)

Bundle to use [MJML](https://mjml.io/) 3 and 4 with Symfony >= 3.

## Installation

### Applications that use Symfony Flex

Open a command console, enter your project directory and execute:

```console
$ composer require notfloran/mjml-bundle
```

### Applications that don't use Symfony Flex

#### Step 1: Download the Bundle

Open a command console, enter your project directory and execute the
following command to download the latest stable version of this bundle:

```console
$ composer require notfloran/mjml-bundle
```

This command requires you to have Composer installed globally, as explained
in the [installation chapter](https://getcomposer.org/doc/00-intro.md)
of the Composer documentation.

#### Step 2: Enable the Bundle

Then, enable the bundle by adding it to the list of registered bundles
in the `app/AppKernel.php` file of your project:

```php
// app/AppKernel.php

// ...
class AppKernel extends Kernel
{
    public function registerBundles()
    {
        $bundles = [
            // ...
            new NotFloran\MjmlBundle\MjmlBundle(),
        ];

        // ...
    }

    // ...
}
```

## Renderer

Choose from one of two renderers, the binary renderer and mjml-php.

### Binary

Install [MJML](https://mjml.io)

```bash
$ npm install mjml
```

Then you need to update the configuration:

```yaml
# config/packages/mjml.yaml
mjml:
  renderer: binary # default: binary
  options:
    binary: '%kernel.project_dir%/node_modules/.bin/mjml' # default: mjml
    node: '/Users/user/.nvm/versions/node/v10.16.0/bin/node' # default: null
    minify: true # default: false
    validation_level: skip # default: strict. See https://mjml.io/documentation/#validating-mjml
```

The `node` option is there for those who have problems with `$PATH`, see [#35](https://github.com/notFloran/mjml-bundle/issues/35).

### mjml-php

Install [mjml-php](https://github.com/alekitto/mjml-php), a PHP extension that embeds [MRML](https://github.com/jdrouet/mrml), 
the MJML rendering engine written in Rust, so that you can render MJML templates directly from PHP without shelling out to a CLI tool. 

Then you need to update the configuration:

```yaml
# config/packages/mjml.yaml
mjml:
  renderer: mjml_php
```

### Custom

First you must create a class which implements `NotFloran\MjmlBundle\Renderer\RendererInterface`, then declare it as a service.

And finally you have to change the configuration:

````yaml
# config/packages/mjml.yaml
mjml:
    renderer: 'service'
    options:
        service_id: 'App\Mjml\MyCustomRenderer'
````

### PHP Config

If you're using symfony 5 and want to configure this bundle with PHP files instead of YAML:

```php
// config/packages/mjml.php
<?php declare(strict_types=1);

use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;

return static function (ContainerConfigurator $configurator): void
{
    $configurator->extension('mjml', [
        'renderer' => 'binary',
        'options' => [
            'binary' => '%kernel.project_dir%/node_modules/.bin/mjml',
            'minify' => true,
            'validation_level' => 'skip'
        ]
    ]);
};
```

### API

The bundle has no official integration with the [MJML API](https://mjml.io/api).

You can create your own integration by using [juanmiguelbesada/mjml-php](https://packagist.org/packages/juanmiguelbesada/mjml-php) and following this gist : https://gist.github.com/notFloran/ea6bab137be628f6a0c19054e08e6906.

## Usage

### Use "mjml" twig tag

```twig
{# mail/example.mjml.twig #}
{% block email_content %}
    {% mjml %}
    <mjml>
        <mj-body>
                <mj-section>
                    <mj-column>

                        <mj-image width="100px" src="https://mjml.io/assets/img/logo-small.png"></mj-image>

                        <mj-divider border-color="#F45E43"></mj-divider>

                        <mj-text font-size="20px" color="#F45E43" font-family="helvetica">
                            Hello {{ name }} from MJML and Symfony
                        </mj-text>

                    </mj-column>
                </mj-section>
        </mj-body>
    </mjml>
    {% endmjml %}
{% endblock %}
```

```php
public function sendEmail(MailerInterface $mailer)
{
    // The MJMl body is rendered by the mjml tag in the twig file
    $htmlBody = $this->renderView('templates/mail/example.mjml.twig', ['name' => 'Floran']);

    $email = (new Email())
        ->from('my-app@example.fr')
        ->to('me@example.fr')
        ->subject('Hello from MJML!')
        ->html($htmlBody);

    $mailer->send($email);

    // ...
}
```

### Use "mjml" service

```twig
{# templates/mail/example.mjml.twig #}
<mjml>
    <mj-body>
            <mj-section>
                <mj-column>

                    <mj-image width="100px" src="https://mjml.io/assets/img/logo-small.png"></mj-image>

                    <mj-divider border-color="#F45E43"></mj-divider>

                    <mj-text font-size="20px" color="#F45E43" font-family="helvetica">
                        Hello {{ name }} from MJML and Symfony
                    </mj-text>

                </mj-column>
            </mj-section>
    </mj-body>
</mjml>
```

```php
use NotFloran\MjmlBundle\Renderer\RendererInterface;

// ...

public function sendEmail(MailerInterface $mailer, RendererInterface $mjml)
{
    $mjmlBody = $this->renderView('templates/mail/example.mjml.twig', ['name' => 'Floran']);
    $htmlBody = $mjml->render($mjmlBody);

    $email = (new Email())
        ->from('my-app@example.fr')
        ->to('me@example.fr')
        ->subject('Hello from MJML!')
        ->html($htmlBody);

    $mailer->send($email);

    // ...
}
```

## SwiftMailer integration

*❗ This integration is deprecated and will be removed in the next major version.*

Declare the following service:

```yaml
NotFloran\MjmlBundle\SwiftMailer\MjmlPlugin:
    tags: [swiftmailer.default.plugin]
```

Create a SwiftMailer message with a MJML body (without `{% mjml %}`) and with `text/mjml` as content-type:

```php
$message = (new \Swift_Message('Hello Email'))
    ->setFrom('send@example.com')
    ->setTo('recipient@example.com')
    ->setBody(
        $this->renderView('mail/example.mjml.twig'),
        'text/mjml'
    );

$mailer->send($message);
```

The plugin will automatically render the MJML body and replace the body with the rendered HTML.

In the case where a spool is used: the MJML content is save in the spool and render when the spool is flushed.

## License

[MjmlBundle](https://github.com/notFloran/mjml-bundle) is licensed under the [MIT license](LICENSE).
