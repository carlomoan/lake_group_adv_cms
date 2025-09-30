<?php
/**
 * Direct API Test - Shows exactly what JavaScript will receive
 */

header('Content-Type: text/html; charset=utf-8');
?>
<!DOCTYPE html>
<html>
<head>
    <title>Direct API Test</title>
    <style>
        body { font-family: monospace; padding: 20px; background: #1e1e1e; color: #d4d4d4; }
        .success { color: #4ec9b0; font-weight: bold; }
        .error { color: #f48771; font-weight: bold; }
        pre { background: #2d2d2d; padding: 15px; border-radius: 5px; overflow-x: auto; white-space: pre-wrap; word-wrap: break-word; }
        .box { background: #2d2d2d; padding: 20px; margin: 20px 0; border-radius: 8px; }
        button { background: #0e639c; color: white; padding: 10px 20px; border: none; border-radius: 5px; cursor: pointer; margin: 5px; }
        button:hover { background: #1177bb; }
    </style>
</head>
<body>
    <h1>🧪 Direct API Test</h1>

    <div class="box">
        <h2>Test APIs via JavaScript</h2>
        <p>This simulates exactly what your webpage does:</p>
        <button onclick="testAPI('/admin/save_content.php')">Test /admin/save_content.php</button>
        <button onclick="testAPI('/api/media-library.php')">Test /api/media-library.php</button>
        <button onclick="testAPI('admin/save_content.php')">Test admin/save_content.php (relative)</button>
        <div id="result"></div>
    </div>

    <script>
        async function testAPI(url) {
            const resultDiv = document.getElementById('result');
            resultDiv.innerHTML = '<p style="color: #dcdcaa;">Testing: ' + url + '</p>';

            try {
                console.log('Fetching:', url);
                const response = await fetch(url, {
                    method: 'GET',
                    headers: {
                        'Content-Type': 'application/json'
                    }
                });

                console.log('Response status:', response.status);
                console.log('Response headers:', [...response.headers.entries()]);

                const text = await response.text();
                console.log('Response text (first 500 chars):', text.substring(0, 500));

                resultDiv.innerHTML = '<h3>Response from: ' + url + '</h3>';
                resultDiv.innerHTML += '<p><strong>Status:</strong> ' + response.status + ' ' + response.statusText + '</p>';
                resultDiv.innerHTML += '<p><strong>Content-Type:</strong> ' + response.headers.get('content-type') + '</p>';
                resultDiv.innerHTML += '<p><strong>Response length:</strong> ' + text.length + ' bytes</p>';

                // Show first character code
                if (text.length > 0) {
                    resultDiv.innerHTML += '<p><strong>First character:</strong> "' + text[0] + '" (code: ' + text.charCodeAt(0) + ')</p>';
                }

                try {
                    const json = JSON.parse(text);
                    resultDiv.innerHTML += '<p style="color: #4ec9b0; font-weight: bold;">✅ Valid JSON!</p>';
                    resultDiv.innerHTML += '<pre>' + JSON.stringify(json, null, 2) + '</pre>';
                } catch (e) {
                    resultDiv.innerHTML += '<p style="color: #f48771; font-weight: bold;">❌ NOT valid JSON</p>';
                    resultDiv.innerHTML += '<p><strong>Parse error:</strong> ' + e.message + '</p>';
                    resultDiv.innerHTML += '<p><strong>Raw response (first 1000 chars):</strong></p>';
                    resultDiv.innerHTML += '<pre>' + text.substring(0, 1000).replace(/</g, '&lt;').replace(/>/g, '&gt;') + '</pre>';
                }

            } catch (error) {
                resultDiv.innerHTML = '<p style="color: #f48771;">❌ Fetch failed: ' + error.message + '</p>';
                console.error('Fetch error:', error);
            }
        }
    </script>

</body>
</html>
