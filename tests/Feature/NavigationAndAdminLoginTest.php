<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class NavigationAndAdminLoginTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_account_actions_are_in_toolbar_with_logout_confirmation(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $response = $this->actingAs($admin,'web')->get('/admin')->assertOk();
        $document = new \DOMDocument;
        $previous = libxml_use_internal_errors(true);
        $document->loadHTML('<?xml encoding="utf-8" ?>'.$response->getContent());
        libxml_clear_errors();
        libxml_use_internal_errors($previous);
        $xpath = new \DOMXPath($document);
        $links = $xpath->query('//nav[@id="sidebar-menu"]/a');
        $this->assertCount(12, $links);
        foreach ($links as $link) {
            $this->assertNotContains(trim($link->getAttribute('title')), ['Profil', 'Tetapan', 'Log Keluar']);
        }
        $this->assertCount(0, $xpath->query('//nav[@id="sidebar-menu"]//form'));
        $account = $xpath->query('//header//nav[@id="admin-account-dropdown"]/a');
        $this->assertCount(2, $account);
        $this->assertSame(route('admin.profile'), $account->item(0)->getAttribute('href'));
        $this->assertSame(route('admin.settings'), $account->item(1)->getAttribute('href'));
        $this->assertCount(1, $xpath->query('//dialog[@aria-labelledby="admin-logout-title"]//form[@method="post"][@action="'.route('logout').'"]'));
        $this->assertCount(1, $xpath->query('//dialog//input[@name="_token"]'));
        $response->assertSee('Adakah anda pasti mahu log keluar?')->assertSee('Batal');
        $this->post(route('logout'))->assertRedirect('/');
        $this->assertGuest();
    }

    public function test_public_navigation_has_six_separate_destinations_and_one_active_page(): void
    {
        foreach (['/'=>'Utama','/tentang'=>'Tentang','/capai-pembelajaran'=>'Capai Pembelajaran','/cara-bermain'=>'Cara Bermain','/masukkan-kod'=>'Masukkan Kod','/log-masuk-admin'=>'Log Masuk Pentadbir'] as $url=>$label) {
            $response = $this->get($url)->assertOk();
            $document = new \DOMDocument;
            $previous = libxml_use_internal_errors(true);
            $document->loadHTML('<?xml encoding="utf-8" ?>'.$response->getContent());
            libxml_clear_errors();
            libxml_use_internal_errors($previous);
            $xpath = new \DOMXPath($document);
            $links = $xpath->query('//nav[@id="navigation"]/a');
            $this->assertCount(6,$links);
            $destinations = [];
            foreach ($links as $link) {
                $href = $link->getAttribute('href');
                $this->assertStringNotContainsString('#',$href);
                $destinations[] = $href;
            }
            $this->assertCount(6,array_unique($destinations));
            $active = $xpath->query('//nav[@id="navigation"]/a[@aria-current="page"]');
            $this->assertCount(1,$active);
            $this->assertSame($label,trim($active->item(0)->textContent));
        }
        $this->get('/')->assertSee('Lima Zon Pengembaraan')->assertSee('Pasport Digital')->assertSee('Mula Pengembaraan');
        $this->get('/tentang')->assertSee('OBJEKTIF PEMBELAJARAN')->assertSee('PEMBELAJARAN BERASASKAN GAMIFIKASI');
        $this->get('/capai-pembelajaran')->assertSee('Simulasi Penerbangan')->assertSee('Balai Ketibaan &amp; Imlak',false);
        $this->get('/cara-bermain')->assertSee('Bilakah jam bermula?')->assertSee('Tiada kamera diperlukan')->assertSee('Isi nama dan kelas');
    }

    public function test_admin_profile_update_keeps_role_and_audits_without_password_data(): void
    {
        $admin = User::factory()->create(['role'=>'admin']);
        \Livewire\Livewire::actingAs($admin,'web')->test(\App\Livewire\AdminProfile::class)
            ->set('display_name','Kapten Admin')->call('save')->assertHasNoErrors();
        $this->assertSame('Kapten Admin',$admin->fresh()->display_name);
        $this->assertSame('admin',$admin->fresh()->role);
        $this->assertDatabaseHas('audit_logs',['admin_user_id'=>$admin->id,'action'=>'profile_update']);
        \Livewire\Livewire::actingAs($admin,'web')->test(\App\Livewire\AdminSettings::class)
            ->set('current_password','wrong')->set('password','Baharu12345')->set('password_confirmation','Baharu12345')
            ->call('changePassword')->assertHasErrors('current_password');
        \Livewire\Livewire::actingAs($admin,'web')->test(\App\Livewire\AdminSettings::class)
            ->set('current_password','password')->set('password','Baharu12345')->set('password_confirmation','Baharu12345')
            ->call('changePassword')->assertHasNoErrors();
        $this->assertTrue(\Illuminate\Support\Facades\Hash::check('Baharu12345',$admin->fresh()->password));
        $this->assertDatabaseHas('audit_logs',['action'=>'password_update','after_data'=>null]);
    }

    public function test_global_navigation_is_outside_all_windows_on_home_and_login_pages(): void
    {
        foreach (['/', '/masukkan-kod', '/log-masuk-admin'] as $url) {
            $response = $this->get($url)->assertOk()->assertSee('Log Masuk Pentadbir');
            $document = new \DOMDocument;
            $previous = libxml_use_internal_errors(true);
            $document->loadHTML($response->getContent());
            libxml_clear_errors();
            libxml_use_internal_errors($previous);
            $xpath = new \DOMXPath($document);
            $this->assertSame(1, $xpath->query('//body/header[contains(@class,"global-nav")]')->length);
            $this->assertSame(0, $xpath->query('//div[contains(@class,"app-window")]//nav')->length);
            $this->assertSame(1, $xpath->query('//nav[@id="navigation"]')->length);
            if ($url === '/') {
                $this->assertSame(0, $xpath->query('//*[contains(@class,"app-window")]')->length);
                $this->assertSame(0, $xpath->query('//*[contains(@class,"window-titlebar")]')->length);
                $this->assertSame(0, $xpath->query('//*[contains(@class,"window-controls")]')->length);
            }
        }
    }

    public function test_admin_portal_rejects_student_even_when_role_is_forged(): void
    {
        $student = User::factory()->create();
        $this->post('/log-masuk-admin', ['identity' => $student->username, 'password' => 'password', 'role' => 'admin'])
            ->assertSessionHasErrors('identity');
        $this->assertGuest();
    }

    public function test_only_active_admin_can_sign_in_through_admin_portal(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $this->post('/log-masuk-admin', ['identity' => $admin->username, 'password' => 'password'])
            ->assertRedirect('/admin');
        $this->assertAuthenticatedAs($admin);
        $this->post('/log-keluar');
        $admin->forceFill(['status' => 'inactive'])->save();
        $this->post('/log-masuk-admin', ['identity' => $admin->username, 'password' => 'password'])
            ->assertSessionHasErrors('identity');
        $this->assertGuest();
    }

}
