@php
    $college = ['6ème', '5ème', '4ème', '3ème'];
    $prefix  = ['Seconde' => 'Seconde ', 'Première' => '1ère ', 'Terminale' => 'Tle '];
    $series  = [
        'lycee_moderne'   => ['Seconde' => ['S', 'A4'], 'Première' => ['C', 'D', 'A4'], 'Terminale' => ['C', 'D', 'A4']],
        'lycee_technique' => ['Seconde' => ['F2', 'F3'], 'Première' => ['F2', 'F3'], 'Terminale' => ['F2', 'F3']],
    ];
    $data = ['college' => [], 'lycee_moderne' => [], 'lycee_technique' => []];
    foreach ($college as $n) { $data['college'][$n] = [$n]; }
    foreach ($series as $type => $levels) {
        foreach ($levels as $n => $list) {
            $data[$type][$n] = array_map(fn ($s) => $prefix[$n] . $s, $list);
        }
    }
    $typeLabels = ['college' => 'Collège', 'lycee_moderne' => 'Lycée moderne', 'lycee_technique' => 'Lycée technique'];
    $lv = old($levelName, $levelValue);
    $cl = old($className, $classValue);
@endphp

<div class="col-md-3 mb-3">
    <label class="form-label">Type de lycée *</label>
    <select id="type-ui" class="form-select" required>
        <option value="">Choisir...</option>
        @foreach($typeLabels as $k => $label)
            <option value="{{ $k }}">{{ $label }}</option>
        @endforeach
    </select>
</div>
<div class="col-md-3 mb-3">
    <label class="form-label">Niveau *</label>
    <select name="{{ $levelName }}" id="lv-ui" class="form-select" required></select>
</div>
<div class="col-md-3 mb-3">
    <label class="form-label">Classe *</label>
    <select name="{{ $className }}" id="cl-ui" class="form-select" required></select>
</div>

<script>
(function () {
    const data = @json($data);
    const typeSel = document.getElementById('type-ui');
    const lvSel = document.getElementById('lv-ui');
    const clSel = document.getElementById('cl-ui');
    const subj = document.getElementById('subject_id');
    let curLv = @json($lv) || '';
    let curCl = @json($cl) || '';

    function fillClasses() {
        const list = (data[typeSel.value] || {})[lvSel.value] || [];
        clSel.innerHTML = '';
        clSel.add(new Option(lvSel.value ? 'Choisir...' : "Choisir le niveau d'abord", ''));
        list.forEach(c => clSel.add(new Option(c, c, false, c === curCl)));
        if (curCl && lvSel.value && !list.includes(curCl)) {
            clSel.add(new Option(curCl + ' (ancienne valeur)', curCl, false, true));
        }
        if (list.length === 1 && !curCl) { clSel.value = list[0]; curCl = list[0]; }
    }

    function fillLevels() {
        const levels = Object.keys(data[typeSel.value] || {});
        lvSel.innerHTML = '';
        lvSel.add(new Option(typeSel.value ? 'Choisir...' : "Choisir le type d'abord", ''));
        levels.forEach(n => lvSel.add(new Option(n, n, false, n === curLv)));
        if (curLv && !levels.includes(curLv)) {
            lvSel.add(new Option(curLv + ' (ancienne valeur)', curLv, false, true));
        }
        fillClasses();
    }

    function filterSubjects(reset) {
        Array.from(subj.options).forEach(o => {
            const t = o.dataset.type;
            const off = !!(typeSel.value && t && t !== typeSel.value);
            o.hidden = off;
            o.disabled = off;
        });
        const sel = subj.selectedOptions[0];
        if (reset && sel && sel.disabled) { subj.value = ''; }
    }

    typeSel.addEventListener('change', function () {
        curLv = ''; curCl = '';
        filterSubjects(true);
        fillLevels();
    });
    lvSel.addEventListener('change', function () {
        curLv = lvSel.value; curCl = '';
        fillClasses();
    });
    subj.addEventListener('change', function () {
        const t = subj.selectedOptions[0] ? subj.selectedOptions[0].dataset.type : '';
        if (t && t !== typeSel.value) {
            typeSel.value = t; curLv = ''; curCl = '';
            filterSubjects(false);
            fillLevels();
        }
    });

    const first = subj.selectedOptions[0];
    if (first && first.dataset.type) { typeSel.value = first.dataset.type; }
    else if (data.college[curLv]) { typeSel.value = 'college'; }
    filterSubjects(false);
    fillLevels();
})();
</script>
