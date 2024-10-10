<?php

namespace Laraigniter\DomPDF;

use CI_Config;
use CI_Output;
use Dompdf\Adapter\CPDF;
use Dompdf\Dompdf;
use Dompdf\Options;
use Elegant\Filesystem\Filesystem;
use Elegant\Support\Facades\Storage;
use Elegant\Support\Str;
use Exception;

/**
 * A Laraigniter wrapper for Dompdf
 *
 * @package laraigniter-dompdf
 *
 * @method PDF setBaseHost(string $baseHost)
 * @method PDF setBasePath(string $basePath)
 * @method PDF setCanvas(\Dompdf\Canvas $canvas)
 * @method PDF setCallbacks(array $callbacks)
 * @method PDF setCss(\Dompdf\Css\Stylesheet $css)
 * @method PDF setDefaultView(string $defaultView, array $options)
 * @method PDF setDom(\DOMDocument $dom)
 * @method PDF setFontMetrics(\Dompdf\FontMetrics $fontMetrics)
 * @method PDF setHttpContext(resource|array $httpContext)
 * @method PDF setPaper(string|float[] $paper, string $orientation = 'portrait')
 * @method PDF setProtocol(string $protocol)
 * @method PDF setTree(\Dompdf\Frame\FrameTree $tree)
 * @method string getBaseHost()
 * @method string getBasePath()
 * @method \Dompdf\Canvas getCanvas()
 * @method array getCallbacks()
 * @method \Dompdf\Css\Stylesheet getCss()
 * @method \DOMDocument getDom()
 * @method \Dompdf\FontMetrics getFontMetrics()
 * @method resource getHttpContext()
 * @method Options getOptions()
 * @method \Dompdf\Frame\FrameTree getTree()
 * @method string getPaperOrientation()
 * @method float[] getPaperSize()
 * @method string getProtocol()
 */
class PDF
{
    /**
     * @var Dompdf
     */
    protected Dompdf $dompdf;

    /**
     * @var \CI_Config
     */
    protected CI_Config $config;

    /**
     * @var \Elegant\Filesystem\Filesystem
     */
    protected Filesystem $files;

    /**
     * @var object
     */
    protected object $view;

    /**
     * @var bool
     */
    protected bool $rendered = false;

    /**
     * @var bool
     */
    protected bool $showWarnings;

    /**
     * @var string
     */
    protected string $public_path;

    /**
     * Initialize the PDF class
     *
     * @param Dompdf $dompdf
     * @param CI_Config $config
     * @param \Elegant\Filesystem\Filesystem $files
     * @param object $view
     */
    public function __construct(Dompdf $dompdf, CI_Config $config, Filesystem $files, object $view)
    {
        $this->dompdf = $dompdf;
        $this->config = $config;
        $this->files = $files;
        $this->view = $view;

        $this->showWarnings = $this->config->config['dompdf']['show_warnings'] ?? false;
    }

    /**
     * Get the DomPDF instance
     *
     * @return \Dompdf\Dompdf
     */
    public function getDomPDF(): Dompdf
    {
        return $this->dompdf;
    }

    /**
     * Show or hide warnings
     *
     * @return $this
     */
    public function setWarnings(bool $warnings): self
    {
        $this->showWarnings = $warnings;

        return $this;
    }

    /**
     * Load an HTML string
     *
     * @param string|null $encoding Not used yet
     *
     * @return $this
     */
    public function html(string $string, ?string $encoding = null): self
    {
        $string = $this->convertEntities($string);

        $this->dompdf->loadHtml($string, $encoding);
        $this->rendered = false;

        return $this;
    }

    /**
     * Load an HTML file
     *
     * @param string $file
     *
     * @return $this
     *
     * @throws \Dompdf\Exception
     */
    public function file(string $file): self
    {
        $this->dompdf->loadHtmlFile($file);

        $this->rendered = false;

        return $this;
    }

    /**
     * Add metadata info
     *
     * @param array<string, string> $info
     *
     * @return $this
     */
    public function setInfo(array $info): self
    {
        foreach ($info as $name => $value) {
            $this->dompdf->add_info($name, $value);
        }
        return $this;
    }

    /**
     * Load a View and convert to HTML
     *
     * @param array<string, mixed> $data
     * @param string|null $encoding Not used yet
     *
     * @return $this
     */
    public function view(string $view, array $data = [], ?string $encoding = null): self
    {
        $html = $this->view->make($view, $data)->render();

        return $this->html($html, $encoding);
    }

    /**
     * Set/Change an option (or array of options) in Dompdf
     *
     * @param array<string, mixed>|string $attribute
     * @param null|mixed $value
     *
     * @return $this
     */
    public function setOption($attribute, $value = null): self
    {
        $this->dompdf->getOptions()->set($attribute, $value);
        return $this;
    }

    /**
     * Replace all the Options from DomPDF
     *
     * @param array<string, mixed> $options
     *
     * @return $this
     */
    public function setOptions(array $options, bool $mergeWithDefaults = false): self
    {
        if ($mergeWithDefaults) {
            $options = array_merge(app('dompdf.options', $options), $options);
        }

        $this->dompdf->setOptions(new Options($options));

        return $this;
    }

    /**
     * Set DOMPdf Header
     *
     * @param string|null $textFormat
     * @param string $position
     * @param int $size
     *
     * @return $this
     *
     * @throws Exception
     */
    public function setHeader(string $textFormat = null, string $position = 'right', int $size = 6): self
    {
        $this->render();

        $textFormat = $textFormat ?: $this->config->config['dompdf']['header']['text_format'];

        $position = $this->config->config['dompdf']['header']['position'] ?: $position;

        if (!is_null($textFormat)) {
            [$x, $y] = $this->getPosition($textFormat, 'top-' . $position, $size);

            $this->dompdf->getCanvas()->page_text($x, $y, $textFormat, $this->getFont(), $size);
        }

        return $this;
    }

    /**
     * Set DOMPdf Footer
     *
     * @param string|null $textFormat
     * @param string $position
     * @param int $size
     *
     * @return $this
     *
     * @throws Exception
     */
    public function setFooter(string $textFormat = null, string $position = 'right', int $size = 6): self
    {
        $this->render();

        $textFormat = $textFormat ?: $this->config->config['dompdf']['footer']['text_format'];

        $position = $this->config->config['dompdf']['footer']['position'] ?: $position;

        if (!is_null($textFormat)) {
            [$x, $y] = $this->getPosition($textFormat, 'bottom-' . $position, $size);

            $this->dompdf->getCanvas()->page_text($x, $y, $textFormat, $this->getFont(), $size);
        }

        return $this;
    }

    /**
     * Output the PDF as a string.
     *
     * The option parameter controls the output. Accepted options are:
     *
     * 'compress' = > 1 or 0 - apply content stream compression, this is
     *    on (1) by default
     *
     * @param array<string, int> $options
     *
     * @return string The rendered PDF as string
     *
     * @throws \Exception
     */
    public function output(array $options = []): string
    {
        if (!$this->rendered) {
            $this->render();
        }

        return (string)$this->dompdf->output($options);
    }

    /**
     * Save the PDF to a file
     *
     * @throws \Exception
     */
    public function save(string $filename, string $disk = null): self
    {
        $disk = $disk ?: $this->config->config['dompdf']['disk'];

        if (!is_null($disk)) {
            Storage::disk($disk)->put($filename, $this->output());
            return $this;
        }

        $this->files->put($filename, $this->output());

        return $this;
    }

    /**
     * Make the PDF downloadable by the user
     *
     * @throws \Exception
     */
    public function download(string $filename = 'document.pdf'): CI_Output
    {
        $output = $this->output();

        $fallback = $this->fallbackName($filename);

        return app('output')
            ->set_status_header()
            ->set_content_type('application/pdf')
            ->set_header('Content-Length: ' . strlen($output))
            ->set_header('Content-Disposition: attachment; filename="' . $fallback . '"')
            ->set_output($output);
    }

    /**
     * Return a response with the PDF to show in the browser
     *
     * @throws \Exception
     */
    public function stream(string $filename = 'document.pdf'): CI_Output
    {
        $output = $this->output();

        $fallback = $this->fallbackName($filename);

        return app('output')
            ->set_status_header()
            ->set_content_type('application/pdf')
            ->set_header('Content-Disposition: inline; filename="' . $fallback . '"')
            ->set_output($output)
            ->_display();
    }

    /**
     * Render the PDF
     *
     * @throws \Exception
     */
    public function render(): void
    {
        $this->dompdf->render();

        if ($this->showWarnings) {
            global $_dompdf_warnings;

            if (!empty($_dompdf_warnings) && count($_dompdf_warnings)) {
                $warnings = '';

                foreach ($_dompdf_warnings as $msg) {
                    $warnings .= $msg . "\n";
                }

                if (!empty($warnings)) {
                    throw new Exception($warnings);
                }
            }
        }

        $this->rendered = true;
    }

    /**
     * @param array<string> $pc
     *
     * @throws \Exception
     */
    public function setEncryption(string $password, string $ownerPassword = '', array $pc = []): void
    {
        $this->render();

        $canvas = $this->dompdf->getCanvas();

        if (!$canvas instanceof CPDF) {
            throw new \RuntimeException('Encryption is only supported when using CPDF');
        }

        $canvas->get_cpdf()->setEncryption($password, $ownerPassword, $pc);
    }

    /**
     * @param string $subject
     *
     * @return string
     */
    protected function convertEntities(string $subject): string
    {
        if (false === $this->config->config['dompdf']['convert_entities'] ?? true) {
            return $subject;
        }

        $entities = [
            '€' => '&euro;',
            '£' => '&pound;',
            '$' => '&dollar;',
        ];

        foreach ($entities as $search => $replace) {
            $subject = str_replace($search, $replace, $subject);
        }

        return $subject;
    }

    /**
     * Make a safe fallback filename
     */
    protected function fallbackName(string $filename): string
    {
        return str_replace('%', '', Str::ascii($filename));
    }

    /**
     * Get dompdf font
     *
     * @return string|null
     */
    protected function getFont(): ?string
    {
        $font = $this->config->config['dompdf']['options']['default_font'];

        return $this->dompdf->getFontMetrics()->getFont($font);
    }

    /**
     * Get the x-axis & y-axis based on position at a config file
     *
     * @param string $textFormat
     * @param string $position
     * @param int $size
     *
     * @return array
     */
    protected function getPosition(string $textFormat, string $position, int $size): array
    {
        $pageNum = $this->dompdf->getCanvas()->get_page_number();
        $pageCount = $this->dompdf->getCanvas()->get_page_count();

        $textFormat = Str::of($textFormat)->replace('{PAGE_NUM}', $pageNum)->replace('{PAGE_COUNT}', $pageCount)->toString();

        $xStartNumber = 23;
        $xEndNumber = 37;

        $yStartNumber = 20;
        $yEndNumber = 25;

        $canvasWidth = $this->dompdf->getCanvas()->get_width();
        $canvasHeight = $this->dompdf->getCanvas()->get_height();

        $fontTextWidth = $this->dompdf->getFontMetrics()->getTextWidth($textFormat, $this->getFont(), $size);

        switch ($position) {
            case 'top-right':
                $x = $canvasWidth - $xEndNumber - $fontTextWidth;
                $y = $yStartNumber;
                break;
            case 'top-center':
                $x = (($canvasWidth - $xStartNumber) / 2) - ($fontTextWidth / 2);
                $y = $yStartNumber;
                break;
            case 'bottom-left':
                $x = $xStartNumber;
                $y = $canvasHeight - $yEndNumber;
                break;
            case 'bottom-right':
                $x = $canvasWidth - $xEndNumber - $fontTextWidth;
                $y = $canvasHeight - $yEndNumber;
                break;
            case 'bottom-center':
                $x = (($canvasWidth - $xStartNumber) / 2) - ($fontTextWidth / 2);
                $y = $canvasHeight - $yEndNumber;
                break;
            default:
                $x = $xStartNumber;
                $y = $yStartNumber;
                break;
        }

        return [$x, $y];
    }

    /**
     * Dynamically handle calls into the dompdf instance.
     *
     * @param string $method
     * @param array<mixed> $parameters
     *
     * @return $this|mixed
     */
    public function __call($method, $parameters)
    {
        if (method_exists($this, $method)) {
            return $this->$method(...$parameters);
        }

        if (method_exists($this->dompdf, $method)) {
            $return = $this->dompdf->$method(...$parameters);

            return $return == $this->dompdf ? $this : $return;
        }

        throw new \UnexpectedValueException("Method [{$method}] does not exist on PDF instance.");
    }
}
