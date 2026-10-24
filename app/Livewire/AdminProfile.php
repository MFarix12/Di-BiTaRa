<?php

namespace App\Livewire;

use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Livewire\Component;

class AdminProfile extends Component
{
    public string $display_name = '';
    public ?string $email = null;

    private function guard(): void
    {
        abort_unless(auth()->user()?->role === 'admin' && auth()->user()?->status === 'active', 403);
    }

    public function mount(): void
    {
        $this->guard();
        $this->display_name = auth()->user()->display_name;
        $this->email = auth()->user()->email;
    }

    public function save(): void
    {
        $this->guard();
        $this->email = filled($this->email) ? mb_strtolower(trim($this->email)) : null;
        $data = $this->validate(['display_name'=>'required|string|max:100', 'email'=>['nullable','email','max:254',Rule::unique('users')->ignore(auth()->id())]]);
        DB::transaction(function () use ($data) {
            auth()->user()->update($data);
            $this->audit('profile_update');
        });
        session()->flash('status', 'Profil Pentadbir berjaya dikemas kini.');
    }

    private function audit(string $action): void
    {
        DB::table('audit_logs')->insert(['admin_user_id'=>auth()->id(),'action'=>$action,'entity_type'=>'user','entity_id'=>auth()->id(),'request_id'=>(string) \Illuminate\Support\Str::uuid(),'created_at'=>now()]);
    }

    public function render() { return view('livewire.admin-profile'); }
}
