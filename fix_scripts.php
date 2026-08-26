<?php
$viewsDir = __DIR__ . '/views';
$files = glob($viewsDir . '/*.php');

foreach ($files as $file) {
    $content = file_get_contents($file);
    
    $search = "        function closeSandsProductsModal() {\n            document.getElementById('sands-products-modal').style.display = 'none';\n            openSandsModal();\n        }";
    $replace = "        function closeSandsProductsModal() {\n            document.getElementById('sands-products-modal').style.display = 'none';\n            openSandsModal();\n        }\n    </script>";
    
    if (strpos($content, $search) !== false && strpos($content, $replace) === false) {
        $content = str_replace($search, $replace, $content);
        file_put_contents($file, $content);
        echo "Fixed script in: " . basename($file) . "\n";
    }
}
echo "Done.\n";
