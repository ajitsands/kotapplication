import os
import re

base_dir = r"e:\kotapplication"

def process_file(filepath):
    with open(filepath, 'r', encoding='utf-8') as f:
        content = f.read()
        
    orig_content = content

    # Replace number_format(X, 3, '.', '') with format_price(X, null, true)
    content = re.sub(r"number_format\(([^,]+),\s*3\s*,\s*'\.'\s*,\s*''\)", r"format_price(\1, null, true)", content)
    
    # Replace number_format(X, 3) with format_price(X)
    content = re.sub(r"number_format\(([^,]+),\s*3\s*\)", r"format_price(\1)", content)

    if orig_content != content:
        with open(filepath, 'w', encoding='utf-8') as f:
            f.write(content)
        print(f"Updated {filepath}")

for root, dirs, files in os.walk(base_dir):
    if "vendor" in root or "node_modules" in root or ".git" in root or "waiter-app" in root:
        continue
    for file in files:
        if file.endswith(".php"):
            process_file(os.path.join(root, file))

print("PHP replacement complete.")
