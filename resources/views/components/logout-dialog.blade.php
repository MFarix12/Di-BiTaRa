@props(['id', 'description'])
    <dialog class="admin-logout-dialog" x-ref="logoutDialog" aria-labelledby="{{ $id }}-title" aria-describedby="{{ $id }}-description" @close="$refs.trigger.focus()" @click="if ($event.target === $refs.logoutDialog) $refs.logoutDialog.close()">
        <div class="admin-logout-content">
            <x-admin-avatar class="admin-dialog-avatar" :decorative="true"/>
            <h2 id="{{ $id }}-title">Adakah anda pasti mahu log keluar?</h2>
            <p id="{{ $id }}-description">{{ $description }}</p>
            <div class="admin-logout-buttons">
                <button type="button" class="admin-logout-cancel" autofocus @click="$refs.logoutDialog.close()">Batal</button>
                <form action="{{ route('logout') }}" method="post">@csrf<button type="submit" class="admin-logout-confirm">Log Keluar <span aria-hidden="true">↪</span></button></form>
            </div>
        </div>
    </dialog>
