import os
import glob

base_dir = r"c:\Users\jcsdi\Documents\Driving School Management System\DrivingApp\app\Http\Controllers"
files = glob.glob(os.path.join(base_dir, "*.php"))

for f in files:
    with open(f, 'r', encoding='utf-8') as file:
        content = file.read()
    if "Auth::guard('admin')->user()->" in content:
        content = content.replace("Auth::guard('admin')->user()->", "Auth::guard('admin')->user()?->")
        with open(f, 'w', encoding='utf-8') as file:
            file.write(content)
        print(f"Updated {f}")
