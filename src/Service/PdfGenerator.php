<?php
namespace App\Service;

use Dompdf\Dompdf;
use Dompdf\Options;
use Twig\Environment;

class PdfGenerator
{
    private Dompdf $dompdf;
    private Environment $twig;

    public function __construct(Environment $twig)
    {
        $this->twig = $twig;
        $options = new Options();
        $options->set('defaultFont', 'Arial');
        $this->dompdf = new Dompdf($options);
    }

    public function generatePdf(string $template, array $data = []): string
    {
        $html = $this->twig->render($template, $data);
        $this->dompdf->loadHtml($html);
        $this->dompdf->render();

        return $this->dompdf->output();
    }
}
