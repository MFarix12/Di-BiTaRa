<?php

namespace Tests\Feature;

use App\Models\User;
use App\Services\Audit;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Validator;
use Tests\TestCase;

class MalayInterfaceTest extends TestCase
{
    use RefreshDatabase;

    private function assertMalayPage(string $url): void
    {
        $response = $this->get($url)->assertOk();
        $document = new \DOMDocument;
        $old = libxml_use_internal_errors(true);
        $document->loadHTML('<?xml encoding="utf-8" ?>'.$response->getContent());
        libxml_clear_errors();
        libxml_use_internal_errors($old);
        $xpath = new \DOMXPath($document);
        foreach ($xpath->query('//script|//style') as $node) $node->parentNode->removeChild($node);
        $text = $document->textContent;
        foreach ($xpath->query('//*[@aria-label or @title or @placeholder]') as $node) {
            foreach (['aria-label','title','placeholder'] as $attribute) $text .= ' '.$node->getAttribute($attribute);
        }
        $this->assertDoesNotMatchRegularExpression('/\b(Dashboard|Settings|Profile|Logout|Submit|Next|Previous|Score|Progress|Achievement|Quiz|Preview|Upload|Download|Loading|Reset|Pilot|Admin)\b/i', $text, $url);
    }

    public function test_public_student_and_admin_pages_use_malay_display_terms(): void
    {
        $this->seed();
        foreach (['/','/tentang','/cara-bermain','/capai-pembelajaran','/masukkan-kod','/log-masuk-admin'] as $url) $this->assertMalayPage($url);
        $student = $this->makeLearner(['display_name'=>'Murid Bahasa']);
        $this->actingAs($student,'learner');
        foreach (['/profil-sesi','/kuiz-aktiviti','/pasport-pencapaian','/profil-sesi?tutorial=1','/notifikasi','/bantuan'] as $url) $this->assertMalayPage($url);
        $this->actingAs(User::factory()->create(['role'=>'admin','display_name'=>'Pentadbir Bahasa','username'=>'pentadbir-bahasa']),'web');
        foreach (['/admin','/admin/kandungan','/admin/nota','/admin/kuiz','/admin/soalan','/admin/arahan','/admin/audio','/admin/media','/admin/pengguna','/admin/audit','/admin/profil','/admin/tetapan'] as $url) $this->assertMalayPage($url);
    }

    public function test_validation_and_pagination_do_not_fall_back_to_english(): void
    {
        $validator = Validator::make(['minutes'=>999,'items'=>'wrong','password'=>'abc','password_confirmation'=>'def','email'=>'wrong'], ['minutes'=>'between:1,120|numeric','items'=>'array','password'=>'min:8|confirmed','email'=>'email']);
        $errors = implode(' ', $validator->errors()->all());
        $this->assertStringContainsString('masa', $errors);
        $this->assertStringContainsString('kata laluan', $errors);
        $this->assertStringContainsString('e-mel', $errors);
        $this->assertStringNotContainsString('must', $errors);
        $this->assertStringNotContainsString('validation.', $errors);
        $paginator = new \Illuminate\Pagination\LengthAwarePaginator(range(1,15),40,15);
        $html = $paginator->links('pagination::tailwind')->toHtml();
        $this->assertStringContainsString('Navigasi halaman', $html);
        $this->assertStringContainsString('rekod', $html);
        $this->assertStringNotContainsString('Showing', $html);
        $this->assertStringNotContainsString('Go to page', $html);
    }

    public function test_audit_display_translates_storage_values_and_hides_internal_fields(): void
    {
        $this->assertSame('Terbitkan', Audit::actionLabel('publish'));
        $this->assertSame('Keluaran Pembelajaran', Audit::entityLabel('learning_releases'));
        $summary = Audit::summary(json_encode(['status'=>'published','is_required'=>true,'internal_key'=>'secret']));
        $this->assertSame('Status: Diterbitkan · Wajib: Ya', $summary);
        $this->assertStringNotContainsString('internal_key', $summary);
    }
}
