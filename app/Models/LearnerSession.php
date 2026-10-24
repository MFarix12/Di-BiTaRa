<?php

namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;

// A session principal, not an account. No password, email, recovery or user row is created.
class LearnerSession extends Authenticatable
{
    protected $guarded=[];
    protected $hidden=['token_hash'];
    protected function casts(): array { return ['created_at'=>'datetime','completed_at'=>'datetime','ended_at'=>'datetime']; }
    public function hasStudentDetails(): bool
    {
        return trim((string)$this->class_name) !== '' && trim((string)$this->display_name) !== '';
    }
    public function getRoleAttribute(): string { return 'pelajar'; }
    public function getStatusAttribute(): string { return $this->ended_at ? 'inactive' : 'active'; }
    public function getUsernameAttribute(): string { return 'Sesi '.strtoupper(substr($this->public_id,0,8)); }
    public function getAuthPassword(): string { return ''; }
}
