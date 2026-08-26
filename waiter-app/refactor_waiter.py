import os
import re

filepath = r"e:\kotapplication\waiter-app\src\App.jsx"

with open(filepath, 'r', encoding='utf-8') as f:
    content = f.read()

orig_content = content

# Inject formatPrice function after settings state
injection = """
  const formatPrice = (amount) => {
    let curr = settings?.currency_code || 'BHD';
    let three_decimals = ['BHD', 'KWD', 'OMR', 'IQD', 'JOD', 'TND', 'LYD'];
    let decimals = three_decimals.includes(curr.toUpperCase().trim()) ? 3 : 2;
    return parseFloat(amount || 0).toFixed(decimals);
  };
"""

if "const formatPrice =" not in content:
    content = content.replace("const [settings, setSettings] = useState(null);", "const [settings, setSettings] = useState(null);" + injection)

# Replace .toFixed(3) with formatPrice
content = re.sub(r'parseFloat\(([^)]+)\)\.toFixed\(3\)', r'formatPrice(\1)', content)
content = re.sub(r'\(([^)]+)\)\.toFixed\(3\)', r'formatPrice(\1)', content)
# Ensure we catch any missed direct calls
content = re.sub(r'([^.])\.toFixed\(3\)', r'formatPrice(\1)', content)

if orig_content != content:
    with open(filepath, 'w', encoding='utf-8') as f:
        f.write(content)
    print("Waiter App App.jsx updated.")
