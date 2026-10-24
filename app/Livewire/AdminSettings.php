<?php
namespace App\Livewire;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password;
use Livewire\Component;
class AdminSettings extends Component
{
 public string $theme='light';
 public string $text_size='normal';
 public bool $animations=true;
 public string $current_password='';
 public string $password='';
 public string $password_confirmation='';
 private function guard(): void {abort_unless(auth()->user()?->role==='admin' && auth()->user()?->status==='active',403);}
 public function mount(): void {
  $this->guard();$p=auth()->user()->admin_preferences??[];
  $this->theme=$p['theme']??'light';$this->text_size=$p['text_size']??'normal';$this->animations=$p['animations']??true;
 }
 public function save(): void {
  $this->guard();$data=$this->validate(['theme'=>'required|in:light,dark','text_size'=>'required|in:normal,large','animations'=>'boolean']);
  DB::transaction(function()use($data){auth()->user()->forceFill(['admin_preferences'=>$data])->save();$this->audit('settings_update');});
  session()->flash('status','Tetapan anda berjaya disimpan.');$this->redirectRoute('admin.settings');
 }
    public function changePassword(): void
    {
        $this->guard();
        $data = $this->validate(['current_password'=>'required|current_password', 'password'=>['required','confirmed',Password::min(8)]]);
        DB::transaction(function () use ($data) {
            auth()->user()->forceFill(['password'=>Hash::make($data['password']), 'remember_token'=>null])->save();
            $this->audit('password_update');
        });
        session()->put('password_hash_web', auth()->user()->getAuthPassword());
        session()->regenerate();
        $this->reset('current_password','password','password_confirmation');
        session()->flash('status', 'Kata laluan berjaya dikemas kini.');
    }


 private function audit(string $action): void {DB::table('audit_logs')->insert(['admin_user_id'=>auth()->id(),'action'=>$action,'entity_type'=>'user','entity_id'=>auth()->id(),'request_id'=>(string)\Illuminate\Support\Str::uuid(),'created_at'=>now()]);}
 public function render(){return view('livewire.admin-settings');}
}
