<?php

namespace App\Livewire;

use Illuminate\Support\Facades\DB;
use Livewire\Attributes\Locked;
use Livewire\Component;
use Livewire\WithPagination;

class ContentManager extends Component
{
    use WithPagination;
    public string $search = '';
    public string $zoneFilter = '';
    public string $title = '';
    public string $body = '';
    public ?int $zone_id = null;
    public bool $is_required = true;
    public int $sort_order = 0;
    public bool $editing = false;
    #[Locked] public ?int $editId = null;
    #[Locked] public int $revision = 1;
    public bool $preview = false;

    private function guard(): void
    {
        abort_unless(auth()->user()?->role === 'admin' && auth()->user()?->status === 'active', 403);
    }

    public function mount(): void { $this->guard(); }
    public function updatedSearch(): void { $this->resetPage(); }
    public function updatedZoneFilter(): void { $this->resetPage(); }

    public function create(): void
    {
        $this->guard();
        $this->reset('title','body','zone_id','editId','revision','preview','sort_order');
        $this->resetValidation();
        $this->is_required = true;
        $this->editing = true;
    }

    public function edit(int $id): void
    {
        $this->guard();
        $row = DB::table('learning_contents')->whereNull('deleted_at')->find($id);
        abort_unless($row, 404);
        $this->resetValidation();
        $this->editId = $id;
        foreach (['title','body','zone_id','sort_order','revision'] as $key) { $this->$key = $row->$key; }
        $this->is_required = (bool) $row->is_required;
        $this->editing = true;
        $this->preview = false;
    }

    public function save(): void
    {
        $this->guard();
        $data = $this->validate(['title' => 'required|string|max:180', 'body' => 'required|string|max:20000', 'zone_id' => 'required|integer|exists:zones,id', 'is_required' => 'boolean', 'sort_order' => 'required|integer|min:0|max:65535']);
        // Phase 1 deliberately accepts plain text. Blade escapes it in all previews.
        $data['body'] = strip_tags($data['body']);
        $data['updated_by'] = auth()->id();
        $data['updated_at'] = now();
        $saved = DB::transaction(function () use ($data) {
            if ($this->editId) {
                $before = DB::table('learning_contents')->whereNull('deleted_at')->lockForUpdate()->find($this->editId);
                abort_unless($before, 404);
                if ($before->revision !== $this->revision) { $this->addError('title', 'Nota telah dikemas kini oleh Pentadbir lain. Buka semula versi terkini.'); return false; }
                DB::table('learning_contents')->where('id', $this->editId)->update($data + ['revision' => $this->revision + 1, 'status' => 'draft']);
                $id = $this->editId;
            } else {
                $id = DB::table('learning_contents')->insertGetId($data + ['created_by' => auth()->id(), 'kind' => 'note', 'status' => 'draft']);
            }
            $this->audit($this->editId ? 'update' : 'create', $id, ['title' => $data['title'], 'status' => 'draft']);
            return true;
        });
        if ($saved) {
            $this->editing = false;
            session()->flash('status', 'Nota disimpan sebagai draf. Penerbitan keluaran dibina pada fasa seterusnya.');
        }
    }

    public function delete(int $id): void
    {
        $this->guard();
        DB::transaction(function () use ($id) {
            $row = DB::table('learning_contents')->whereNull('deleted_at')->lockForUpdate()->find($id);
            abort_unless($row, 404);
            DB::table('learning_contents')->where('id', $id)->update(['deleted_at' => now(), 'updated_at' => now(), 'updated_by' => auth()->id(), 'status' => 'inactive']);
            $this->audit('delete', $id, ['title' => $row->title, 'status' => 'inactive']);
        });
        session()->flash('status', 'Nota dipadam lembut. Rekod sejarah dikekalkan.');
    }

    private function audit(string $action, int $id, array $after): void
    {
        DB::table('audit_logs')->insert(['admin_user_id' => auth()->id(), 'action' => $action, 'entity_type' => 'learning_contents', 'entity_id' => $id, 'after_data' => json_encode($after), 'request_id' => (string) \Illuminate\Support\Str::uuid()]);
    }

    public function render()
    {
        $this->guard();
        $rows = DB::table('learning_contents')->leftJoin('zones','zones.id','=','learning_contents.zone_id')
            ->whereNull('learning_contents.deleted_at')->when($this->search, fn ($q) => $q->where('learning_contents.title','like','%'.$this->search.'%'))
            ->when($this->zoneFilter, fn ($q) => $q->where('zone_id',$this->zoneFilter))
            ->select('learning_contents.*','zones.name as zone_name')->orderBy('learning_contents.id','desc')->paginate(10);
        return view('livewire.content-manager', ['rows' => $rows, 'zones' => DB::table('zones')->orderBy('sequence')->get()]);
    }
}
