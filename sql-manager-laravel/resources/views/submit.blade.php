<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            SQL Request Builder
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-5xl mx-auto sm:px-6 lg:px-8">
            @if (session('success'))
                <div class="mb-4 p-4 bg-green-100 text-green-700 rounded">
                    {{ session('success') }}
                </div>
            @endif

            @if ($errors->any())
                <div class="mb-4 p-4 bg-red-100 text-red-700 rounded">
                    {{ $errors->first() }}
                </div>
            @endif

            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6">
                    <form method="POST" action="{{ route('submit.store') }}" id="sql-form" class="space-y-6">
                        @csrf

                        <div>
                            <label for="table" class="block text-sm font-medium text-gray-700 mb-2">1. Table</label>
                            <select name="table" id="table" class="w-full border rounded p-2">
                                <option value="">-- Select Table --</option>
                                @foreach ($tables as $table)
                                    <option value="{{ $table }}">{{ $table }}</option>
                                @endforeach
                            </select>
                        </div>

                        <div id="builder" class="hidden space-y-6">
                            <div>
                                <label for="operation" class="block text-sm font-medium text-gray-700 mb-2">2. Operation</label>
                                <select name="operation" id="operation" class="w-full border rounded p-2">
                                    <option value="SELECT">SELECT</option>
                                    <option value="INSERT">INSERT</option>
                                    <option value="UPDATE">UPDATE</option>
                                    <option value="DELETE">DELETE</option>
                                </select>
                            </div>

                            <div id="select-columns-section">
                                <p class="block text-sm font-medium text-gray-700 mb-2">3. Columns (SELECT)</p>
                                <div id="select-columns" class="grid sm:grid-cols-2 gap-2"></div>
                            </div>

                            <div id="update-section" class="hidden">
                                <label for="update-column" class="block text-sm font-medium text-gray-700 mb-2">3. Update Column</label>
                                <select id="update-column" class="w-full border rounded p-2"></select>
                                <label for="update-value" class="block text-sm font-medium text-gray-700 mt-4 mb-2">New Value</label>
                                <input id="update-value" type="text" class="w-full border rounded p-2" placeholder="New value">
                            </div>

                            <div id="insert-section" class="hidden">
                                <p class="block text-sm font-medium text-gray-700 mb-2">3. Insert Values</p>
                                <div id="insert-fields" class="grid sm:grid-cols-2 gap-3"></div>
                            </div>

                            <div id="where-section">
                                <p class="block text-sm font-medium text-gray-700 mb-2">4. Optional WHERE</p>
                                <div class="grid sm:grid-cols-3 gap-3">
                                    <select id="where-column" class="border rounded p-2">
                                        <option value="">-- Column --</option>
                                    </select>
                                    <select id="where-operator" class="border rounded p-2">
                                        <option value="=">=</option>
                                        <option value="!=">!=</option>
                                        <option value=">">&gt;</option>
                                        <option value="<">&lt;</option>
                                        <option value="LIKE">LIKE</option>
                                    </select>
                                    <input id="where-value" type="text" class="border rounded p-2" placeholder="Value">
                                </div>
                            </div>

                            <div class="p-4 bg-gray-100 rounded">
                                <p class="text-sm font-semibold text-gray-700 mb-1">Generated SQL</p>
                                <pre id="sql-text" class="text-sm text-gray-800 whitespace-pre-wrap"></pre>
                            </div>

                            <input type="hidden" name="sql_text" id="sql_text_input">
                            <button type="submit" class="bg-blue-500 text-white px-6 py-2 rounded hover:bg-blue-600">
                                Submit Request
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <script>
        let currentColumns = [];

        const tableEl = document.getElementById('table');
        const opEl = document.getElementById('operation');
        const builderEl = document.getElementById('builder');
        const selectColumnsSectionEl = document.getElementById('select-columns-section');
        const selectColumnsEl = document.getElementById('select-columns');
        const updateSectionEl = document.getElementById('update-section');
        const updateColumnEl = document.getElementById('update-column');
        const updateValueEl = document.getElementById('update-value');
        const insertSectionEl = document.getElementById('insert-section');
        const insertFieldsEl = document.getElementById('insert-fields');
        const whereSectionEl = document.getElementById('where-section');
        const whereColumnEl = document.getElementById('where-column');
        const whereOperatorEl = document.getElementById('where-operator');
        const whereValueEl = document.getElementById('where-value');
        const sqlTextEl = document.getElementById('sql-text');
        const sqlHiddenEl = document.getElementById('sql_text_input');

        function escapeSql(value) {
            return String(value).replace(/'/g, "''");
        }

        function quoteValue(value) {
            return `'${escapeSql(value)}'`;
        }

        function loadColumns() {
            const table = tableEl.value;
            if (!table) {
                builderEl.classList.add('hidden');
                currentColumns = [];
                return;
            }

            fetch(`/api/columns?table=${encodeURIComponent(table)}`)
                .then((res) => {
                    if (!res.ok) {
                        throw new Error('Failed to load columns');
                    }
                    return res.json();
                })
                .then((columns) => {
                    currentColumns = columns;
                    builderEl.classList.remove('hidden');
                    hydrateColumnUIs(columns);
                    updateFormVisibility();
                    buildSql();
                })
                .catch(() => {
                    sqlTextEl.textContent = 'Could not load columns for selected table.';
                    sqlHiddenEl.value = '';
                });
        }

        function hydrateColumnUIs(columns) {
            selectColumnsEl.innerHTML = '';
            updateColumnEl.innerHTML = '';
            whereColumnEl.innerHTML = '<option value="">-- Column --</option>';
            insertFieldsEl.innerHTML = '';

            columns.forEach((col) => {
                const id = `select-col-${col}`;
                selectColumnsEl.innerHTML += `
                    <label for="${id}" class="flex items-center gap-2 text-sm">
                        <input id="${id}" type="checkbox" data-select-col="${col}">
                        <span>${col}</span>
                    </label>
                `;

                updateColumnEl.innerHTML += `<option value="${col}">${col}</option>`;
                whereColumnEl.innerHTML += `<option value="${col}">${col}</option>`;

                insertFieldsEl.innerHTML += `
                    <div>
                        <label class="block text-xs text-gray-600 mb-1">${col}</label>
                        <input type="text" data-insert-col="${col}" class="w-full border rounded p-2" placeholder="${col}">
                    </div>
                `;
            });

            document.querySelectorAll('[data-select-col]').forEach((el) => {
                el.addEventListener('change', buildSql);
            });
            document.querySelectorAll('[data-insert-col]').forEach((el) => {
                el.addEventListener('input', buildSql);
            });
        }

        function updateFormVisibility() {
            const op = opEl.value;
            selectColumnsSectionEl.classList.toggle('hidden', op !== 'SELECT');
            updateSectionEl.classList.toggle('hidden', op !== 'UPDATE');
            insertSectionEl.classList.toggle('hidden', op !== 'INSERT');
            whereSectionEl.classList.toggle('hidden', op === 'INSERT');
        }

        function buildSql() {
            const table = tableEl.value;
            const op = opEl.value;
            if (!table || !op) {
                sqlTextEl.textContent = '';
                sqlHiddenEl.value = '';
                return;
            }

            let sql = '';
            const whereCol = whereColumnEl.value;
            const whereVal = whereValueEl.value;
            const whereOp = whereOperatorEl.value;
            const hasWhere = whereCol && whereVal !== '';

            if (op === 'SELECT') {
                const selectedCols = Array.from(document.querySelectorAll('[data-select-col]:checked'))
                    .map((el) => `\`${el.dataset.selectCol}\``);
                const colSql = selectedCols.length > 0 ? selectedCols.join(', ') : '*';
                sql = `SELECT ${colSql} FROM \`${table}\``;
                if (hasWhere) {
                    sql += ` WHERE \`${whereCol}\` ${whereOp} ${quoteValue(whereVal)}`;
                }
            }

            if (op === 'INSERT') {
                const pairs = Array.from(document.querySelectorAll('[data-insert-col]'))
                    .map((el) => ({ col: el.dataset.insertCol, val: el.value }))
                    .filter((x) => x.val !== '');

                if (pairs.length > 0) {
                    const cols = pairs.map((x) => `\`${x.col}\``).join(', ');
                    const vals = pairs.map((x) => quoteValue(x.val)).join(', ');
                    sql = `INSERT INTO \`${table}\` (${cols}) VALUES (${vals})`;
                }
            }

            if (op === 'UPDATE') {
                const updateCol = updateColumnEl.value;
                const updateVal = updateValueEl.value;
                if (updateCol && updateVal !== '') {
                    sql = `UPDATE \`${table}\` SET \`${updateCol}\` = ${quoteValue(updateVal)}`;
                    if (hasWhere) {
                        sql += ` WHERE \`${whereCol}\` ${whereOp} ${quoteValue(whereVal)}`;
                    }
                }
            }

            if (op === 'DELETE') {
                sql = `DELETE FROM \`${table}\``;
                if (hasWhere) {
                    sql += ` WHERE \`${whereCol}\` ${whereOp} ${quoteValue(whereVal)}`;
                }
            }

            sqlTextEl.textContent = sql || 'Please fill required fields.';
            sqlHiddenEl.value = sql;
        }

        tableEl.addEventListener('change', loadColumns);
        opEl.addEventListener('change', () => {
            updateFormVisibility();
            buildSql();
        });
        updateColumnEl.addEventListener('change', buildSql);
        updateValueEl.addEventListener('input', buildSql);
        whereColumnEl.addEventListener('change', buildSql);
        whereOperatorEl.addEventListener('change', buildSql);
        whereValueEl.addEventListener('input', buildSql);
    </script>
</x-app-layout>
