<?php

namespace Laraigniter\DomPDF;

use Elegant\Contracts\Hook\PostControllerConstructor;
use Elegant\Contracts\Hook\PreSystem;

class PdfServiceProvider implements PreSystem, PostControllerConstructor
{

    public function preSystem()
    {
        if (!file_exists(base_path('config/dompdf.php'))) {
            copy(realpath(dirname(__DIR__) . '\config\dompdf.php'), base_path('config/dompdf.php'));
        }
    }

    public function postControllerConstructor(&$params)
    {
        app('load')->config('dompdf', true);

        $this->registerDomPdfWrapper();
    }

    /**
     * Register the DOM PDF instance.
     *
     * @return void
     */
    protected function registerDomPdfWrapper()
    {
        $this->registerDomPdfOptions();

        $options = app('dompdf.options');

        $dompdf = new \Dompdf\Dompdf($options);

        $path = realpath(app('config')->config['dompdf']['public_path'] ?: base_path('public'));

        if ($path === false) {
            throw new \RuntimeException('Cannot resolve public path');
        }

        $dompdf->setBasePath($path);

        app('dompdf', $dompdf);

        $app = app();

        app('dompdf.wrapper', new PDF($app->dompdf, $app->config, $app->files, $app->view));
    }

    protected function registerDomPdfOptions()
    {
        $defines = app('config')->config['dompdf']['defines'] ?? [];

        if ($defines) {
            $options = [];

            /**
             * @var string $key
             * @var mixed $value
             */
            foreach ($defines as $key => $value) {
                $key = strtolower(str_replace('DOMPDF_', '', $key));
                $options[$key] = $value;
            }
        } else {
            $options = app('config')->config['dompdf']['options'];
        }

        app('dompdf.options', $options);
    }
}
