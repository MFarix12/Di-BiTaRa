<?php

namespace Tests\Feature;

use App\Models\User;
use App\Services\CertificatePdf;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PublicBrandingTest extends TestCase
{
    use RefreshDatabase;

    public function test_public_footer_has_working_destinations_and_official_logo(): void
    {
        foreach (['/','/tentang','/cara-bermain','/capai-pembelajaran','/masukkan-kod','/log-masuk-admin','/privasi','/syarat-penggunaan','/bantuan'] as $url) {
            $response = $this->get($url)->assertOk()->assertSee('class="public-footer"', false)->assertSee('images/official-logo.png');
            foreach (['home','about','play','adventure','access.enter','admin.login','help','privacy','terms'] as $route) $response->assertSee(route($route), false);
            $response->assertSee('Hak Cipta Terpelihara © 2026 Di-BITaRa');
        }
    }

    public function test_authenticated_pages_never_render_a_footer_but_keep_official_branding(): void
    {
        $this->seed();
        $student = $this->makeLearner();
        $this->actingAs($student,'learner');
        foreach (['/profil-sesi','/kuiz-aktiviti','/pasport-pencapaian','/profil-sesi?tutorial=1','/notifikasi','/bantuan','/privasi'] as $url) {
            $this->get($url)->assertOk()->assertDontSee('<footer', false)->assertSee('images/official-logo.png');
        }
        $this->actingAs(User::factory()->create(['role'=>'admin']),'web');
        foreach (['/admin','/admin/nota','/admin/kuiz','/admin/profil','/admin/audit','/admin/tetapan','/'] as $url) {
            $this->get($url)->assertOk()->assertDontSee('<footer', false)->assertSee('images/official-logo.png');
        }
    }

    public function test_certificate_contains_the_official_logo_as_an_embedded_image(): void
    {
        $pdf = app(CertificatePdf::class)->generate('Murid Bahasa', 'DBR-UJIAN', '21/10/2026');
        $this->assertStringStartsWith('%PDF-1.4', $pdf);
        $this->assertStringContainsString('/DiBiTaRaLogo 6 0 R', $pdf);
        $this->assertStringContainsString('/Subtype /Image', $pdf);
        $this->assertStringContainsString('(Murid Bahasa)', $pdf);
        $this->assertStringContainsString('%%EOF', $pdf);
    }

    public function test_existing_certificate_branding_can_be_refreshed_without_changing_award_data(): void
    {
        \Illuminate\Support\Facades\Storage::fake('private');
        $user = User::factory()->create();
        $release = \Illuminate\Support\Facades\DB::table('learning_releases')->insertGetId(['version'=>'TEST-BRAND','title'=>'Ujian','status'=>'published','manifest'=>'{}','scoring_policy'=>'{}','checksum'=>str_repeat('a',64)]);
        $journey = \Illuminate\Support\Facades\DB::table('student_journeys')->insertGetId(['user_id'=>$user->id,'learning_release_id'=>$release]);
        $disk = \Illuminate\Support\Facades\Storage::disk('private');
        $disk->put('certificates/original.pdf', '%PDF-original');
        $media = \Illuminate\Support\Facades\DB::table('media_files')->insertGetId(['title'=>'Sijil','kind'=>'document','disk'=>'private','path'=>'certificates/original.pdf','mime_type'=>'application/pdf','original_name'=>'original.pdf','size_bytes'=>13,'checksum'=>hash('sha256','%PDF-original'),'status'=>'ready','created_by'=>$user->id]);
        $id = \Illuminate\Support\Facades\DB::table('certificates')->insertGetId(['student_journey_id'=>$journey,'media_file_id'=>$media,'recipient_name'=>'Murid Bahasa','certificate_number'=>'DBR-LAMA','status'=>'ready','issued_at'=>'2026-10-20 02:00:00']);
        $this->artisan('dibitara:refresh-certificate-branding')->assertSuccessful();
        $this->assertDatabaseHas('certificates',['id'=>$id,'recipient_name'=>'Murid Bahasa','certificate_number'=>'DBR-LAMA','issued_at'=>'2026-10-20 02:00:00']);
        $this->assertSame('%PDF-original', $disk->get('certificates/original.pdf'));
        $this->assertStringContainsString('/DiBiTaRaLogo', $disk->get('certificates/branding-v4/'.$id.'.pdf'));
        $this->artisan('dibitara:refresh-certificate-branding')->expectsOutput('0 sijil diselaraskan. Nama, nombor, tarikh dan kemajuan dikekalkan.')->assertSuccessful();
    }
}
