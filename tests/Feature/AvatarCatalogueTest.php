<?php

namespace Tests\Feature;

use App\Livewire\DeparturePreparation;
use App\Models\User;
use App\Services\AvatarCatalogue;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Livewire\Livewire;
use Tests\TestCase;

class AvatarCatalogueTest extends TestCase
{
    use RefreshDatabase;

    public function test_installation_is_repeatable_and_each_new_role_has_its_own_image(): void
    {
        $this->seed();
        $admin = User::factory()->create(['role'=>'admin']);
        $catalogue = app(AvatarCatalogue::class);
        $catalogue->install($admin->id);
        $before = DB::table('avatars')->pluck('id','code')->all();
        $captain = DB::table('avatars')->where('code','pilot-1')->first();
        $otherMedia = DB::table('avatars')->where('code','pilot-cerdas')->value('media_file_id');
        DB::table('avatars')->where('id',$captain->id)->update(['media_file_id'=>$otherMedia]);
        $catalogue->install($admin->id);
        $this->assertSame($before,DB::table('avatars')->pluck('id','code')->all());
        $this->assertDatabaseCount('avatars',16);
        $this->assertNotEquals($otherMedia,DB::table('avatars')->where('id',$captain->id)->value('media_file_id'));
        $this->assertSame(AvatarCatalogue::image('pilot-1'),AvatarCatalogue::image('unknown-avatar'));
        $this->assertStringNotContainsString('pilot-avatars.png',$this->blade('<x-pilot-avatar code="pilot-1"/>')->__toString());
        $hashes = [];
        foreach (config('avatar-catalog') as $code=>$entry) {
            $this->assertDatabaseHas('avatars',['code'=>$code,'gender'=>$entry['gender'],'role_label'=>$entry['role']]);
            $file = public_path(AvatarCatalogue::path($code));
            $this->assertFileExists($file);
            $hashes[] = hash_file('sha256',$file);
            $this->assertSame(IMAGETYPE_PNG,getimagesize($file)[2]);
        }
        $this->assertCount(16,array_unique($hashes));
    }

}
