@props(['code' => 'pilot-1', 'name' => 'Avatar juruterbang'])
@php($avatarImage = \App\Services\AvatarCatalogue::image($code))
<span class="pilot-portrait has-avatar-image" role="img" aria-label="{{ $name }}"><img src="{{ $avatarImage }}" alt="" loading="lazy" decoding="async"></span>
