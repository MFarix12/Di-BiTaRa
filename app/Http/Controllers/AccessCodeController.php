<?php

namespace App\Http\Controllers;

use App\Services\Audit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class AccessCodeController extends Controller
{
    public function index()
    {
        $codes=DB::table('access_codes')->select('access_codes.*')->selectSub(DB::table('learner_sessions')->selectRaw('COUNT(*)')->whereColumn('access_code_id','access_codes.id'),'session_count')->latest('id')->paginate(20);
        return view('admin.access-codes',compact('codes'));
    }

    public function save(Request $r, ?int $code=null)
    {
        $data=$r->validate(['name'=>'required|string|max:100','status'=>'required|in:active,inactive','expires_at'=>'nullable|date','school_year'=>($code?'sometimes':'required').'|integer|in:4,5,6']);
        $data['expires_at']=($data['expires_at'] ?? null) ? \Carbon\Carbon::parse($data['expires_at'],'Asia/Kuala_Lumpur')->utc()->format('Y-m-d H:i:s') : null;
        DB::transaction(function () use ($r,$code,$data) {
            if ($code) {
                $existing=DB::table('access_codes')->where('id',$code)->lockForUpdate()->first();
                abort_unless($existing,404);
                if(isset($data['school_year']) && (int)$data['school_year']!==(int)$existing->school_year) throw \Illuminate\Validation\ValidationException::withMessages(['school_year'=>'Tahun kod tidak boleh ditukar. Cipta kod baharu untuk tahun lain.']);
                abort_unless(DB::table('access_codes')->where('id',$code)->exists(),404);DB::table('access_codes')->where('id',$code)->update($data+['updated_at'=>now()]); }
            else {
                $release=DB::table('learning_releases')->where('school_year',$data['school_year'])->where('status','published')->latest('id')->first();
                if(!$release) throw \Illuminate\Validation\ValidationException::withMessages(['school_year'=>'Set soalan tahun ini belum diterbitkan.']);
                $data['learning_release_id']=$release->id;
                do { $generated='DBR-T'.$data['school_year'].'-'.Str::upper(Str::random(6)); } while(DB::table('access_codes')->where('code',$generated)->exists());
                $code=DB::table('access_codes')->insertGetId($data+['code'=>$generated,'created_by'=>$r->user()->id,'created_at'=>now(),'updated_at'=>now()]);
            }
            Audit::record($r->isMethod('put')?'update':'create','access_codes',$code,$data);
        });
        return back()->with('status','Kod akses telah disimpan.');
    }

    public function results(Request $r, \App\Services\SessionResults $report)
    {
        $filters=$report->filters($r);$at=now();
        $sessions=$report->query($filters)->paginate(20)->withQueryString();
        $rows=$report->rows($sessions->items(),$at,($sessions->currentPage()-1)*$sessions->perPage());
        $codes=DB::table('access_codes')->orderBy('code')->get(['id','code','name']);
        $certificates=app(\App\Services\SessionCertificates::class)->forSessions(collect($sessions->items())->pluck('id')->all());
        return view('admin.session-results',compact('sessions','rows','filters','codes','certificates'));
    }

    public function export(Request $r, string $format, \App\Services\SessionResults $report)
    {
        abort_unless(in_array($format,['xlsx','pdf']),404);
        $filters=$report->filters($r);$at=now();$rows=[];
        // Each batch shares the export timestamp; export is never limited to the current page.
        $report->query($filters)->chunk(200,function($sessions)use($report,$at,&$rows){
            array_push($rows,...$report->rows($sessions,$at,count($rows)));
        });
        $date=$at->copy()->timezone('Asia/Kuala_Lumpur')->format('d/m/Y H:i:s');
        $summary=$report->summary($filters);
        $bytes=$format==='xlsx' ? app(\App\Services\SessionResultsXlsx::class)->generate($rows,$date,$summary) : app(\App\Services\SessionResultsPdf::class)->generate($rows,$date,$summary);
        $filename='Keputusan_Sesi_Di-BITaRa_'.$at->copy()->timezone('Asia/Kuala_Lumpur')->format('Y-m-d').'.'.$format;
        return response($bytes,200,[
            'Content-Type'=>$format==='xlsx'?'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet':'application/pdf',
            'Content-Disposition'=>'attachment; filename="'.$filename.'"',
            'Cache-Control'=>'private, no-store, max-age=0','X-Content-Type-Options'=>'nosniff',
        ]);
    }
}
