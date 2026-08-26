import os
import re

files = [
    r"e:\kotapplication\views\admin.php",
    r"e:\kotapplication\views\counter_display.php",
    r"e:\kotapplication\views\kot_display.php",
    r"e:\kotapplication\views\customer_menu.php"
]

injection = """    <script>
        const _cur = '<?= htmlspecialchars($settings['currency_code'] ?? 'BHD') ?>'.toUpperCase().trim();
        window.PRICE_DECIMALS = ['BHD', 'KWD', 'OMR', 'IQD', 'JOD', 'TND', 'LYD'].includes(_cur) ? 3 : 2;
    </script>"""

for filepath in files:
    if os.path.exists(filepath):
        with open(filepath, 'r', encoding='utf-8') as f:
            content = f.read()
            
        if "window.PRICE_DECIMALS =" not in content:
            content = re.sub(r'(<meta charset="UTF-8">)', r'\1\n' + injection, content, count=1)
            
            with open(filepath, 'w', encoding='utf-8') as f:
                f.write(content)
            print(f"Injected into {filepath}")
