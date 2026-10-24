<?php
namespace Tests\Feature;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
class StudentRegistrationTest extends TestCase {
 use RefreshDatabase;
 public function test_account_creation_login_and_recovery_endpoints_are_no_longer_available(): void {
  foreach(['/daftar','/daftar/mula','/log-masuk','/pulihkan-akaun'] as $url) { $this->post($url,['username'=>'pelajar','password'=>'kata-laluan','role'=>'admin'])->assertNotFound(); }
  $this->assertDatabaseCount('users',0);$this->assertDatabaseCount('student_profiles',0);
 }
}
