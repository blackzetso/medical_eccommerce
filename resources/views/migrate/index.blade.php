<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Migrate - إدارة قاعدة البيانات</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        body {
            font-family: 'Courier New', monospace;
            background: #1e1e1e;
            color: #d4d4d4;
            min-height: 100vh;
            padding: 20px;
        }
        .container {
            max-width: 1200px;
            margin: 0 auto;
        }
        .header {
            background: #252526;
            padding: 20px;
            border-radius: 5px;
            margin-bottom: 20px;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        h1 {
            color: #4ec9b0;
            font-size: 24px;
        }
        .logout-btn {
            background: #f48771;
            color: white;
            border: none;
            padding: 10px 20px;
            border-radius: 5px;
            cursor: pointer;
            text-decoration: none;
            display: inline-block;
        }
        .logout-btn:hover {
            background: #e06c75;
        }
        .controls {
            background: #252526;
            padding: 20px;
            border-radius: 5px;
            margin-bottom: 20px;
        }
        .migrate-btn {
            background: #4ec9b0;
            color: #1e1e1e;
            border: none;
            padding: 15px 30px;
            border-radius: 5px;
            font-size: 16px;
            font-weight: bold;
            cursor: pointer;
            font-family: 'Courier New', monospace;
            transition: background 0.3s;
        }
        .migrate-btn:hover {
            background: #3da89c;
        }
        .migrate-btn:disabled {
            background: #555;
            cursor: not-allowed;
        }
        .output-container {
            background: #1e1e1e;
            border: 2px solid #3c3c3c;
            border-radius: 5px;
            padding: 20px;
            min-height: 400px;
            max-height: 600px;
            overflow-y: auto;
        }
        .output {
            font-family: 'Courier New', monospace;
            font-size: 14px;
            line-height: 1.6;
            white-space: pre-wrap;
            word-wrap: break-word;
        }
        .output-line {
            margin-bottom: 5px;
        }
        .output-success {
            color: #4ec9b0;
        }
        .output-error {
            color: #f48771;
        }
        .output-info {
            color: #569cd6;
        }
        .output-warning {
            color: #dcdcaa;
        }
        .tables-list {
            margin-top: 20px;
            padding: 15px;
            background: #252526;
            border-radius: 5px;
        }
        .tables-list h3 {
            color: #4ec9b0;
            margin-bottom: 10px;
        }
        .tables-list ul {
            list-style: none;
            padding: 0;
        }
        .tables-list li {
            padding: 5px 0;
            color: #d4d4d4;
            border-bottom: 1px solid #3c3c3c;
        }
        .tables-list li:last-child {
            border-bottom: none;
        }
        .tables-list li::before {
            content: "✓ ";
            color: #4ec9b0;
            margin-left: 10px;
        }
        .loading {
            display: none;
            text-align: center;
            color: #569cd6;
            margin: 20px 0;
        }
        .loading.active {
            display: block;
        }
        .spinner {
            border: 3px solid #3c3c3c;
            border-top: 3px solid #4ec9b0;
            border-radius: 50%;
            width: 40px;
            height: 40px;
            animation: spin 1s linear infinite;
            margin: 0 auto;
        }
        @keyframes spin {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>🚀 Database Migrate Tool</h1>
            <form method="POST" action="{{ route('migrate.logout') }}" style="display: inline;">
                @csrf
                <button type="submit" class="logout-btn">تسجيل الخروج</button>
            </form>
        </div>

        <div class="controls">
            <button id="migrateBtn" class="migrate-btn" onclick="runMigrate()">
                ▶️ تنفيذ Migrate
            </button>
        </div>

        <div class="loading" id="loading">
            <div class="spinner"></div>
            <p>جاري تنفيذ migrate...</p>
        </div>

        <div class="output-container">
            <div id="output" class="output">
                <div class="output-line output-info">جاهز للتنفيذ... اضغط على زر "تنفيذ Migrate"</div>
            </div>
        </div>

        <div id="tablesContainer" class="tables-list" style="display: none;">
            <h3>📊 الجداول التي تم إنشاؤها/تحديثها:</h3>
            <ul id="tablesList"></ul>
        </div>
    </div>

    <script>
        function addOutputLine(text, type = 'info') {
            const output = document.getElementById('output');
            const line = document.createElement('div');
            line.className = `output-line output-${type}`;
            line.textContent = text;
            output.appendChild(line);
            output.scrollTop = output.scrollHeight;
        }

        function clearOutput() {
            document.getElementById('output').innerHTML = '';
            document.getElementById('tablesContainer').style.display = 'none';
            document.getElementById('tablesList').innerHTML = '';
        }

        function showTables(tables) {
            if (tables && tables.length > 0) {
                const tablesList = document.getElementById('tablesList');
                tables.forEach(table => {
                    const li = document.createElement('li');
                    li.textContent = table;
                    tablesList.appendChild(li);
                });
                document.getElementById('tablesContainer').style.display = 'block';
            }
        }

        async function runMigrate() {
            const btn = document.getElementById('migrateBtn');
            const loading = document.getElementById('loading');
            
            btn.disabled = true;
            loading.classList.add('active');
            clearOutput();

            addOutputLine('بدء تنفيذ migrate...', 'info');
            addOutputLine('─────────────────────────────────────', 'info');

            try {
                const response = await fetch('{{ route("migrate.run") }}', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                    }
                });

                const data = await response.json();

                if (data.success) {
                    // عرض output من Artisan
                    if (data.output) {
                        const lines = data.output.split('\n');
                        lines.forEach(line => {
                            if (line.trim()) {
                                let type = 'info';
                                if (line.includes('Migrating:')) {
                                    type = 'success';
                                } else if (line.includes('Migrated:')) {
                                    type = 'success';
                                } else if (line.includes('error') || line.includes('Error')) {
                                    type = 'error';
                                } else if (line.includes('warning') || line.includes('Warning')) {
                                    type = 'warning';
                                }
                                addOutputLine(line, type);
                            }
                        });
                    }

                    addOutputLine('─────────────────────────────────────', 'info');
                    addOutputLine(data.message || 'تم التنفيذ بنجاح', 'success');
                    
                    if (data.tables && data.tables.length > 0) {
                        addOutputLine(`\nتم إنشاء/تحديث ${data.tables.length} جدول:`, 'info');
                        showTables(data.tables);
                    } else {
                        addOutputLine('جميع migrations محدثة بالفعل', 'info');
                    }
                } else {
                    addOutputLine('حدث خطأ أثناء التنفيذ:', 'error');
                    addOutputLine(data.error || 'خطأ غير معروف', 'error');
                    if (data.output) {
                        addOutputLine(data.output, 'error');
                    }
                }
            } catch (error) {
                addOutputLine('حدث خطأ في الاتصال:', 'error');
                addOutputLine(error.message, 'error');
            } finally {
                btn.disabled = false;
                loading.classList.remove('active');
            }
        }
    </script>
</body>
</html>

