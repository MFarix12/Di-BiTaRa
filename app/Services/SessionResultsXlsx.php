<?php
namespace App\Services;

/** Small OOXML writer with explicit text cells (no formula execution or external links). */
class SessionResultsXlsx
{
    private function xml(string $s): string { return htmlspecialchars(preg_replace('/[^\x{9}\x{A}\x{D}\x{20}-\x{D7FF}\x{E000}-\x{FFFD}\x{10000}-\x{10FFFF}]/u','',$s),ENT_XML1|ENT_QUOTES,'UTF-8'); }
    private function column(int $n): string { $s='';do{$s=chr(65+$n%26).$s;$n=intdiv($n,26)-1;}while($n>=0);return $s; }
    public function generate(array $rows,string $date,string $filters): string
    {
        $headers=SessionResults::HEADERS;$widths=array_map(fn($h)=>mb_strwidth($h)+3,$headers);
        foreach($rows as $row)foreach($row as $i=>$v)$widths[$i]=min(42,max($widths[$i],mb_strwidth((string)$v)+2));
        $cells=function(array $values,int $row,int $style) use($widths) {
            $height=$style===1?34:29;
            if($style===2)foreach($values as $i=>$value)$height=max($height,16*(int)ceil(mb_strwidth((string)$value)/max(1,$widths[$i]-2)));
            $s='<row r="'.$row.'" ht="'.$height.'" customHeight="1">';
            foreach($values as $i=>$v){$ref=$this->column($i).$row;
                if($style===2 && in_array($i,[0,8,14]) && ($i!==14 || is_int($v)))$s.='<c r="'.$ref.'" s="2" t="n"><v>'.(int)$v.'</v></c>';
                elseif($style===2 && in_array($i,[12,13]) && $v!=='—'){
                    $d=\Carbon\Carbon::createFromFormat('d/m/Y H:i:s',$v,'UTC');$serial=$d->timestamp/86400+25569;
                    $s.='<c r="'.$ref.'" s="3" t="n"><v>'.$serial.'</v></c>';
                }elseif($style===2 && $i===10 && preg_match('/^(\d+):(\d{2}):(\d{2})$/',$v,$m)){
                    $s.='<c r="'.$ref.'" s="4" t="n"><v>'.(($m[1]*3600+$m[2]*60+$m[3])/86400).'</v></c>';
                }else $s.='<c r="'.$ref.'" s="'.$style.'" t="inlineStr"><is><t xml:space="preserve">'.$this->xml((string)$v).'</t></is></c>';
            }return $s.'</row>';
        };
        $end=count($rows)+4;$sheet='<?xml version="1.0" encoding="UTF-8"?><worksheet xmlns="http://schemas.openxmlformats.org/spreadsheetml/2006/main"><dimension ref="A1:O'.max(4,$end).'"/><sheetViews><sheetView workbookViewId="0"><pane ySplit="4" topLeftCell="A5" activePane="bottomLeft" state="frozen"/></sheetView></sheetViews><cols>';
        foreach($widths as $i=>$width)$sheet.='<col min="'.($i+1).'" max="'.($i+1).'" width="'.$width.'" customWidth="1" bestFit="1"/>';
        $sheet.='</cols><sheetData>'.$cells(['Laporan Keputusan Sesi Di-BITaRa'],1,0).$cells(['Tarikh laporan: '.$date.' (waktu Malaysia)'],2,5).$cells([$filters],3,5).$cells($headers,4,1);
        foreach($rows as $i=>$row)$sheet.=$cells($row,$i+5,2);
        $sheet.='</sheetData><autoFilter ref="A4:O'.max(4,$end).'"/><mergeCells count="3"><mergeCell ref="A1:O1"/><mergeCell ref="A2:O2"/><mergeCell ref="A3:O3"/></mergeCells><pageMargins left="0.3" right="0.3" top="0.5" bottom="0.5" header="0.2" footer="0.2"/><pageSetup orientation="landscape" paperSize="8" fitToWidth="1" fitToHeight="0"/></worksheet>';
        $styles='<?xml version="1.0"?><styleSheet xmlns="http://schemas.openxmlformats.org/spreadsheetml/2006/main"><numFmts count="2"><numFmt numFmtId="164" formatCode="dd/mm/yyyy hh:mm:ss"/><numFmt numFmtId="165" formatCode="[h]:mm:ss"/></numFmts><fonts count="3"><font><sz val="11"/><name val="Calibri"/><color rgb="FF172F4E"/></font><font><b/><sz val="11"/><name val="Calibri"/><color rgb="FFFFFFFF"/></font><font><b/><sz val="16"/><name val="Calibri"/><color rgb="FF123963"/></font></fonts><fills count="3"><fill><patternFill patternType="none"/></fill><fill><patternFill patternType="gray125"/></fill><fill><patternFill patternType="solid"><fgColor rgb="FF123963"/></patternFill></fill></fills><borders count="2"><border/><border><bottom style="thin"><color rgb="FFD5E0EC"/></bottom></border></borders><cellStyleXfs count="1"><xf numFmtId="0" fontId="0" fillId="0" borderId="0"/></cellStyleXfs><cellXfs count="6"><xf fontId="2" fillId="0" borderId="0" numFmtId="0" xfId="0"/><xf fontId="1" fillId="2" borderId="0" numFmtId="0" xfId="0" applyAlignment="1"><alignment wrapText="1" vertical="center"/></xf><xf fontId="0" fillId="0" borderId="1" numFmtId="0" xfId="0" applyAlignment="1"><alignment wrapText="1" vertical="center"/></xf><xf fontId="0" fillId="0" borderId="1" numFmtId="164" xfId="0" applyNumberFormat="1"/><xf fontId="0" fillId="0" borderId="1" numFmtId="165" xfId="0" applyNumberFormat="1"/><xf fontId="0" fillId="0" borderId="0" numFmtId="0" xfId="0"/></cellXfs><cellStyles count="1"><cellStyle name="Normal" xfId="0" builtinId="0"/></cellStyles></styleSheet>';
        return $this->zip([
            '[Content_Types].xml'=>'<?xml version="1.0"?><Types xmlns="http://schemas.openxmlformats.org/package/2006/content-types"><Default Extension="rels" ContentType="application/vnd.openxmlformats-package.relationships+xml"/><Default Extension="xml" ContentType="application/xml"/><Override PartName="/xl/workbook.xml" ContentType="application/vnd.openxmlformats-officedocument.spreadsheetml.sheet.main+xml"/><Override PartName="/xl/worksheets/sheet1.xml" ContentType="application/vnd.openxmlformats-officedocument.spreadsheetml.worksheet+xml"/><Override PartName="/xl/styles.xml" ContentType="application/vnd.openxmlformats-officedocument.spreadsheetml.styles+xml"/></Types>',
            '_rels/.rels'=>'<?xml version="1.0"?><Relationships xmlns="http://schemas.openxmlformats.org/package/2006/relationships"><Relationship Id="rId1" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/officeDocument" Target="xl/workbook.xml"/></Relationships>',
            'xl/workbook.xml'=>'<?xml version="1.0"?><workbook xmlns="http://schemas.openxmlformats.org/spreadsheetml/2006/main" xmlns:r="http://schemas.openxmlformats.org/officeDocument/2006/relationships"><sheets><sheet name="Keputusan Sesi" sheetId="1" r:id="rId1"/></sheets></workbook>',
            'xl/_rels/workbook.xml.rels'=>'<?xml version="1.0"?><Relationships xmlns="http://schemas.openxmlformats.org/package/2006/relationships"><Relationship Id="rId1" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/worksheet" Target="worksheets/sheet1.xml"/><Relationship Id="rId2" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/styles" Target="styles.xml"/></Relationships>',
            'xl/styles.xml'=>$styles,'xl/worksheets/sheet1.xml'=>$sheet,
        ]);
    }
    private function zip(array $files): string
    {
        $body='';$directory='';$offset=0;
        foreach($files as $name=>$data){
            $crc=crc32($data);$compressed=gzdeflate($data,6);$size=strlen($data);$length=strlen($compressed);
            $record=pack('VvvvvvVVVvv',0x04034b50,20,0,8,0,33,$crc,$length,$size,strlen($name),0).$name.$compressed;
            $directory.=pack('VvvvvvvVVVvvvvvVV',0x02014b50,20,20,0,8,0,33,$crc,$length,$size,strlen($name),0,0,0,0,0,$offset).$name;
            $body.=$record;$offset+=strlen($record);
        }
        return $body.$directory.pack('VvvvvVVv',0x06054b50,0,0,count($files),count($files),strlen($directory),strlen($body),0);
    }
}
