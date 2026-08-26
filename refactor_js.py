import os
import re

base_dir = r"e:\kotapplication\views"

def process_file(filepath):
    with open(filepath, 'r', encoding='utf-8') as f:
        content = f.read()
        
    orig_content = content
    
    # Replace .toFixed(3) with .toFixed(window.PRICE_DECIMALS || 3)
    content = content.replace(".toFixed(3)", ".toFixed(window.PRICE_DECIMALS || 3)")

    if orig_content != content:
        with open(filepath, 'w', encoding='utf-8') as f:
            f.write(content)
        print(f"Updated {filepath}")

for root, dirs, files in os.walk(base_dir):
    for file in files:
        if file.endswith(".php"):
            process_file(os.path.join(root, file))

print("JS replacement in views complete.")
