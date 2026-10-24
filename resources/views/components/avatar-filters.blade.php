@props(['roles'])
<div class="avatar-filter-bar">
    <div class="registration-avatar-tabs" role="group" aria-label="Tapis jantina avatar">
        @foreach(['all'=>'Semua','male'=>'Lelaki','female'=>'Perempuan'] as $value=>$label)
        <button type="button" @click="genderFilter='{{ $value }}'" :class="{'active':genderFilter==='{{ $value }}'}" :aria-pressed="genderFilter==='{{ $value }}'">{{ $label }}</button>
        @endforeach
    </div>
    <label class="avatar-role-filter">Jawatan<select x-model="roleFilter" aria-label="Jawatan avatar"><option value="all">Semua Jawatan</option>@foreach($roles as $role)<option value="{{ $role }}">{{ $role }}</option>@endforeach</select></label>
</div>
