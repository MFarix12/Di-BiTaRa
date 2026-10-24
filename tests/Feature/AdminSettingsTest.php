<?php
namespace Tests\Feature;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;
class AdminSettingsTest extends TestCase {
 use RefreshDatabase;
 public function test_profile_and_settings_are_separate_and_preferences_persist(): void {
  $admin=User::factory()->create(['role'=>'admin']);$this->actingAs($admin,'web');
  $this->get('/admin/profil')->assertOk()->assertSee('Kemas Kini Profil')->assertDontSee('Kata Laluan Semasa')->assertDontSee('Mod Paparan');
  $this->get('/admin/tetapan')->assertOk()->assertSee('Tetapan Paparan')->assertSee('Kata Laluan Semasa')->assertDontSee('wire:model="display_name"',false);
  Livewire::test(\App\Livewire\AdminSettings::class)->set('theme','dark')->set('text_size','large')->set('animations',false)->call('save')->assertHasNoErrors()->assertRedirect(route('admin.settings'));
  $this->assertSame(['theme'=>'dark','text_size'=>'large','animations'=>false],$admin->fresh()->admin_preferences);
  $this->get('/admin/tetapan')->assertOk()->assertSee('data-admin-theme="dark"',false)->assertSee('data-admin-motion="off"',false)->assertSee('data-admin-text="large"',false);
  Livewire::test(\App\Livewire\AdminSettings::class)->assertSet('theme','dark')->assertSet('text_size','large')->assertSet('animations',false)->set('theme','invalid')->call('save')->assertHasErrors('theme');
  $this->assertDatabaseHas('audit_logs',['action'=>'settings_update','entity_id'=>$admin->id]);
 }
 public function test_preferences_are_per_account_and_settings_require_admin(): void {
  $a=User::factory()->create(['role'=>'admin']);$b=User::factory()->create(['role'=>'admin']);
  $this->get('/admin/tetapan')->assertRedirect('/log-masuk-admin');
  $this->actingAs($a,'web');Livewire::test(\App\Livewire\AdminSettings::class)->set('theme','dark')->call('save');
  $this->assertNull($b->fresh()->admin_preferences);
  $this->actingAs($b,'web');Livewire::test(\App\Livewire\AdminSettings::class)->assertSet('theme','light');
  \Illuminate\Support\Facades\Auth::guard('web')->logout();\Illuminate\Support\Facades\Auth::forgetGuards();$student=$this->makeLearner();$this->actingAs($student,'learner')->get('/admin/tetapan')->assertRedirect('/log-masuk-admin');
 }
}
