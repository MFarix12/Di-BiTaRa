<?php

namespace Tests\Feature;

use App\Livewire\ContentManager;
use App\Livewire\StudentProfile;
use App\Livewire\Tutorial;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Livewire\Livewire;
use Tests\TestCase;

class FoundationTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed();
        $this->artisan('dibitara:admin', ['username' => 'pengurus', '--generate' => true])->assertSuccessful();
        $this->admin = User::where('username','pengurus')->firstOrFail();
    }

    private function student(): User
    {
        $user = User::factory()->create();
        DB::table('student_profiles')->insert(['user_id' => $user->id, 'avatar_id' => DB::table('avatars')->value('id')]);
        return $user;
    }

    public function test_public_pages_and_36_table_schema_are_available(): void
    {
        foreach (['/','/masukkan-kod','/log-masuk-admin','/bantuan'] as $url) {
            $this->get($url)->assertOk();
        }
        $this->assertCount(36, array_filter(\Illuminate\Support\Facades\Schema::getTableListing(), fn ($name) => !in_array(last(explode('.', $name)),['migrations','sqlite_sequence'])));
        $this->assertDatabaseCount('zones',5);
        $this->assertDatabaseCount('avatars',15);
    }

    public function test_admin_can_crud_drafts_with_audit_and_safe_preview(): void
    {
        $manager = Livewire::actingAs($this->admin,'web')->test(ContentManager::class)->call('create')
            ->set('title','Mengenali di-')->set('body','<script>alert(1)</script> Imbuhan ditulis rapat.')
            ->set('zone_id',DB::table('zones')->value('id'))->call('save')->assertHasNoErrors();
        $note = DB::table('learning_contents')->first();
        $this->assertSame('draft',$note->status);
        $this->assertStringNotContainsString('<script>',$note->body);
        $this->assertDatabaseHas('audit_logs',['action'=>'create','entity_id'=>$note->id,'entity_type'=>'learning_contents']);
        $manager->call('edit',$note->id)->set('title','Nota Dikemas Kini')->call('save')->assertHasNoErrors();
        $this->assertDatabaseHas('learning_contents',['id'=>$note->id,'revision'=>2]);
        $manager->call('delete',$note->id);
        $this->assertNotNull(DB::table('learning_contents')->find($note->id)->deleted_at);
        $this->assertDatabaseCount('audit_logs',4);
        $this->assertDatabaseCount('learning_releases',0);
    }

    public function test_concurrent_content_edit_is_rejected_instead_of_overwriting(): void
    {
        $first = Livewire::actingAs($this->admin,'web')->test(ContentManager::class)->call('create')->set('title','Asal')->set('body','Teks nota')->set('zone_id',DB::table('zones')->value('id'))->call('save');
        $id = DB::table('learning_contents')->value('id');
        $first->call('edit',$id);
        Livewire::actingAs($this->admin,'web')->test(ContentManager::class)->call('edit',$id)->set('title','Versi Terkini')->call('save');
        $first->set('title','Versi Lapuk')->call('save')->assertHasErrors('title');
        $this->assertDatabaseHas('learning_contents',['id'=>$id,'title'=>'Versi Terkini']);
    }

}
