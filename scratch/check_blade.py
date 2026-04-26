
import re

def check_blade_directives(filepath):
    with open(filepath, 'r', encoding='utf-8') as f:
        content = f.read()

    # Find all @if, @elseif, @else, @endif
    directives = re.findall(r'(@if|@elseif|@else|@endif)', content)
    
    level = 0
    mismatches = []
    
    for i, d in enumerate(directives):
        if d == '@if':
            level += 1
        elif d == '@endif':
            level -= 1
            if level < 0:
                mismatches.append(f"Unexpected @endif at index {i}")
                level = 0
        elif d == '@else' or d == '@elseif':
            if level == 0:
                mismatches.append(f"Unexpected {d} at index {i} (level 0)")
    
    if level > 0:
        mismatches.append(f"Unclosed @if (level {level} at end)")
        
    return mismatches, directives

mismatches, directives = check_blade_directives(r'c:\Users\jcsdi\Documents\Driving School Management System\DrivingApp\resources\views\school\guest\dashboard.blade.php')
print(f"Mismatches: {mismatches}")
print(f"Directives: {directives}")
