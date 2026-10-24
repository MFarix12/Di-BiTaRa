<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class StudentCertificateController extends Controller
{
    public function retry(Request $request,int $certificate,\App\Services\LearningEngine $engine) {
        DB::transaction(function()use($request,$certificate,$engine){
            $record=DB::table('certificates')->join('student_journeys','student_journeys.id','=','certificates.student_journey_id')->where('certificates.id',$certificate)->where('student_journeys.'.\App\Services\LearnerIdentity::key($request->user()),$request->user()->id)->select('certificates.*')->first();abort_unless($record,404);
            $journey=DB::table('student_journeys')->where('id',$record->student_journey_id)->lockForUpdate()->first();$engine->refresh($journey);
        });
        return back()->with('status','Status sijil telah disemak. Sila lihat bahagian Sijil Saya.');
    }
    public function show(Request $request, int $certificate)
    {
        $record = DB::table('certificates')->join('student_journeys','student_journeys.id','=','certificates.student_journey_id')
            ->where('certificates.id',$certificate)->where('student_journeys.'.\App\Services\LearnerIdentity::key($request->user()),$request->user()->id)
            ->where('certificates.status','ready')->select('certificates.*')->first();
        abort_unless($record && $record->media_file_id,404);
        $media = DB::table('media_files')->where('id',$record->media_file_id)->whereNull('deleted_at')->where('status','ready')->where('mime_type','application/pdf')->first();
        abort_unless($media && in_array($media->disk,['private','local'],true),404);
        $path = str_replace('\\','/',$media->path);
        abort_if(str_starts_with($path,'/') || str_contains($path,':') || in_array('..',explode('/',$path),true),404);
        $disk = Storage::disk($media->disk);
        abort_unless($disk->exists($path),404);
        $filename = 'sijil-'.preg_replace('/[^a-zA-Z0-9_-]/','-',$record->certificate_number).'.pdf';
        $disposition = $request->routeIs('student.certificate.download') ? 'attachment' : 'inline';
        return response()->stream(function () use ($disk,$path) {
            $stream = $disk->readStream($path);
            if (is_resource($stream)) { fpassthru($stream); fclose($stream); }
        },200,['Content-Type'=>'application/pdf','Content-Disposition'=>$disposition.'; filename="'.$filename.'"','Cache-Control'=>'private, no-store','X-Content-Type-Options'=>'nosniff']);
    }
}
