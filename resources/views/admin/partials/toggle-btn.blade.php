<form method="POST" action="{{ $route }}" class="d-inline">
    @csrf @method('PUT')
    <button class="btn btn-sm {{ $active ? 'btn-outline-success' : 'btn-outline-secondary' }}" title="{{ $active ? 'Désactiver' : 'Activer' }}">
        <i class="bi {{ $active ? 'bi-toggle-on' : 'bi-toggle-off' }}"></i>
    </button>
</form>
