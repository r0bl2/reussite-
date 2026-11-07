@php $levels = ['6ème', '5ème', '4ème', '3ème', 'Seconde', 'Première', 'Terminale']; @endphp
<select name="{{ $name }}" class="form-select form-select-sm w-auto">
    <option value="">Tous niveaux</option>
    @foreach($levels as $l)
        <option value="{{ $l }}" @selected(request($name) === $l)>{{ $l }}</option>
    @endforeach
</select>
