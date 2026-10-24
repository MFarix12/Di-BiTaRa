<?php
namespace App\Services;

use Dompdf\Dompdf;
use Dompdf\Options;

class SessionResultsPdf
{
    public function generate(array $rows,string $date,string $filters): string
    {
        $options=new Options();$options->set('isRemoteEnabled',false);$options->set('isPhpEnabled',false);$options->set('defaultFont','DejaVu Sans');
        $pdf=new Dompdf($options);$pdf->setPaper('a3','landscape');
        $logo='data:image/png;base64,'.base64_encode(file_get_contents(public_path('images/official-logo.png')));
        $pdf->loadHtml(view('admin.exports.session-results',compact('rows','date','filters','logo'))->render(),'UTF-8');
        $pdf->render();$font=$pdf->getFontMetrics()->getFont('DejaVu Sans','normal');
        $canvas=$pdf->getCanvas();$canvas->page_text(28,$canvas->get_height()-24,'Di-BITaRa · Waktu Malaysia (UTC+08:00)',$font,8,[.3,.38,.48]);
        $canvas->page_text($canvas->get_width()-145,$canvas->get_height()-24,'Halaman {PAGE_NUM} / {PAGE_COUNT}',$font,8,[.3,.38,.48]);
        return $pdf->output();
    }
}
