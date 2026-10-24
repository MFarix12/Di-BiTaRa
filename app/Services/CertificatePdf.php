<?php

namespace App\Services;

/** Printable A4 landscape certificate using the supplied illustration. */
class CertificatePdf
{
    public function generate(string $name, string $number, string $date, array $details = []): string
    {
        $frame=file_get_contents(resource_path('branding/certificate-frame.jpg'));
        [$width,$height]=getimagesize(resource_path('branding/certificate-frame.jpg'));
        $logo=file_get_contents(resource_path('branding/certificate-logo.jpg'));
        $metrics=json_decode(file_get_contents(resource_path('branding/helvetica-bold-widths.json')),true);
        $stream="% /DiBITaRaTextV1\nq 842 0 0 595 0 0 cm /DiBiTaRaFrameV3 Do Q\n";
        $lines=[['SIJIL PENCAPAIAN',28,390],['Bijak Imbuhan & Tatabahasa Ruang Angkasa',10,365],['Dengan sukacitanya dianugerahkan kepada',12,336],[$name,28,297],['atas kejayaan melengkapkan lima zon pembelajaran',13,261],['dan aktiviti Imlak dalam pengembaraan Di-BITaRa.',13,240],['Tahniah, juruterbang bahasa!',12,212],['Tarikh: '.$date.'   |   No. Sijil: '.$number,10,183],['Disahkan oleh Sistem Di-BITaRa',9,164]];
        if($details){
            $stream.="% /DiBITaRaSessionV3\n";
            $lines=[['SIJIL PENCAPAIAN',27,390],['Bijak Imbuhan & Tatabahasa Ruang Angkasa',10,365],['Dianugerahkan kepada',12,340],[$name,26,309],['Tahun '.($details['year']??4).'  |  Kelas: '.($details['class']?:'—'),12,280],['atas kejayaan menyelesaikan keseluruhan',12,252],['pengembaraan pembelajaran Di-BITaRa.',12,232],['Tarikh: '.$date.'   |   No. Sijil: '.$number,10,204],['Disahkan oleh Sistem Di-BITaRa',9,180],['Tahniah, juruterbang bahasa!',10,161]];
        }
        foreach($lines as [$text,$size,$y]){
            $text=iconv('UTF-8','Windows-1252//TRANSLIT',$text);
            $units=array_sum(array_map(fn($char)=>$metrics[(string)ord($char)]??556,str_split($text)));
            $size=min($size,490/max(1,$units)*1000);$x=(842-$units/1000*$size)/2;
            $text=str_replace(['\\','(',')',"\r","\n"],['\\\\','\\(','\\)',' ',' '],$text);
            $stream.="0.025 0.12 0.31 rg BT /F1 $size Tf $x $y Td ($text) Tj ET\n";
        }
        $objects=[
            '<< /Type /Catalog /Pages 2 0 R >>',
            '<< /Type /Pages /Kids [3 0 R] /Count 1 >>',
            '<< /Type /Page /Parent 2 0 R /MediaBox [0 0 842 595] /Resources << /Font << /F1 4 0 R >> /XObject << /DiBiTaRaLogo 6 0 R /DiBiTaRaFrameV3 7 0 R >> >> /Contents 5 0 R >>',
            '<< /Type /Font /Subtype /Type1 /BaseFont /Helvetica-Bold /Encoding /WinAnsiEncoding >>',
            '<< /Length '.strlen($stream)." >>\nstream\n".$stream.'endstream',
            '<< /Type /XObject /Subtype /Image /Width 350 /Height 350 /ColorSpace /DeviceRGB /BitsPerComponent 8 /Filter /DCTDecode /Length '.strlen($logo)." >>\nstream\n".$logo."\nendstream",
            "<< /Type /XObject /Subtype /Image /Width $width /Height $height /ColorSpace /DeviceRGB /BitsPerComponent 8 /Filter /DCTDecode /Length ".strlen($frame)." >>\nstream\n".$frame."\nendstream",
        ];
        $pdf="%PDF-1.4\n";$offsets=[0];
        foreach($objects as $i=>$object){$offsets[]=strlen($pdf);$pdf.=($i+1)." 0 obj\n$object\nendobj\n";}
        $start=strlen($pdf);$count=count($objects)+1;$pdf.="xref\n0 $count\n0000000000 65535 f \n";
        foreach(array_slice($offsets,1) as $offset)$pdf.=sprintf('%010d 00000 n ',$offset)."\n";
        return $pdf."trailer\n<< /Size $count /Root 1 0 R >>\nstartxref\n$start\n%%EOF";
    }
}
