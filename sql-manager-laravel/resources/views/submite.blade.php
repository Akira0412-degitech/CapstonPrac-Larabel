<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            SQL Request Builder
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">

            {{-- Success/Error messages --}}
            @if(session('success'))
                <div class="mb-4 p-4 bg-green-100 text-green-700 rounded">
                    {{ session('success') }}
                </div>
            @endif

            @if(session('error'))
                <div class="mb-4 p-4 bg-red-100 text-red-700 rounded">
                    {{ session('error') }}
                </div>
            @endif

            {{-- Step 1: Table Selection --}}
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg mb-6">
                <div class="p-6">
                    <h3 class="text-lg font-semibold mb-4">Step 1: Select Table</h3>
                    <form method="POST" action="{{ route('submit.store') }}" id="sql-form">
                        @csrf
                        <div class="mb-4">
                            <label class="block text-sm font-medium text-gray-700 mb-2">Table</label>
                            <select name="table" id="table" class="w-full border rounded p-2" onchange="loadColumns()">
                                <option value="">-- Select Table --</option>
                                @foreach($tables as $table)
                                    @php
                                        $tableName = array_values((array)$table)[0];
                                    @endphp
                                    <option value="{{ $tableName }}">{{ $tableName }}</option>
                                @endforeach
                            </select>
                        </div>

                        {{-- Step 2: Operation --}}
                        <div id="operation-section" style="display:none;">
                            <h3 class="text-lg font-semibold mb-4">Step 2: Select Operation</h3>

                            <div class="mb-4">
                                <label class="block text-sm font-medium text-gray-700 mb-2">Operation</label>
                                <select name="operation" id="operation" class="w-full border rounded p-2" onchange="updateForm()">
                                    <option value="SELECT">SELECT</option>
                                    <option value="INSERT">INSERT</option>
                                    <option value="UPDATE">UPDATE</option>
                                    <option value="DELETE">DELETE</option>
                                </select>
                            </div>

                            {{-- Column section --}}
                            <div id="column-section" class="mb-4">
                                <label class="block text-sm font-medium text-gray-700 mb-2">Column</label>
                                <select name="column" id="column" class="w-full border rounded p-2">
                                    <option value="*">* (ALL)</option>
                                </select>
                            </div>

                            {{-- Value section --}}
                            <div id="value-section" class="mb-4" style="display:none;">
                                <label class="block text-sm font-medium text-gray-700 mb-2">New Value</label>
                                <input type="text" name="value" class="w-full border rounded p-2" placeholder="New value">
                            </div>

                            {{-- Insert section --}}
                            <div id="insert-section" class="mb-4" style="display:none;">
                                <label class="block text-sm font-medium text-gray-700 mb-2">Insert Values</label>
                                <div id="insert-fields"></div>
                            </div>

                            {{-- WHERE section --}}
                            <div id="where-section" class="mb-4">
                                <div class="grid grid-cols-2 gap-4">
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 mb-2">WHERE Column</label>
                                        <select name="where_column" id="where-column" class="w-full border rounded p-2">
                                            <option value="">-- None --</option>
                                        </select>
                                    </div>
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 mb-2">WHERE Value</label>
                                        <input type="text" name="where_value" class="w-full border rounded p-2" placeholder="Condition value">
                                    </div>
                                </div>
                            </div>

                            {{-- Generated SQL Preview --}}
                            <div id="sql-preview" class="mb-4 p-4 bg-gray-100 rounded font-mono text-sm" style="display:none;">
                                <p class="font-semibold mb-1">Generated SQL:</p>
                                <p id="sql-text"></p>
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
    function loadColumns() {
        const table = document.getElementById('table').value;
        if (!table) return;

        // Fetch columns via AJAX
        fetch(`/api/columns?table=${table}`)
            .then(res => res.json())
            .then(columns => {
                // Update column selects
                const columnSelect = document.getElementById('column');
                const whereSelect = document.getElementById('where-column');
                const insertFields = document.getElementById('insert-fields');

                columnSelect.innerHTML = '<option value="*">* (ALL)</option>';
                whereSelect.innerHTML = '<option value="">-- None --</option>';
                insertFields.innerHTML = '';

                columns.forEach(col => {
                    columnSelect.innerHTML += `<option value="${col}">${col}</option>`;
                    whereSelect.innerHTML += `<option value="${col}">${col}</option>`;
                    insertFields.innerHTML += `
                        <div class="mb-2">
                            <label class="text-sm text-gray-600">${col}</label>
                            <input type="text" name="insert_values[${col}]" 
                                   class="w-full border rounded p-2" placeholder="${col}">
                        </div>`;
                });

                document.getElementById('operation-section').style.display = 'block';
                updateForm();
                updateSQL();
            });
    }

    function updateForm() {
        const operation = document.getElementById('operation').value;

        document.getElementById('column-section').style.display = 'none';
        document.getElementById('value-section').style.display = 'none';
        document.getElementById('insert-section').style.display = 'none';
        document.getElementById('where-section').style.display = 'none';

        if (operation === 'SELECT') {
            document.getElementById('column-section').style.display = 'block';
            document.getElementById('where-section').style.display = 'block';
        } else if (operation === 'INSERT') {
            document.getElementById('insert-section').style.display = 'block';
        } else if (operation === 'UPDATE') {
            document.getElementById('column-section').style.display = 'block';
            document.getElementById('value-section').style.display = 'block';
            document.getElementById('where-section').style.display = 'block';
        } else if (operation === 'DELETE') {
            document.getElementById('where-section').style.display = 'block';
        }

        updateSQL();
    }

    function updateSQL() {
        const table = document.getElementById('table').value;
        const operation = document.getElementById('operation').value;
        const column = document.getElementById('column').value;
        const whereCol = document.getElementById('where-column').value;
        const whereVal = document.querySelector('[name="where_value"]').value;

        let sql = '';

        if (operation === 'SELECT') {
            sql = `SELECT \`${column}\` FROM \`${table}\``;
            if (whereCol && whereVal) sql += ` WHERE \`${whereCol}\` = '${whereVal}'`;
        } else if (operation === 'UPDATE') {
            const value = document.querySelector('[name="value"]').value;
            sql = `UPDATE \`${table}\` SET \`${column}\` = '${value}'`;
            if (whereCol && whereVal) sql += ` WHERE \`${whereCol}\` = '${whereVal}'`;
        } else if (operation === 'DELETE') {
            sql = `DELETE FROM \`${table}\``;
            if (whereCol && whereVal) sql += ` WHERE \`${whereCol}\` = '${whereVal}'`;
        }

        if (sql) {
            document.getElementById('sql-preview').style.display = 'block';
            document.getElementById('sql-text').textContent = sql;
            document.getElementById('sql_text_input').value = sql;
        }
    }

    // Update SQL preview on input change
    document.addEventListener('input', updateSQL);
    document.addEventListener('change', updateSQL);
    </script>
</x-app-layout>