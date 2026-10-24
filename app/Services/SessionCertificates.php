<?php
namespace App\Services;

use Illuminate\Support\Facades\{DB, Storage};

class SessionCertificates
{
    public function query()
    {
        return DB::table('certificates as cert')
            ->join('student_journeys as j','j.id','=','cert.student_journey_id')
            ->join('learner_sessions as s','s.id','=','j.learner_session_id')
            ->join('media_files as m','m.id','=','cert.media_file_id')
            ->where('cert.status','ready')->where('j.status','completed')
            ->whereNotNull('j.completed_at')->whereNotNull('s.completed_at')
            ->where('m.status','ready')->whereNull('m.deleted_at')->where('m.mime_type','application/pdf')
            ->whereIn('m.disk',['private','local'])
            ->whereExists(fn($q)=>$q->selectRaw('1')->from('reflections as r')->join('zones as z','z.id','=','r.zone_id')->whereColumn('r.student_journey_id','j.id')->where('z.sequence',5))
            ->whereRaw('(SELECT COUNT(DISTINCT z.sequence) FROM student_progress p JOIN zones z ON z.id=p.zone_id WHERE p.student_journey_id=j.id AND p.status=? AND z.sequence BETWEEN 1 AND 5) = 5',['completed'])
            ->whereRaw('(SELECT COUNT(DISTINCT z.sequence) FROM passport_stamps p JOIN zones z ON z.id=p.zone_id WHERE p.student_journey_id=j.id AND z.sequence BETWEEN 1 AND 5) = 5')
            ->select('cert.*','s.id as session_id','s.display_name','s.class_name','s.school_year','s.completed_at','m.path','m.disk');
    }

    public function available(object $record): bool
    {
        $path=str_replace('\\','/',$record->path);
        return $path!=='' && !str_starts_with($path,'/') && !str_contains($path,':') && !in_array('..',explode('/',$path),true)
            && Storage::disk($record->disk)->exists($path);
    }

    public function forSessions(array $ids)
    {
        return $this->query()->whereIn('s.id',$ids)->get()->filter(fn($r)=>$this->available($r))->keyBy('session_id');
    }

    public function filename(object $record): string
    {
        return 'Sijil_Di-BITaRa_'.preg_replace('/[^a-zA-Z0-9_-]/','-',$record->certificate_number).'.pdf';
    }

    public function preview(object $record): array
    {
        // Read the actual issued PDF text positions, rather than recreating award data from mutable profiles.
        $pdf=Storage::disk($record->disk)->get($record->path);$lines=[];$frame=null;
        if(preg_match('/7 0 obj\s*<<[^>]*\/Length (\d+)[^>]*>>\s*stream\n/s',$pdf,$image,PREG_OFFSET_CAPTURE)){
            $start=$image[0][1]+strlen($image[0][0]);
            $frame='data:image/jpeg;base64,'.base64_encode(substr($pdf,$start,(int)$image[1][0]));
        }
        preg_match_all('/BT \/F1 ([\d.]+) Tf ([\d.]+) ([\d.]+) Td \(((?:\\\\.|[^\\\\)])*)\) Tj ET/',$pdf,$matches,PREG_SET_ORDER);
        foreach($matches as $line){
            $text=preg_replace('/\\\\([\\\\()])/','$1',$line[4]);
            $lines[]=['size'=>(float)$line[1],'x'=>(float)$line[2],'y'=>595-(float)$line[3],'text'=>iconv('Windows-1252','UTF-8',$text)];
        }
        return compact('frame','lines');
    }
}
