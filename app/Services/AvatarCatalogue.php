<?php

namespace App\Services;

use Illuminate\Support\Facades\DB;

class AvatarCatalogue
{
    public static function path(string $code): string
    {
        $code = config('avatar-catalog.'.$code) ? $code : 'pilot-1';
        return 'images/avatars/'.(config('avatar-catalog.'.$code.'.image') ?? $code.'.png');
    }

    public static function image(string $code): string
    {
        return asset(self::path($code));
    }

    public static function details(object $avatar): array
    {
        return ['name'=>$avatar->role_label ?? 'Kapten', 'image'=>self::image($avatar->code),
            'index'=>max(0,min(3,(int) str_replace('pilot-','',$avatar->code)-1)),
            'gender'=>$avatar->gender ?? (in_array($avatar->code,['pilot-3','pilot-4']) ? 'female' : 'male'),
            'role'=>$avatar->role_label ?? 'Kapten'];
    }

    public function install(int $admin): void
    {
        DB::transaction(function () use ($admin) {
            foreach (config('avatar-catalog') as $code=>$entry) {
                $path = self::path($code);
                $file = public_path($path);
                if (! is_file($file)) { throw new \RuntimeException('Fail avatar belum tersedia: '.$path); }
                $media = DB::table('media_files')->where('disk','brand')->where('path',$path)->first();
                $mediaId = $media?->id ?? DB::table('media_files')->insertGetId([
                    'kind'=>'image','title'=>$entry['name'],'disk'=>'brand','path'=>$path,'original_name'=>$code.'.png',
                    'mime_type'=>'image/png','size_bytes'=>filesize($file),'source'=>'generated','status'=>'ready',
                    'created_by'=>$admin,'alt_text'=>$entry['role'],'checksum'=>hash_file('sha256',$file),
                ]);
                if (! DB::table('avatars')->where('code',$code)->exists()) {
                    DB::table('avatars')->insert(['code'=>$code,'name'=>$entry['name'],'gender'=>$entry['gender'],
                        'role_label'=>$entry['role'],'media_file_id'=>$mediaId,'sort_order'=>10+array_search($code,array_keys(config('avatar-catalog')))]);
                } else {
                    // Preserve avatar IDs and every student's existing selection.
                    DB::table('avatars')->where('code',$code)->update(['media_file_id'=>$mediaId]);
                }
            }
        });
    }
}
