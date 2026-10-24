<?php
namespace App\Http\Controllers;

use App\Services\{SessionCertificates,SessionResults};
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class AdminCertificateController extends Controller
{
    private function record(int $certificate,SessionCertificates $files): object
    {
        $record=$files->query()->where('cert.id',$certificate)->first();
        abort_unless($record && $files->available($record),404,'Sijil belum tersedia untuk sesi ini.');
        return $record;
    }

    public function preview(int $certificate,SessionCertificates $files)
    {
        $record=$this->record($certificate,$files);
        return view('admin.certificate-preview',['certificate'=>$record,'preview'=>$files->preview($record)]);
    }

    public function pdf(Request $request,int $certificate,SessionCertificates $files)
    {
        $record=$this->record($certificate,$files);$disk=Storage::disk($record->disk);
        return response()->stream(function()use($disk,$record){
            $stream=$disk->readStream($record->path);
            if(is_resource($stream)){fpassthru($stream);fclose($stream);}
        },200,['Content-Type'=>'application/pdf','Content-Disposition'=>($request->routeIs('admin.certificates.download')?'attachment':'inline').'; filename="'.$files->filename($record).'"','Cache-Control'=>'private, no-store','X-Content-Type-Options'=>'nosniff']);
    }

    public function bundle(Request $request,SessionCertificates $files,SessionResults $report)
    {
        $input=$request->validate(['scope'=>'required|in:filtered,selected','sessions'=>'required_if:scope,selected|array|min:1','sessions.*'=>'integer|distinct|min:1']);
        $filters=$report->filters($request);
        $ids=$report->query($filters)->pluck('s.id')->all();
        if($input['scope']==='selected'){
            abort_unless(!array_diff($input['sessions'],$ids),422,'Pilihan tidak sepadan dengan tapisan semasa.');
            $ids=$input['sessions'];
        }
        $records=$files->forSessions($ids)->values();
        if($input['scope']==='selected' && $records->count()!==count($ids)){
            return back()->withErrors(['certificates'=>'Ada sesi pilihan yang belum layak atau fail sijil belum tersedia. Sila semak semula.']);
        }
        if($records->isEmpty())return back()->withErrors(['certificates'=>'Belum ada sijil yang layak dimuat turun untuk tapisan ini.']);
        $filename='Sijil_Pelajar_Di-BITaRa_'.now()->timezone('Asia/Kuala_Lumpur')->format('Y-m-d').'.zip';
        // ZIP is streamed one PDF at a time; no ZipArchive extension or public temporary files required.
        return response()->streamDownload(function()use($records,$files){
            $directory='';$offset=0;
            foreach($records as $record){
                $name=$files->filename($record);$data=Storage::disk($record->disk)->get($record->path);
                $compressed=gzdeflate($data,6);$crc=crc32($data);$size=strlen($data);$length=strlen($compressed);
                $entry=pack('VvvvvvVVVvv',0x04034b50,20,0,8,0,33,$crc,$length,$size,strlen($name),0).$name.$compressed;
                $directory.=pack('VvvvvvvVVVvvvvvVV',0x02014b50,20,20,0,8,0,33,$crc,$length,$size,strlen($name),0,0,0,0,0,$offset).$name;
                echo $entry;$offset+=strlen($entry);
            }
            echo $directory.pack('VvvvvVVv',0x06054b50,0,0,$records->count(),$records->count(),strlen($directory),$offset,0);
        },$filename,['Content-Type'=>'application/zip','Cache-Control'=>'private, no-store','X-Content-Type-Options'=>'nosniff']);
    }
}
