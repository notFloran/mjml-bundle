# Upgrade

## 4.0.0

`NotFloran\MjmlBundle\SwiftMailer\MjmlPlugin` has been removed. If you still need SwiftMailer support, copy the class into your project.

## 3.4.0

`NotFloran\MjmlBundle\SwiftMailer\MjmlPlugin` is deprecated and will be removed in v4, you must copy and paste the class in your project.

## 3.0.0

The class `NotFloran\MjmlBundle\Mjml` is replaced with `NotFloran\MjmlBundle\Renderer\BinaryRenderer`.

### Configuration

Most of the options are now in `mjml.options`:

Before:

```
# config/packages/mjml.yaml
mjml:
    bin: 'mjml'
    mimify: true
```

After:


```
# config/packages/mjml.yaml
mjml:
  renderer: binary
  options:
    binary: 'mjml'
    minify: true
```

### Services

The service `@NotFloran\MjmlBundle\Mjml` no longer exists, you must use `@mjml` or `@NotFloran\MjmlBundle\Renderer\RendererInterface`.
