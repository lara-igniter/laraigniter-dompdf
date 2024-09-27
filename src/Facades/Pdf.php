<?php

namespace Laraigniter\DomPDF\Facades;

use Elegant\Support\Facades\Facade;

/**
 * @method static \Laraigniter\DomPDF\PDF setBaseHost(string $baseHost)
 * @method static \Laraigniter\DomPDF\PDF setBasePath(string $basePath)
 * @method static \Laraigniter\DomPDF\PDF setCanvas(\Dompdf\Canvas $canvas)
 * @method static \Laraigniter\DomPDF\PDF setCallbacks(array $callbacks)
 * @method static \Laraigniter\DomPDF\PDF setCss(\Dompdf\Css\Stylesheet $css)
 * @method static \Laraigniter\DomPDF\PDF setDefaultView(string $defaultView, array $options)
 * @method static \Laraigniter\DomPDF\PDF setDom(\DOMDocument $dom)
 * @method static \Laraigniter\DomPDF\PDF setFontMetrics(\Dompdf\FontMetrics $fontMetrics)
 * @method static \Laraigniter\DomPDF\PDF setHttpContext(resource|array $httpContext)
 * @method static \Laraigniter\DomPDF\PDF setPaper(string|float[] $paper, string $orientation = 'portrait')
 * @method static \Laraigniter\DomPDF\PDF setProtocol(string $protocol)
 * @method static \Laraigniter\DomPDF\PDF setTree(\Dompdf\Frame\FrameTree $tree)
 * @method static \Laraigniter\DomPDF\PDF setWarnings(bool $warnings)
 * @method static \Laraigniter\DomPDF\PDF setOption(array|string $attribute, $value = null)
 * @method static \Laraigniter\DomPDF\PDF setOptions(array $options)
 * @method static \Laraigniter\DomPDF\PDF setHeader(string $textFormat = null, string $position = 'right', int $size = 6)
 * @method static \Laraigniter\DomPDF\PDF setFooter(string $textFormat = null, string $position = 'right', int $size = 6)
 * @method static \Laraigniter\DomPDF\PDF view(string $view, array $data = [], ?string $encoding = null)
 * @method static \Laraigniter\DomPDF\PDF html(string $string, ?string $encoding = null)
 * @method static \Laraigniter\DomPDF\PDF file(string $file)
 * @method static \Laraigniter\DomPDF\PDF setInfo(array $info)
 * @method static string output(array $options = [])
 * @method static \Laraigniter\DomPDF\PDF save()
 * @method static \CI_Output download(string $filename = 'document.pdf')
 * @method static \CI_Output stream(string $filename = 'document.pdf')
 *
 * @see \Laraigniter\DomPDF\PDF
 */
class Pdf extends Facade
{
    /**
     * Get the registered name of the component.
     *
     * @return string
     */
    protected static function getFacadeAccessor()
    {
        return 'dompdf.wrapper';
    }

    /**
     * Handle dynamic, static calls to the object.
     *
     * @param string $method
     * @param array<mixed> $args
     * @return mixed
     *
     * @throws \RuntimeException
     */
    public static function __callStatic($method, $args)
    {
        // Resolve a new instance, avoid using a cached instance
        $instance = app(static::getFacadeAccessor());

        return $instance->$method(...$args);
    }
}
