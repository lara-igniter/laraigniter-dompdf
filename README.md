## DOMPDF Wrapper for Laraigniter

### Laraigniter wrapper for [Dompdf HTML to PDF Converter](https://github.com/dompdf/dompdf)

## Installation

### Laraigniter
Require this package in your composer.json and update composer. This will download the package and the dompdf + fontlib libraries also.

    composer require lara-igniter/laraigniter-dompdf

## Using

You can create a new DOMPDF instance and load a HTML string, file or view name. You can save it to a file, or stream (show in browser) or download.

```php
    use Laraigniter\DomPDF\Facade\Pdf;

    $pdf = Pdf::view('emails.invoice', [
        'data' => $data
    ]);
    
    return $pdf->download('example.pdf');
```

or use the app() controller instance:

```php
    $pdf = app('dompdf.wrapper');
    $pdf->html('<h1>Example PDF</h1>');
    
    return $pdf->stream();
```

Or use the facade:

You can chain the methods:

```php
    return Pdf::file(public_path('emails/example.html'))
        ->save('example.pdf')
        ->stream('example.pdf');
```

You can change the orientation and paper size, and hide or show errors (by default, errors are shown when debug is on)

```php
    Pdf::html($html)->setPaper('a4', 'landscape')
        ->setWarnings(false)
        ->save('example.pdf')
```

If you need the output as a string, you can get the rendered PDF with the output() function, so you can save/output it yourself.

### Configuration
The defaults configuration settings are set in `config/dompdf.php`.

You can still alter the dompdf options in your code before generating the pdf using this command:
```php
    Pdf::setOption(['dpi' => 150, 'defaultFont' => 'sans-serif']);
```

Available options and their defaults:
* __rootDir__: "{app_directory}/vendor/dompdf/dompdf"
* __tempDir__: "/tmp" _(available in config/dompdf.php)_
* __fontDir__: "{app_directory}/storage/fonts" _(available in config/dompdf.php)_
* __fontCache__: "{app_directory}/storage/fonts" _(available in config/dompdf.php)_
* __chroot__: "{app_directory}" _(available in config/dompdf.php)_
* __logOutputFile__: "/tmp/log.htm"
* __defaultMediaType__: "screen" _(available in config/dompdf.php)_
* __defaultPaperSize__: "a4" _(available in config/dompdf.php)_
* __defaultFont__: "serif" _(available in config/dompdf.php)_
* __dpi__: 96 _(available in config/dompdf.php)_
* __fontHeightRatio__: 1.1 _(available in config/dompdf.php)_
* __isPhpEnabled__: false _(available in config/dompdf.php)_
* __isRemoteEnabled__: false _(available in config/dompdf.php)_
* __isJavascriptEnabled__: true _(available in config/dompdf.php)_
* __isHtml5ParserEnabled__: true _(available in config/dompdf.php)_
* __allowedRemoteHosts__: null _(available in config/dompdf.php)_
* __isFontSubsettingEnabled__: false _(available in config/dompdf.php)_
* __debugPng__: false
* __debugKeepTemp__: false
* __debugCss__: false
* __debugLayout__: false
* __debugLayoutLines__: true
* __debugLayoutBlocks__: true
* __debugLayoutInline__: true
* __debugLayoutPaddingBox__: true
* __pdfBackend__: "CPDF" _(available in config/dompdf.php)_
* __pdflibLicense__: ""
* __adminUsername__: "user"
* __adminPassword__: "password"
* __artifactPathValidation__: null _(available in config/dompdf.php)_

#### Note: Remote access is disabled by default, to provide more security. Use with caution!

### Tip: UTF-8 support
In your templates, set the UTF-8 Metatag:

    <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>

### Tip: Page breaks
You can use the CSS `page-break-before`/`page-break-after` properties to create a new page.

    <style>
    .page-break {
        page-break-after: always;
    }
    </style>
    <h1>Page 1</h1>
    <div class="page-break"></div>
    <h1>Page 2</h1>

### License

This DOMPDF Wrapper for Laraigniter is open-sourced software licensed under the [MIT license](http://opensource.org/licenses/MIT)
