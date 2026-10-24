"""One-time scaffold from the approved Database.md; review the generated migration."""
from pathlib import Path
import re

root = Path(__file__).resolve().parents[1]
text = (root / 'Database.md').read_text(encoding='utf-8')
sections = re.split(r'^### 3\.\d+ ([a-z_]+)\n', text, flags=re.M)
tables = {}
for i in range(1, len(sections), 2):
    name, body = sections[i], sections[i+1].split('\n## 4.')[0]
    fields = []
    for line in body.splitlines():
        if not line.startswith('| '): continue
        cells = [c.strip() for c in line.strip('|').split('|')]
        if len(cells) != 6 or cells[0] == 'Field': continue
        fields.append(cells)
    tables[name] = (body, fields)

order = ['users', 'media_files', 'avatars', 'student_profiles', 'account_recovery_tokens',
         'zones', 'instructions', 'learning_contents', 'activities', 'questions',
         'answer_options', 'question_items', 'question_targets', 'question_solutions',
         'content_media', 'question_audio', 'instruction_audio', 'learning_releases',
         'student_journeys', 'attempts', 'attempt_questions', 'responses', 'game_sessions',
         'content_completions', 'student_progress', 'passport_stamps', 'reflections',
         'certificates', 'audit_logs']
composite = {
    'answer_options': [['question_id','option_key']],
    'question_items': [['question_id','item_key']],
    'question_targets': [['question_id','target_key']],
    'media_files': [['disk','path']],
    'content_media': [['learning_content_id','media_file_id','purpose']],
    'question_audio': [['question_id','purpose']],
    'student_journeys': [['user_id','learning_release_id']],
    'attempts': [['student_journey_id','activity_id','attempt_no']],
    'attempt_questions': [['attempt_id','question_id'],['attempt_id','position']],
    'content_completions': [['student_journey_id','learning_content_id']],
    'student_progress': [['student_journey_id','zone_id']],
    'passport_stamps': [['student_journey_id','zone_id']],
    'reflections': [['student_journey_id','zone_id']],
}
indexes = {
    'users': [['role','status','deleted_at']],
    'learning_contents': [['zone_id','status','deleted_at','sort_order']],
    'activities': [['zone_id','status','deleted_at','sort_order']],
    'questions': [['activity_id','status','deleted_at','sort_order']],
    'attempts': [['student_journey_id','activity_id','status','score']],
    'audit_logs': [['created_at','id'],['admin_user_id','created_at'],['entity_type','entity_id','created_at']],
}

def php_string(s): return "'" + s.replace("'", "\\'") + "'"
lines = ['<?php', '', 'use Illuminate\\Database\\Migrations\\Migration;',
         'use Illuminate\\Database\\Schema\\Blueprint;', 'use Illuminate\\Support\\Facades\\Schema;', '',
         'return new class extends Migration', '{', '    public function up(): void', '    {']
for name in order:
    body, fields = tables[name]
    lines += [f"        Schema::create('{name}', function (Blueprint $table) {{", '            $table->id();']
    for field, typ, key, nullable, default, desc in fields:
        if typ == 'BIGINT UNSIGNED': decl = f"unsignedBigInteger('{field}')"
        elif typ == 'INT UNSIGNED': decl = f"unsignedInteger('{field}')"
        elif typ == 'SMALLINT UNSIGNED': decl = f"unsignedSmallInteger('{field}')"
        elif typ == 'TINYINT UNSIGNED': decl = f"unsignedTinyInteger('{field}')"
        elif typ.startswith('VARCHAR'): decl = f"string('{field}', {re.search(r'\d+',typ)[0]})"
        elif typ.startswith('CHAR'): decl = f"char('{field}', {re.search(r'\d+',typ)[0]})"
        elif typ == 'DATETIME(3)': decl = f"dateTime('{field}', 3)"
        elif typ == 'DECIMAL(5,2)': decl = f"decimal('{field}', 5, 2)"
        else: decl = {'TEXT':f"text('{field}')",'LONGTEXT':f"longText('{field}')",'JSON':f"json('{field}')",'BOOLEAN':f"boolean('{field}')"}[typ]
        if nullable == 'Ya': decl += '->nullable()'
        if default.startswith('CURRENT_TIMESTAMP'): decl += '->useCurrent()'
        elif default not in ['—','NULL']:
            decl += '->default(' + (default if re.fullmatch(r'\d+(\.\d+)?',default) else php_string(default)) + ')'
        if 'UNIQUE' in key and 'UNIQUE(' not in key: decl += '->unique()'
        lines.append('            $table->' + decl + ';')
    common = re.search(r'Set bersama: ([^.]+)', body)[1]
    if 'T' in common:
        lines += ["            $table->dateTime('created_at', 3)->useCurrent();", "            $table->dateTime('updated_at', 3)->useCurrent();"]
    if 'S' in common: lines += ["            $table->softDeletes('deleted_at', 3);"]
    for cols in composite.get(name, []): lines += ['            $table->unique([' + ', '.join(map(php_string,cols)) + ']);']
    for cols in indexes.get(name, []): lines += ['            $table->index([' + ', '.join(map(php_string,cols)) + ']);']
    for field, typ, key, *_ in fields:
        match = re.search(r'FK ([a-z_]+)\.id',key)
        if match:
            action = 'nullOnDelete' if name == 'student_profiles' and field == 'avatar_id' else 'restrictOnDelete'
            lines += [f"            $table->foreign('{field}')->references('id')->on('{match[1]}')->{action}();"]
    lines += ['        });', '']
lines += ['    }', '', '    public function down(): void', '    {']
for name in reversed(order): lines += [f"        Schema::dropIfExists('{name}');"]
lines += ['    }', '};', '']
target = root/'database/migrations/2026_10_20_000000_create_dibitara_schema.php'
target.write_text('\n'.join(lines),encoding='utf-8')
print(f'Generated {len(order)} application tables: {target}')
