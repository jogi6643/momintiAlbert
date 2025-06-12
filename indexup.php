<!DOCTYPE html>

<?php
$jsonFile = 'data.json';

// Check if file exists and is readable
if (!file_exists($jsonFile)) {
    die("Error: data.json file not found in " . __DIR__);
}

if (!is_readable($jsonFile)) {
    die("Error: data.json file is not readable. Check file permissions.");
}

// Get file contents
$jsonContent = file_get_contents($jsonFile);
if ($jsonContent === false) {
    die("Error: Could not read data.json file");
}

// Check if file is empty
if (empty(trim($jsonContent))) {
    die("Error: data.json file is empty");
}

// First, try to decode as-is
$data = json_decode($jsonContent, true);

// If JSON is invalid, try to fix common issues
if ($data === null) {
    echo "<!-- Attempting to fix JSON automatically... -->";
    
    $fixedContent = $jsonContent;
    
    // Simple method: Find and fix answer fields manually
    // Your specific issue: "answer": "{"time":... needs to be "answer": "{\"time\":...
    
    // Step 1: Find all lines with answer fields
    $lines = explode("\n", $jsonContent);
    $fixedLines = [];
    $insideAnswer = false;
    $answerBuffer = '';
    $currentIndent = '';
    
    foreach ($lines as $lineNumber => $line) {
        $trimmed = trim($line);
        
        // Check if this line starts with "answer":
        if (preg_match('/^(\s*)"answer":\s*"(.*)$/', $line, $matches)) {
            $currentIndent = $matches[1];
            $answerStart = $matches[2];
            
            // Check if this line also ends the answer (contains closing quote)
            if (preg_match('/^(.*)"(\s*[,}]?\s*)$/', $answerStart, $endMatches)) {
                // Single line answer - escape it
                $answerContent = $endMatches[1];
                $endPart = $endMatches[2];
                
                // Escape quotes in the answer content
                $escapedAnswer = str_replace('"', '\\"', $answerContent);
                $fixedLines[] = $currentIndent . '"answer": "' . $escapedAnswer . '"' . $endPart;
            } else {
                // Multi-line answer starts here
                $insideAnswer = true;
                $answerBuffer = $answerStart;
            }
        } else if ($insideAnswer) {
            // We're collecting multi-line answer content
            if (preg_match('/^(.*)"(\s*[,}]?\s*)$/', $line, $matches)) {
                // This line ends the answer
                $answerBuffer .= "\n" . $matches[1];
                $endPart = $matches[2];
                
                // Escape the entire answer content
                $escapedAnswer = str_replace('"', '\\"', $answerBuffer);
                $fixedLines[] = $currentIndent . '"answer": "' . $escapedAnswer . '"' . $endPart;
                
                $insideAnswer = false;
                $answerBuffer = '';
            } else {
                // Continue collecting answer content
                $answerBuffer .= "\n" . $line;
            }
        } else {
            // Regular line, keep as is
            $fixedLines[] = $line;
        }
    }
    
    $fixedContent = implode("\n", $fixedLines);
    
    // Try alternative approach: Parse line by line and fix answer fields
    if ($data === null) {
        $lines = explode("\n", $jsonContent);
        $fixedLines = [];
        $insideAnswer = false;
        $answerBuffer = '';
        $answerIndent = '';
        
        foreach ($lines as $line) {
            $trimmedLine = trim($line);
            
            // Check if this line starts an answer field
            if (preg_match('/^(\s*)"answer":\s*"(.*)$/', $line, $matches)) {
                $answerIndent = $matches[1];
                $answerStart = $matches[2];
                
                // Check if the answer ends on the same line
                if (preg_match('/^(.*)"$/', $answerStart)) {
                    // Single line answer - just escape it
                    $escapedAnswer = addslashes($answerStart);
                    $fixedLines[] = $answerIndent . '"answer": "' . $escapedAnswer . '"';
                } else {
                    // Multi-line answer - start collecting
                    $insideAnswer = true;
                    $answerBuffer = $answerStart;
                }
            } elseif ($insideAnswer) {
                // We're inside a multi-line answer
                if (preg_match('/^(.*)"(\s*[,}])?\s*$/', $line, $matches)) {
                    // This line ends the answer
                    $answerBuffer .= "\n" . $matches[1];
                    
                    // Try to parse and fix the answer content
                    $fixedAnswer = $answerBuffer;
                    
                    // If it looks like JSON, try to fix it
                    if (strpos($fixedAnswer, '{') !== false) {
                        // Find JSON boundaries
                        $startPos = strpos($fixedAnswer, '{');
                        if ($startPos !== false) {
                            $jsonPart = substr($fixedAnswer, $startPos);
                            
                            // Try to fix common JSON issues
                            $jsonPart = preg_replace('/([{,]\s*)(\w+)(\s*:)/', '$1"$2"$3', $jsonPart); // Add quotes to keys
                            $jsonPart = preg_replace('/:\s*([^",{\[\]}\s]+)(?=\s*[,}])/', ': "$1"', $jsonPart); // Add quotes to simple values
                            
                            $testDecode = json_decode($jsonPart, true);
                            if ($testDecode !== null) {
                                $fixedAnswer = json_encode($testDecode, JSON_UNESCAPED_UNICODE);
                            }
                        }
                    }
                    
                    $escapedAnswer = addslashes($fixedAnswer);
                    $fixedLines[] = $answerIndent . '"answer": "' . $escapedAnswer . '"' . (isset($matches[2]) ? $matches[2] : '');
                    $insideAnswer = false;
                    $answerBuffer = '';
                } else {
                    $answerBuffer .= "\n" . $line;
                }
            } else {
                $fixedLines[] = $line;
            }
        }
        
        $fixedContent = implode("\n", $fixedLines);
    }
    
    // Try to decode the fixed content
    $data = json_decode($fixedContent, true);
    
    if ($data !== null) {
        echo "<!-- ✅ JSON fixed automatically! -->";
        
        // Save the fixed version
        $backupFile = $jsonFile . '.backup.' . date('Y-m-d-H-i-s');
        file_put_contents($backupFile, $jsonContent); // Save original as backup
        file_put_contents($jsonFile, $fixedContent); // Save fixed version
        
        echo "<!-- Original backed up to: $backupFile -->";
        echo "<!-- Fixed JSON saved successfully -->";
    } else {
        // Still couldn't fix it - show detailed error
        $error = json_last_error_msg();
        
        echo "<div style='background: #ffebee; border: 2px solid #f44336; padding: 20px; margin: 20px; border-radius: 8px;'>";
        echo "<h2 style='color: #d32f2f; margin: 0 0 10px 0;'>❌ Could Not Auto-Fix JSON</h2>";
        echo "<p><strong>Error:</strong> " . htmlspecialchars($error) . "</p>";
        
        echo "<h3>🔧 Automatic Fix Tool:</h3>";
        echo "<p>Click the button below to automatically fix your JSON:</p>";
        
        echo "<div style='background: #fff3e0; border: 2px solid #ff9800; padding: 15px; margin: 15px 0; border-radius: 8px;'>";
        echo "<h4>⚡ One-Click Fix</h4>";
        echo "<button id='fixJsonBtn' style='background: #ff9800; color: white; border: none; padding: 12px 24px; border-radius: 4px; cursor: pointer; font-size: 14px; margin-right: 10px;'>🔧 Fix JSON Now</button>";
        echo "<button id='downloadBtn' style='background: #4caf50; color: white; border: none; padding: 12px 24px; border-radius: 4px; cursor: pointer; font-size: 14px; display: none;'>📥 Download Fixed JSON</button>";
        echo "</div>";
        
        echo "<div id='fixResult' style='margin-top: 15px;'></div>";
        
        // Show complete JSON content for manual inspection
        echo "<div style='background: #f5f5f5; border: 2px solid #ccc; padding: 15px; margin: 15px 0; border-radius: 8px;'>";
        echo "<h4>📄 Complete JSON Content:</h4>";
        echo "<p><strong>File Size:</strong> " . number_format(strlen($jsonContent)) . " characters</p>";
        echo "<textarea id='originalJson' style='width: 100%; height: 400px; font-family: monospace; font-size: 10px; border: 1px solid #ccc; border-radius: 4px; padding: 10px;' readonly>";
        echo htmlspecialchars($jsonContent);
        echo "</textarea>";
        echo "<div style='margin-top: 10px;'>";
        echo "<button onclick='copyOriginalJson()' style='background: #2196f3; color: white; border: none; padding: 8px 16px; border-radius: 4px; cursor: pointer; margin-right: 10px;'>📋 Copy Original JSON</button>";
        echo "<button onclick='selectAllOriginal()' style='background: #666; color: white; border: none; padding: 8px 16px; border-radius: 4px; cursor: pointer;'>🔍 Select All</button>";
        echo "</div>";
        echo "</div>";
        
        echo "<h3>Manual Fix Required:</h3>";
        echo "<p>The issue is with nested JSON in the 'answer' fields. Here's a corrected sample:</p>";
        
        echo "<h4>❌ Current Format (Wrong):</h4>";
        echo "<pre style='background: #ffebee; padding: 10px; border-radius: 4px;'>";
        echo '"answer": "{"time":1746801489211,"blocks":[...]}"';
        echo "</pre>";
        
        echo "<h4>✅ Correct Format:</h4>";
        echo "<pre style='background: #e8f5e8; padding: 10px; border-radius: 4px;'>";
        echo '"answer": "{\"time\":1746801489211,\"blocks\":[...]}"';
        echo "</pre>";
        
        echo "<p><strong>Solution:</strong> Replace all double quotes inside answer strings with \\\"</p>";
        
        // JavaScript for fixing
        echo "<script>";
        echo "document.addEventListener('DOMContentLoaded', function() {";
        echo "    const originalJsonContent = " . json_encode($jsonContent) . ";";
        echo "    let fixedJsonContent = '';";
        echo "";
        echo "    document.getElementById('fixJsonBtn').addEventListener('click', function() {";
        echo "        const resultDiv = document.getElementById('fixResult');";
        echo "        const btn = this;";
        echo "        ";
        echo "        btn.disabled = true;";
        echo "        btn.innerHTML = '🔄 Processing...';";
        echo "        ";
        echo "        resultDiv.innerHTML = '<div style=\"background: #e3f2fd; padding: 10px; border-radius: 4px;\">🔄 Fixing JSON...</div>';";
        echo "        ";
        echo "        setTimeout(function() {";
        echo "            try {";
        echo "                let content = originalJsonContent;";
        echo "                ";
        echo "                // Method 1: Simple find and replace approach";
        echo "                // Find answer fields and escape quotes within them";
        echo "                const lines = content.split('\\n');";
        echo "                const fixedLines = [];";
        echo "                let insideAnswer = false;";
        echo "                let answerContent = '';";
        echo "                let answerPrefix = '';";
        echo "                ";
        echo "                for (let i = 0; i < lines.length; i++) {";
        echo "                    let line = lines[i];";
        echo "                    ";
        echo "                    // Check if line contains start of answer field";
        echo "                    const answerStartMatch = line.match(/^(\\s*\"answer\":\\s*\")(.*)$/);";
        echo "                    if (answerStartMatch && !insideAnswer) {";
        echo "                        answerPrefix = answerStartMatch[1];";
        echo "                        let answerPart = answerStartMatch[2];";
        echo "                        ";
        echo "                        // Check if answer ends on same line";
        echo "                        const answerEndMatch = answerPart.match(/^(.*)\"(\\s*[,}]?\\s*)$/);";
        echo "                        if (answerEndMatch) {";
        echo "                            // Single line answer";
        echo "                            let content = answerEndMatch[1];";
        echo "                            let ending = answerEndMatch[2];";
        echo "                            // Escape quotes in content";
        echo "                            content = content.replace(/\"/g, '\\\\\"');";
        echo "                            fixedLines.push(answerPrefix + content + '\"' + ending);";
        echo "                        } else {";
        echo "                            // Multi-line answer starts";
        echo "                            insideAnswer = true;";
        echo "                            answerContent = answerPart;";
        echo "                        }";
        echo "                    } else if (insideAnswer) {";
        echo "                        // Inside multi-line answer";
        echo "                        const answerEndMatch = line.match(/^(.*)\"(\\s*[,}]?\\s*)$/);";
        echo "                        if (answerEndMatch) {";
        echo "                            // End of answer";
        echo "                            answerContent += '\\n' + answerEndMatch[1];";
        echo "                            let ending = answerEndMatch[2];";
        echo "                            // Escape quotes in entire answer content";
        echo "                            answerContent = answerContent.replace(/\"/g, '\\\\\"');";
        echo "                            fixedLines.push(answerPrefix + answerContent + '\"' + ending);";
        echo "                            insideAnswer = false;";
        echo "                            answerContent = '';";
        echo "                        } else {";
        echo "                            // Continue collecting answer content";
        echo "                            answerContent += '\\n' + line;";
        echo "                        }";
        echo "                    } else {";
        echo "                        // Regular line";
        echo "                        fixedLines.push(line);";
        echo "                    }";
        echo "                }";
        echo "                ";
        echo "                const fixedContent = fixedLines.join('\\n');";
        echo "                ";
        echo "                // Test if the fixed JSON is valid";
        echo "                try {";
        echo "                    JSON.parse(fixedContent);";
        echo "                    ";
        echo "                    // Success!";
        echo "                    fixedJsonContent = fixedContent;";
        echo "                    ";
        echo "                    resultDiv.innerHTML = `";
        echo "                        <div style='background: #e8f5e8; border: 2px solid #4caf50; padding: 15px; border-radius: 4px;'>";
        echo "                            <h4 style='color: #2e7d32; margin: 0 0 10px 0;'>✅ JSON Fixed Successfully!</h4>";
        echo "                            <p>Your JSON has been automatically fixed. Follow these steps:</p>";
        echo "                            <ol>";
        echo "                                <li><strong>Copy the fixed JSON below</strong></li>";
        echo "                                <li><strong>Replace your data.json file content</strong></li>";
        echo "                                <li><strong>Refresh this page</strong></li>";
        echo "                            </ol>";
        echo "                            <textarea id='fixedJsonTextarea' style='width: 100%; height: 300px; font-family: monospace; font-size: 11px; margin: 10px 0; border: 2px solid #4caf50; border-radius: 4px; padding: 10px;'>\${fixedContent}</textarea>";
        echo "                            <div style='margin-top: 10px;'>";
        echo "                                <button onclick='copyToClipboard()' style='background: #4caf50; color: white; border: none; padding: 10px 20px; border-radius: 4px; cursor: pointer; margin-right: 10px; font-size: 14px;'>📋 Copy Fixed JSON</button>";
        echo "                                <button onclick='downloadFixed()' style='background: #2196f3; color: white; border: none; padding: 10px 20px; border-radius: 4px; cursor: pointer; font-size: 14px;'>💾 Download Fixed JSON</button>";
        echo "                            </div>";
        echo "                        </div>";
        echo "                    `;";
        echo "                    ";
        echo "                    document.getElementById('downloadBtn').style.display = 'inline-block';";
        echo "                    ";
        echo "                } catch (parseError) {";
        echo "                    // Still invalid";
        echo "                    resultDiv.innerHTML = `";
        echo "                        <div style='background: #ffebee; border: 2px solid #f44336; padding: 15px; border-radius: 4px;'>";
        echo "                            <h4 style='color: #d32f2f; margin: 0 0 10px 0;'>❌ Automatic Fix Failed</h4>";
        echo "                            <p><strong>Parse Error:</strong> \${parseError.message}</p>";
        echo "                            <p>Please try the manual fix method below or contact support.</p>";
        echo "                            <details style='margin-top: 10px;'>";
        echo "                                <summary>Show attempted fix (for debugging)</summary>";
        echo "                                <textarea style='width: 100%; height: 200px; font-family: monospace; font-size: 10px; margin-top: 10px;'>\${fixedContent}</textarea>";
        echo "                            </details>";
        echo "                        </div>";
        echo "                    `;";
        echo "                }";
        echo "                ";
        echo "            } catch (error) {";
        echo "                resultDiv.innerHTML = `";
        echo "                    <div style='background: #ffebee; border: 2px solid #f44336; padding: 15px; border-radius: 4px;'>";
        echo "                        <h4 style='color: #d32f2f; margin: 0 0 10px 0;'>❌ Processing Error</h4>";
        echo "                        <p><strong>Error:</strong> \${error.message}</p>";
        echo "                    </div>";
        echo "                `;";
        echo "            }";
        echo "            ";
        echo "            btn.disabled = false;";
        echo "            btn.innerHTML = '🔧 Fix JSON Now';";
        echo "        }, 500);";
        echo "    });";
        echo "    window.copyOriginalJson = function() {";
        echo "        const textarea = document.getElementById('originalJson');";
        echo "        textarea.select();";
        echo "        textarea.setSelectionRange(0, 99999);";
        echo "        ";
        echo "        try {";
        echo "            document.execCommand('copy');";
        echo "            alert('✅ Original JSON copied to clipboard!');";
        echo "        } catch (err) {";
        echo "            alert('❌ Could not copy automatically. Please select all text and copy manually.');";
        echo "        }";
        echo "    };";
        echo "    ";
        echo "    window.selectAllOriginal = function() {";
        echo "        const textarea = document.getElementById('originalJson');";
        echo "        textarea.focus();";
        echo "        textarea.select();";
        echo "        textarea.setSelectionRange(0, textarea.value.length);";
        echo "    };";
        echo "    ";
        echo "    // Helper functions";
        echo "    window.copyToClipboard = function() {";
        echo "        const textarea = document.getElementById('fixedJsonTextarea');";
        echo "        textarea.select();";
        echo "        textarea.setSelectionRange(0, 99999);";
        echo "        ";
        echo "        try {";
        echo "            document.execCommand('copy');";
        echo "            alert('✅ Fixed JSON copied to clipboard!\\n\\nNow:\\n1. Open your data.json file\\n2. Select all content (Ctrl+A)\\n3. Paste (Ctrl+V)\\n4. Save the file\\n5. Refresh this page');";
        echo "        } catch (err) {";
        echo "            alert('❌ Could not copy automatically. Please select all text and copy manually.');";
        echo "        }";
        echo "    };";
        echo "    ";
        echo "    window.downloadFixed = function() {";
        echo "        const content = fixedJsonContent;";
        echo "        const blob = new Blob([content], { type: 'application/json' });";
        echo "        const url = URL.createObjectURL(blob);";
        echo "        const a = document.createElement('a');";
        echo "        a.href = url;";
        echo "        a.download = 'data-fixed.json';";
        echo "        document.body.appendChild(a);";
        echo "        a.click();";
        echo "        document.body.removeChild(a);";
        echo "        URL.revokeObjectURL(url);";
        echo "        alert('✅ Fixed JSON downloaded as data-fixed.json\\n\\nNow:\\n1. Replace your data.json with data-fixed.json\\n2. Refresh this page');";
        echo "    };";
        echo "});";
        echo "</script>";
        echo "</div>";
        
        // Create a working sample
        $sampleData = [
            'book_id' => 1150,
            'title' => 'Fixed Sample Book',
            'chapters' => [
                [
                    'id' => 10706,
                    'title' => 'Januar',
                    'questions' => [
                        [
                            'id' => 46096,
                            'title' => 'Wie stellt ihr euch das neue Jahr vor?',
                            'answers' => [
                                [
                                    'id' => 10522,
                                    'user_id' => 571,
                                    'author_first_name' => 'Mats',
                                    'author_last_name' => 'Johanson',
                                    'answer' => json_encode([
                                        'time' => 1746801489211,
                                        'blocks' => [
                                            [
                                                'id' => 'CmHeMJX3AR',
                                                'type' => 'header',
                                                'data' => [
                                                    'text' => 'Ein neues Jahr voller Möglichkeiten',
                                                    'level' => 2
                                                ]
                                            ],
                                            [
                                                'id' => 'xhkz6QnIc5',
                                                'type' => 'paragraph',
                                                'data' => [
                                                    'text' => 'Der Januar öffnete für unsere Familie die Tür zu einem neuen Jahr...'
                                                ]
                                            ]
                                        ]
                                    ])
                                ]
                            ]
                        ]
                    ]
                ]
            ]
        ];
        
        echo "<div style='background: #e8f5e8; border: 2px solid #4caf50; padding: 20px; margin: 20px; border-radius: 8px;'>";
        echo "<h3 style='color: #2e7d32;'>✅ Working Sample (copy this structure):</h3>";
        echo "<textarea style='width: 100%; height: 300px; font-family: monospace; font-size: 12px;'>";
        echo json_encode($sampleData, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
        echo "</textarea>";
        echo "<button onclick='navigator.clipboard.writeText(this.previousElementSibling.value)' style='background: #4caf50; color: white; border: none; padding: 10px 20px; border-radius: 4px; cursor: pointer; margin-top: 10px;'>Copy to Clipboard</button>";
        echo "</div>";
        
        die();
    }
}

// If we reach here, JSON is valid
echo "<!-- ✅ JSON loaded successfully -->";
echo "<!-- Debug Info: 
File Path: " . realpath($jsonFile) . "
File Size: " . filesize($jsonFile) . " bytes
Chapters Count: " . (isset($data['chapters']) ? count($data['chapters']) : 'No chapters found') . "
-->";
?>

<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Google Docs Style - Book Editor</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Google+Sans:wght@300;400;500;600&family=Roboto:wght@300;400;500&display=swap" rel="stylesheet">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Google Sans', 'Roboto', sans-serif;
            background: #f9fbfd;
            height: 100vh;
            display: flex;
            flex-direction: column;
            overflow: hidden;
        }

        /* Google Docs Header */
        .header {
            background: #fff;
            border-bottom: 1px solid #e8eaed;
            height: 64px;
            display: flex;
            align-items: center;
            padding: 0 24px;
            position: relative;
            z-index: 1000;
            box-shadow: 0 1px 3px rgba(60,64,67,.1);
        }

        .header-left {
            display: flex;
            align-items: center;
            gap: 16px;
        }

        .docs-icon {
            width: 40px;
            height: 40px;
            background: #4285f4;
            border-radius: 6px;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-weight: 500;
            font-size: 18px;
        }

        .document-name {
            font-size: 18px;
            color: #3c4043;
            font-weight: 400;
            border: none;
            background: transparent;
            outline: none;
            padding: 6px 8px;
            border-radius: 4px;
            min-width: 200px;
        }

        .document-name:hover {
            background: #f8f9fa;
        }

        .header-right {
            margin-left: auto;
            display: flex;
            align-items: center;
            gap: 16px;
        }

        .status-indicator {
            font-size: 13px;
            color: #5f6368;
            padding: 4px 8px;
            background: #f8f9fa;
            border-radius: 4px;
            border: 1px solid #dadce0;
        }

        .status-saving { color: #1a73e8; background: #e8f0fe; }
        .status-saved { color: #137333; background: #e6f4ea; }
        .status-error { color: #d93025; background: #fce8e6; }

        .share-btn {
            background: #1a73e8;
            color: white;
            border: none;
            padding: 10px 24px;
            border-radius: 4px;
            cursor: pointer;
            font-size: 14px;
            font-weight: 500;
            transition: background 0.2s;
        }

        .share-btn:hover {
            background: #1557b8;
        }

        /* Google Docs Toolbar */
        .toolbar-container {
            background: white;
            border-bottom: 1px solid #e8eaed;
            position: relative;
        }

        .toolbar {
            display: flex;
            align-items: center;
            padding: 8px 24px;
            gap: 2px;
            overflow-x: auto;
            scrollbar-width: none;
            -ms-overflow-style: none;
        }

        .toolbar::-webkit-scrollbar {
            display: none;
        }

        .toolbar-section {
            display: flex;
            align-items: center;
            gap: 2px;
            padding: 0 6px;
            border-right: 1px solid #e8eaed;
            margin-right: 8px;
        }

        .toolbar-section:last-child {
            border-right: none;
            margin-right: 0;
        }

        .toolbar-btn {
            min-width: 28px;
            height: 28px;
            border: none;
            background: transparent;
            border-radius: 4px;
            cursor: pointer;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 14px;
            color: #3c4043;
            position: relative;
            transition: background 0.2s;
        }

        .toolbar-btn:hover {
            background: #f8f9fa;
        }

        .toolbar-btn.active {
            background: #e8f0fe;
            color: #1a73e8;
        }

        .toolbar-select {
            border: none;
            background: transparent;
            padding: 4px 6px;
            border-radius: 4px;
            font-size: 14px;
            color: #3c4043;
            cursor: pointer;
            min-width: 80px;
        }

        .toolbar-select:hover {
            background: #f8f9fa;
        }

        .font-family-select {
            min-width: 140px;
        }

        .font-size-select {
            min-width: 60px;
        }

        /* Color picker */
        .color-picker {
            width: 20px;
            height: 20px;
            border: none;
            border-radius: 2px;
            cursor: pointer;
        }

        /* Main Layout */
        .main-layout {
            flex: 1;
            display: flex;
            overflow: hidden;
        }

        /* Sidebar */
        .sidebar {
            width: 280px;
            background: white;
            border-right: 1px solid #e8eaed;
            display: flex;
            flex-direction: column;
            overflow: hidden;
        }

        .sidebar-header {
            padding: 16px 20px;
            border-bottom: 1px solid #e8eaed;
            background: #f8f9fa;
        }

        .sidebar-title {
            font-size: 16px;
            font-weight: 500;
            color: #3c4043;
            margin-bottom: 12px;
        }

        .search-input {
            width: 100%;
            padding: 8px 12px;
            border: 1px solid #dadce0;
            border-radius: 8px;
            font-size: 14px;
            background: white;
            outline: none;
        }

        .search-input:focus {
            border-color: #1a73e8;
            box-shadow: 0 0 0 2px rgba(26,115,232,.2);
        }

        .questions-container {
            flex: 1;
            overflow-y: auto;
            padding: 8px;
        }

        .question-item {
            padding: 12px 16px;
            border-radius: 8px;
            cursor: pointer;
            margin-bottom: 4px;
            transition: all 0.2s;
            border: 1px solid transparent;
        }

        .question-item:hover {
            background: #f8f9fa;
        }

        .question-item.active {
            background: #e8f0fe;
            border-color: #1a73e8;
        }

        .question-chapter {
            font-size: 12px;
            color: #5f6368;
            font-weight: 500;
            margin-bottom: 4px;
        }

        .question-title {
            font-size: 14px;
            color: #3c4043;
            line-height: 1.4;
        }

        /* Editor Area */
        .editor-area {
            flex: 1;
            display: flex;
            justify-content: center;
            background: #f9fbfd;
            overflow-y: auto;
            padding: 24px;
        }

        .document-wrapper {
            width: 100%;
            max-width: 816px; /* 8.5 inches at 96 DPI */
        }

        /* Document Pages with Auto Page Break */
        .document-page {
            background: white;
            width: 816px; /* 8.5" */
            min-height: 1056px; /* 11" */
            max-height: 1056px; /* Force page break */
            margin: 0 auto 24px;
            padding: 96px; /* 1" margins */
            box-shadow: 0 2px 10px rgba(0,0,0,.1);
            border-radius: 2px;
            position: relative;
            overflow: hidden;
            page-break-after: always;
        }

        .document-page:last-child {
            page-break-after: avoid;
        }

        .page-content {
            min-height: 600px;
            max-height: 864px; /* Remaining height after header */
            font-family: 'Times New Roman', serif;
            font-size: 12pt;
            line-height: 1.15;
            color: #000;
            outline: none;
            word-wrap: break-word;
            overflow: visible;
        }

        /* Page Break Indicator */
        .page-break-line {
            width: 100%;
            height: 2px;
            background: linear-gradient(90deg, #1a73e8 50%, transparent 50%);
            background-size: 20px 2px;
            margin: 20px 0;
            position: relative;
            cursor: pointer;
        }

        .page-break-line::after {
            content: "Page Break";
            position: absolute;
            right: 0;
            top: -20px;
            font-size: 10px;
            color: #1a73e8;
            background: white;
            padding: 2px 6px;
            border: 1px solid #1a73e8;
            border-radius: 4px;
        }

        .page-header {
            margin-bottom: 24px;
            border-bottom: 1px solid #e8eaed;
            padding-bottom: 16px;
        }

        .question-title-input {
            width: 100%;
            font-size: 24px;
            font-weight: 400;
            color: #3c4043;
            border: none;
            outline: none;
            background: transparent;
            font-family: 'Google Sans', sans-serif;
            padding: 8px 0;
        }

        .question-title-input::placeholder {
            color: #9aa0a6;
        }

        .page-content {
            min-height: 600px;
            font-family: 'Times New Roman', serif;
            font-size: 12pt;
            line-height: 1.15;
            color: #000;
            outline: none;
            word-wrap: break-word;
        }

        .page-content:empty::before {
            content: 'Start writing your answer here...';
            color: #9aa0a6;
            font-style: italic;
        }

        .page-number {
            position: absolute;
            bottom: 24px;
            right: 32px;
            font-size: 10px;
            color: #5f6368;
        }

        /* Content Formatting */
        .page-content h1, .page-content h2, .page-content h3, 
        .page-content h4, .page-content h5, .page-content h6 {
            margin: 16px 0 8px 0;
            font-weight: bold;
        }

        .page-content h1 { font-size: 20pt; }
        .page-content h2 { font-size: 16pt; }
        .page-content h3 { font-size: 14pt; }
        .page-content h4 { font-size: 12pt; }
        .page-content h5 { font-size: 11pt; }
        .page-content h6 { font-size: 10pt; }

        .page-content p {
            margin: 0 0 12px 0;
        }

        .page-content ul, .page-content ol {
            margin: 12px 0;
            padding-left: 36px;
        }

        .page-content li {
            margin-bottom: 6px;
        }

        .page-content blockquote {
            margin: 12px 0;
            padding-left: 24px;
            border-left: 4px solid #dadce0;
            color: #5f6368;
            font-style: italic;
        }

        .page-content img {
            max-width: 100%;
            height: auto;
            margin: 12px 0;
            border-radius: 4px;
            box-shadow: 0 2px 8px rgba(0,0,0,.1);
        }

        .page-content table {
            width: 100%;
            border-collapse: collapse;
            margin: 12px 0;
        }

        .page-content th, .page-content td {
            border: 1px solid #dadce0;
            padding: 8px 12px;
            text-align: left;
        }

        .page-content th {
            background: #f8f9fa;
            font-weight: 500;
        }

        /* Loading States */
        .loading {
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 40px;
            color: #5f6368;
            font-style: italic;
        }

        .spinner {
            width: 20px;
            height: 20px;
            border: 2px solid #f3f3f3;
            border-top: 2px solid #1a73e8;
            border-radius: 50%;
            animation: spin 1s linear infinite;
            margin-right: 12px;
        }

        @keyframes spin {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
        }

        /* Export PDF Button */
        .export-pdf-btn {
            background: #137333;
            margin-left: 8px;
        }

        .export-pdf-btn:hover {
            background: #0f5132;
        }

        /* Print Styles */
        @media print {
        * {
            -webkit-print-color-adjust: exact !important;
            color-adjust: exact !important;
        }
        
        body {
            background: white !important;
            margin: 0;
            padding: 0;
            font-size: 12pt;
            line-height: 1.15;
        }
        
        .header, .toolbar-container, .sidebar {
            display: none !important;
        }
        
        .main-layout {
            display: block !important;
            height: auto !important;
            overflow: visible !important;
        }
        
        .editor-area {
            padding: 0 !important;
            background: white !important;
            overflow: visible !important;
            display: block !important;
        }
        
        .document-wrapper {
            width: 100% !important;
            max-width: none !important;
        }
        
        .document-page {
            width: 8.5in !important;
            height: 11in !important;
            margin: 0 !important;
            padding: 1in !important;
            box-shadow: none !important;
            border: none !important;
            border-radius: 0 !important;
            background: white !important;
            page-break-after: always !important;
            page-break-inside: avoid !important;
            display: block !important;
            position: relative !important;
            overflow: hidden !important;
        }
        
        .document-page:last-child {
            page-break-after: auto !important;
        }
        
        .page-content {
            height: 9in !important;
            max-height: 9in !important;
            min-height: auto !important;
            overflow: hidden !important;
            font-family: 'Times New Roman', serif !important;
            font-size: 12pt !important;
            line-height: 1.15 !important;
            color: black !important;
        }
        
        .page-break-line, .page-number {
            display: none !important;
        }
    }

        /* Responsive Design */
        @media (max-width: 768px) {
            .sidebar {
                width: 240px;
            }
            
            .document-page {
                width: 100%;
                padding: 48px 24px;
                margin: 0 0 16px;
            }
            
            .editor-area {
                padding: 12px;
            }
            
            .toolbar {
                padding: 8px 12px;
            }
        }

        /* Custom Scrollbar */
        ::-webkit-scrollbar {
            width: 8px;
            height: 8px;
        }

        ::-webkit-scrollbar-track {
            background: #f1f1f1;
        }

        ::-webkit-scrollbar-thumb {
            background: #c1c1c1;
            border-radius: 4px;
        }

        ::-webkit-scrollbar-thumb:hover {
            background: #a8a8a8;
        }
    </style>
</head>
<body>
    <!-- Google Docs Header -->
    <div class="header">
        <div class="header-left">
            <div class="docs-icon">📖</div>
            <input type="text" class="document-name" id="documentName" value="Book Editor" readonly>
        </div>
        <div class="header-right">
            <div class="status-indicator status-saved" id="statusIndicator">
                <span id="statusText">All changes saved</span>
            </div>
            <button class="share-btn" onclick="saveAllChanges()">Save All</button>
        </div>
        <div style="padding: 5px;">
            <button class="share-btn export-pdf-btn" onclick="exportToPDF()">
                <span>📄</span> Export PDF
            </button>
        </div>
    </div>

    <!-- Google Docs Toolbar -->
    <div class="toolbar-container">
        <div class="toolbar">
            <!-- Undo/Redo -->
            <div class="toolbar-section">
                <button class="toolbar-btn" onclick="execCommand('undo')" title="Undo (Ctrl+Z)">↶</button>
                <button class="toolbar-btn" onclick="execCommand('redo')" title="Redo (Ctrl+Y)">↷</button>
            </div>

            <!-- Print -->
            <div class="toolbar-section">
                <button class="toolbar-btn" onclick="printDocument()" title="Print (Ctrl+P)">🖨️</button>
            </div>

            <!-- Zoom -->
            <div class="toolbar-section">
                <select class="toolbar-select" onchange="changeZoom(this.value)">
                    <option value="100">100%</option>
                    <option value="75">75%</option>
                    <option value="90">90%</option>
                    <option value="125">125%</option>
                    <option value="150">150%</option>
                </select>
            </div>

            <!-- Font Family -->
            <div class="toolbar-section">
                <select class="toolbar-select font-family-select" onchange="changeFontFamily(this.value)">
                    <option value="Times New Roman">Times New Roman</option>
                    <option value="Arial">Arial</option>
                    <option value="Calibri">Calibri</option>
                    <option value="Georgia">Georgia</option>
                    <option value="Helvetica">Helvetica</option>
                    <option value="Comic Sans MS">Comic Sans MS</option>
                    <option value="Impact">Impact</option>
                    <option value="Trebuchet MS">Trebuchet MS</option>
                    <option value="Verdana">Verdana</option>
                </select>
            </div>

            <!-- Font Size -->
            <div class="toolbar-section">
                <select class="toolbar-select font-size-select" onchange="changeFontSize(this.value)">
                    <option value="8">8</option>
                    <option value="9">9</option>
                    <option value="10">10</option>
                    <option value="11">11</option>
                    <option value="12" selected>12</option>
                    <option value="14">14</option>
                    <option value="16">16</option>
                    <option value="18">18</option>
                    <option value="20">20</option>
                    <option value="24">24</option>
                    <option value="28">28</option>
                    <option value="32">32</option>
                    <option value="36">36</option>
                </select>
            </div>

            <!-- Text Formatting -->
            <div class="toolbar-section">
                <button class="toolbar-btn" id="boldBtn" onclick="toggleFormat('bold')" title="Bold (Ctrl+B)"><strong>B</strong></button>
                <button class="toolbar-btn" id="italicBtn" onclick="toggleFormat('italic')" title="Italic (Ctrl+I)"><em>I</em></button>
                <button class="toolbar-btn" id="underlineBtn" onclick="toggleFormat('underline')" title="Underline (Ctrl+U)"><u>U</u></button>
            </div>

            <!-- Text Color -->
            <div class="toolbar-section">
                <button class="toolbar-btn" onclick="showColorPicker('textColor')" title="Text color">A</button>
                <button class="toolbar-btn" onclick="showColorPicker('backgroundColor')" title="Highlight color">🎨</button>
            </div>

            <!-- Alignment -->
            <div class="toolbar-section">
                <button class="toolbar-btn" onclick="alignText('left')" title="Align left">⬅️</button>
                <button class="toolbar-btn" onclick="alignText('center')" title="Center">⬆️</button>
                <button class="toolbar-btn" onclick="alignText('right')" title="Align right">➡️</button>
                <button class="toolbar-btn" onclick="alignText('justify')" title="Justify">⬌</button>
            </div>

            <!-- Line Spacing -->
            <div class="toolbar-section">
                <select class="toolbar-select" onchange="changeLineHeight(this.value)">
                    <option value="1">Single</option>
                    <option value="1.15" selected>1.15</option>
                    <option value="1.5">1.5</option>
                    <option value="2">Double</option>
                </select>
            </div>

            <!-- Lists -->
            <div class="toolbar-section">
                <button class="toolbar-btn" onclick="insertList('ul')" title="Bullet list">•</button>
                <button class="toolbar-btn" onclick="insertList('ol')" title="Numbered list">1.</button>
            </div>

            <!-- Indentation -->
            <div class="toolbar-section">
                <button class="toolbar-btn" onclick="changeIndent('outdent')" title="Decrease indent">⬅</button>
                <button class="toolbar-btn" onclick="changeIndent('indent')" title="Increase indent">➡</button>
            </div>

            <!-- Insert -->
            <div class="toolbar-section">
                <button class="toolbar-btn" onclick="insertLink()" title="Insert link">🔗</button>
                <button class="toolbar-btn" onclick="insertImage()" title="Insert image">🖼️</button>
                <button class="toolbar-btn" onclick="insertTable()" title="Insert table">📊</button>
                <button class="toolbar-btn" onclick="insertPageBreak()" title="Insert page break (Shift+Ctrl+Enter)">📄</button>
            </div>

            <!-- Styles -->
            <div class="toolbar-section">
                <select class="toolbar-select" onchange="applyStyle(this.value)">
                    <option value="p">Normal text</option>
                    <option value="h1">Heading 1</option>
                    <option value="h2">Heading 2</option>
                    <option value="h3">Heading 3</option>
                    <option value="h4">Heading 4</option>
                    <option value="h5">Heading 5</option>
                    <option value="h6">Heading 6</option>
                    <option value="blockquote">Quote</option>
                </select>
            </div>
        </div>
    </div>

    <!-- Main Layout -->
    <div class="main-layout">
        <!-- Sidebar -->
        <div class="sidebar">
            <div class="sidebar-header">
                <div class="sidebar-title">Document Outline</div>
                <input type="text" class="search-input" id="searchInput" placeholder="Search questions...">
            </div>
            <div class="questions-container" id="questionsContainer">
                <div class="loading">
                    <div class="spinner"></div>
                    Loading questions...
                </div>
            </div>
        </div>

        <!-- Editor Area -->
        <div class="editor-area">
            <div class="document-wrapper" id="documentWrapper">
                <!-- Pages will be dynamically created here -->
                <div class="document-page" id="page-1">
                    <div class="page-header">
                        <input type="text" class="question-title-input" id="questionTitle" placeholder="Enter question title...">
                    </div>
                    <div class="page-content" id="pageContent-1" contenteditable="true">
                        <p>Select a question from the sidebar to start editing...</p>
                    </div>
                    <div class="page-number">Page 1</div>
                </div>
            </div>
        </div>
    </div>

    <!-- Color Picker Modal (Hidden) -->
    <div id="colorPickerModal" style="display: none;">
        <input type="color" id="colorPicker" style="position: absolute; visibility: hidden;">
    </div>

    <script>
        // Global Variables with Page Management
        let data = <?= json_encode($data, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) ?>;
        let currentChapter = -1;
        let currentQuestion = -1;
        let autoSaveTimer;
        let isLoading = false;
        let currentPageCount = 1;
        let pageBreakTimer;

        console.log('Loaded data:', data);
        console.log('Data structure check:', {
            hasData: !!data,
            hasChapters: !!(data && data.chapters),
            chaptersCount: data && data.chapters ? data.chapters.length : 0,
            firstChapter: data && data.chapters && data.chapters[0] ? data.chapters[0] : null
        });

        // Page height monitoring with better timing
        const MAX_PAGE_HEIGHT = 800; // Reduced for better page breaks
        const CONTENT_PADDING = 50; // Increased safety padding

        // Initialize Application
        document.addEventListener('DOMContentLoaded', function() {
            initializeApp();
        });

        function initializeApp() {
            console.log('🚀 Initializing application...');
            
            // Check if data loaded properly
            if (!data) {
                console.error('❌ No data loaded. Check your data.json file path and content.');
                updateStatus('Error: No data loaded', 'status-error');
                return;
            }
            
            console.log('✅ Data loaded successfully');
            
            loadSidebar();
            setupEventListeners();
            updateStatus('Ready to edit', 'status-saved');
            
            // Auto-load first question if available
            if (data.chapters && data.chapters.length > 0 && data.chapters[0].questions && data.chapters[0].questions.length > 0) {
                console.log('📝 Auto-loading first question...');
                setTimeout(() => {
                    loadQuestion(0, 0);
                }, 500);
            }
        }

        // Load Sidebar with Questions
        function loadSidebar() {
            const container = document.getElementById('questionsContainer');
            container.innerHTML = '';

            console.log('Loading sidebar. Data check:', {
                data: !!data,
                chapters: data ? data.chapters : null,
                chaptersLength: data && data.chapters ? data.chapters.length : 0
            });

            if (!data) {
                container.innerHTML = '<div class="loading">❌ No data loaded. Check your data.json file.</div>';
                return;
            }

            if (!data.chapters) {
                container.innerHTML = '<div class="loading">❌ No chapters found in data.</div>';
                return;
            }

            if (data.chapters.length === 0) {
                container.innerHTML = '<div class="loading">📝 No chapters available in your data.json file.</div>';
                return;
            }

            let totalQuestions = 0;
            
            data.chapters.forEach((chapter, chapterIndex) => {
                console.log(`Chapter ${chapterIndex}:`, {
                    title: chapter.title,
                    hasQuestions: !!chapter.questions,
                    questionsCount: chapter.questions ? chapter.questions.length : 0
                });

                if (chapter.questions && chapter.questions.length > 0) {
                    chapter.questions.forEach((question, questionIndex) => {
                        const questionItem = document.createElement('div');
                        questionItem.className = 'question-item';
                        questionItem.innerHTML = `
                            <div class="question-chapter">${chapter.title || `Chapter ${chapterIndex + 1}`}</div>
                            <div class="question-title">${question.title || `Question ${questionIndex + 1}`}</div>
                        `;
                        
                        questionItem.onclick = () => {
                            loadQuestion(chapterIndex, questionIndex);
                            
                            // Update active state
                            document.querySelectorAll('.question-item').forEach(item => item.classList.remove('active'));
                            questionItem.classList.add('active');
                        };
                        
                        container.appendChild(questionItem);
                        totalQuestions++;
                    });
                }
            });

            if (totalQuestions === 0) {
                container.innerHTML = '<div class="loading">📝 No questions found in chapters.</div>';
            } else {
                console.log(`Successfully loaded ${totalQuestions} questions from ${data.chapters.length} chapters`);
            }
        }

        // Load Question Content
        function loadQuestion(chapterIndex, questionIndex) {
            if (isLoading) return;
            
            console.log(`Loading question: Chapter ${chapterIndex}, Question ${questionIndex}`);
            
            // Validate indices
            if (!data.chapters || !data.chapters[chapterIndex] || !data.chapters[chapterIndex].questions || !data.chapters[chapterIndex].questions[questionIndex]) {
                console.error('Invalid question indices:', { chapterIndex, questionIndex });
                updateStatus('Error: Invalid question', 'status-error');
                return;
            }
            
            // Save current content before switching
            if (currentChapter >= 0 && currentQuestion >= 0) {
                saveCurrentContent();
            }

            isLoading = true;
            updateStatus('Loading...', 'status-saving');

            currentChapter = chapterIndex;
            currentQuestion = questionIndex;

            const question = data.chapters[chapterIndex].questions[questionIndex];
            
            console.log('Question data:', {
                title: question.title,
                hasAnswers: !!question.answers,
                answersCount: question.answers ? question.answers.length : 0,
                firstAnswer: question.answers && question.answers[0] ? question.answers[0] : null
            });
            
            // Load question title
            const titleInput = document.getElementById('questionTitle');
            if (titleInput) {
                titleInput.value = question.title || '';
            }

            // Load question content
            let content = '';
            if (question.answers && question.answers[0] && question.answers[0].answer) {
                try {
                    console.log('Raw answer data:', question.answers[0].answer);
                    const answerData = JSON.parse(question.answers[0].answer);
                    console.log('Parsed answer data:', answerData);
                    
                    if (answerData.blocks && Array.isArray(answerData.blocks)) {
                        content = convertBlocksToHTML(answerData.blocks);
                        console.log('Converted HTML content:', content);
                    } else {
                        console.log('No blocks found in answer data');
                        content = '<p>Start writing your answer here...</p>';
                    }
                } catch (error) {
                    console.error('Error parsing answer:', error);
                    console.log('Answer string:', question.answers[0].answer);
                    content = '<p>Error loading content. Start writing here...</p>';
                }
            } else {
                console.log('No answers found for this question');
                content = '<p>Start writing your answer here...</p>';
            }

            const pageContent = document.getElementById('pageContent-1');
            if (pageContent) {
                pageContent.innerHTML = content;
                console.log('Content loaded into editor');
                
                // Check for page overflow and create new pages if needed
                setTimeout(() => {
                    checkAndCreatePages();
                }, 100);
            } else {
                console.error('Page content element not found');
            }

            isLoading = false;
            updateStatus('Loaded successfully', 'status-saved');
        }

        // Convert Blocks to HTML
        function convertBlocksToHTML(blocks) {
            console.log('Converting blocks to HTML:', blocks);
            
            if (!blocks || !Array.isArray(blocks) || blocks.length === 0) {
                console.log('No blocks to convert');
                return '<p>Start writing your answer here...</p>';
            }

            const htmlParts = blocks.map((block, index) => {
                console.log(`Converting block ${index}:`, block);
                
                if (!block || !block.type) {
                    console.log(`Block ${index} has no type, skipping`);
                    return '';
                }

                switch (block.type) {
                    case 'paragraph':
                        const text = block.data && block.data.text ? block.data.text : '';
                        return `<p>${text}</p>`;
                        
                    case 'header':
                        const level = block.data && block.data.level ? block.data.level : 1;
                        const headerText = block.data && block.data.text ? block.data.text : '';
                        return `<h${level}>${headerText}</h${level}>`;
                        
                    case 'list':
                        if (!block.data || !block.data.items || !Array.isArray(block.data.items)) {
                            return '<ul><li>Empty list</li></ul>';
                        }
                        const listType = block.data.style === 'ordered' ? 'ol' : 'ul';
                        const items = block.data.items.map(item => `<li>${item}</li>`).join('');
                        return `<${listType}>${items}</${listType}>`;
                        
                    case 'image':
                        const imageUrl = block.data && block.data.file && block.data.file.url ? block.data.file.url : '';
                        if (imageUrl) {
                            const caption = block.data && block.data.caption ? block.data.caption : '';
                            return `<div style="text-align: center; margin: 15px 0;">
                                <img src="${imageUrl}" alt="${caption}" style="max-width: 100%; height: auto; border-radius: 4px; box-shadow: 0 2px 8px rgba(0,0,0,0.1);">
                                ${caption ? `<div style="font-size: 12px; color: #666; margin-top: 8px; font-style: italic;">${caption}</div>` : ''}
                            </div>`;
                        }
                        return '<div style="background: #f8f9fa; border: 2px dashed #dadce0; padding: 20px; margin: 12px 0; text-align: center; border-radius: 8px; color: #666;">📷 Image not found</div>';
                        
                    case 'twoImage':
                        if (!block.data || !block.data.images || !Array.isArray(block.data.images)) {
                            return '<div style="background: #f8f9fa; border: 2px dashed #dadce0; padding: 20px; margin: 12px 0; text-align: center; border-radius: 8px; color: #666;">📷 Two images not found</div>';
                        }
                        
                        let twoImageHtml = '<div style="display: flex; gap: 10px; margin: 15px 0; justify-content: space-between;">';
                        block.data.images.forEach(image => {
                            if (image && image.url) {
                                twoImageHtml += `<div style="flex: 1;"><img src="${image.url}" alt="" style="width: 100%; height: auto; border-radius: 4px; box-shadow: 0 2px 8px rgba(0,0,0,0.1);"></div>`;
                            }
                        });
                        twoImageHtml += '</div>';
                        return twoImageHtml;
                        
                    case 'audio':
                        const audioUrl = block.data && block.data.url ? block.data.url : '';
                        if (audioUrl) {
                            return `<div style="background: #e8f0fe; border: 2px solid #1a73e8; padding: 20px; margin: 12px 0; text-align: center; border-radius: 8px; color: #1a73e8;">
                                🎵 Audio: <a href="${audioUrl}" target="_blank" style="color: #1a73e8; text-decoration: none;">Play Audio</a>
                            </div>`;
                        }
                        return '<div style="background: #f8f9fa; border: 2px dashed #dadce0; padding: 20px; margin: 12px 0; text-align: center; border-radius: 8px; color: #666;">🎵 Audio Placeholder</div>';
                        
                    default:
                        console.log(`Unknown block type: ${block.type}`);
                        const defaultText = block.data && block.data.text ? block.data.text : `[${block.type}]`;
                        return `<p>${defaultText}</p>`;
                }
            });

            const result = htmlParts.filter(html => html).join('');
            console.log('Final HTML result:', result);
            
            return result || '<p>Start writing your answer here...</p>';
        }

        // Convert HTML to Blocks
        function convertHTMLToBlocks(html) {
            const tempDiv = document.createElement('div');
            tempDiv.innerHTML = html;
            
            const blocks = [];
            let blockId = 1;
            
            Array.from(tempDiv.children).forEach(element => {
                const tagName = element.tagName.toLowerCase();
                
                if (tagName.match(/^h[1-6]$/)) {
                    blocks.push({
                        id: `block_${blockId++}`,
                        type: 'header',
                        data: {
                            text: element.textContent,
                            level: parseInt(tagName.substring(1))
                        }
                    });
                } else if (tagName === 'p') {
                    blocks.push({
                        id: `block_${blockId++}`,
                        type: 'paragraph',
                        data: {
                            text: element.innerHTML
                        }
                    });
                } else if (tagName === 'ul' || tagName === 'ol') {
                    const items = Array.from(element.children).map(li => li.textContent);
                    blocks.push({
                        id: `block_${blockId++}`,
                        type: 'list',
                        data: {
                            style: tagName === 'ol' ? 'ordered' : 'unordered',
                            items: items
                        }
                    });
                } else if (tagName === 'img') {
                    blocks.push({
                        id: `block_${blockId++}`,
                        type: 'image',
                        data: {
                            file: { url: element.src },
                            caption: element.alt || ''
                        }
                    });
                }
            });
            
            return blocks;
        }

        // Page Management Functions
        function checkAndCreatePages() {
            console.log('🔄 Checking page overflow...');
            
            const wrapper = document.getElementById('documentWrapper');
            const firstPage = document.getElementById('page-1');
            const firstPageContent = document.getElementById('pageContent-1');
            
            if (!firstPageContent) return;
            
            const contentHeight = firstPageContent.scrollHeight;
            console.log(`📏 Content height: ${contentHeight}px, Max allowed: ${MAX_PAGE_HEIGHT}px`);
            
            if (contentHeight > MAX_PAGE_HEIGHT) {
                console.log('📄 Content overflow detected, creating new page...');
                splitContentAcrossPages(firstPageContent);
            }
        }

        function splitContentAcrossPages(contentElement) {
            const allElements = Array.from(contentElement.children);
            if (allElements.length <= 1) return;
            
            // Find split point
            let currentHeight = 0;
            let splitIndex = 0;
            
            for (let i = 0; i < allElements.length; i++) {
                const elementHeight = allElements[i].offsetHeight + 20; // Add margin
                if (currentHeight + elementHeight > MAX_PAGE_HEIGHT) {
                    splitIndex = i;
                    break;
                }
                currentHeight += elementHeight;
            }
            
            if (splitIndex === 0) splitIndex = Math.ceil(allElements.length / 2);
            
            // Create new page
            const newPageNumber = currentPageCount + 1;
            createNewPage(newPageNumber);
            
            // Move overflow content to new page
            const newPageContent = document.getElementById(`pageContent-${newPageNumber}`);
            const elementsToMove = allElements.slice(splitIndex);
            
            elementsToMove.forEach(element => {
                newPageContent.appendChild(element);
            });
            
            currentPageCount = newPageNumber;
            console.log(`✅ Created page ${newPageNumber}, moved ${elementsToMove.length} elements`);
            
            // Check if new page also overflows
            setTimeout(() => {
                const newContentHeight = newPageContent.scrollHeight;
                if (newContentHeight > MAX_PAGE_HEIGHT) {
                    splitContentAcrossPages(newPageContent);
                }
            }, 100);
        }

        function createNewPage(pageNumber) {
            const wrapper = document.getElementById('documentWrapper');
            
            const newPage = document.createElement('div');
            newPage.className = 'document-page';
            newPage.id = `page-${pageNumber}`;
            
            newPage.innerHTML = `
                <div class="page-content" id="pageContent-${pageNumber}" contenteditable="true">
                </div>
                <div class="page-number">Page ${pageNumber}</div>
            `;
            
            wrapper.appendChild(newPage);
            
            // Setup event listeners for new page
            setupPageEventListeners(pageNumber);
            
            console.log(`📄 Created new page: ${pageNumber}`);
        }

        function setupPageEventListeners(pageNumber) {
            const pageContent = document.getElementById(`pageContent-${pageNumber}`);
            if (!pageContent) return;
            
            // Input event for real-time monitoring
            pageContent.addEventListener('input', () => {
                updateStatus('Editing...', 'status-saving');
                clearTimeout(autoSaveTimer);
                clearTimeout(pageBreakTimer);
                
                // Check both overflow and underflow
                pageBreakTimer = setTimeout(() => {
                    checkPageOverflow(pageNumber);
                    checkPageUnderflow(pageNumber);
                }, 300);
                
                // Auto-save
                autoSaveTimer = setTimeout(() => {
                    saveCurrentContent();
                }, 2000);
            });
            
            // Keydown event for special key handling
            pageContent.addEventListener('keydown', (e) => {
                if (e.key === 'Enter') {
                    // Check after Enter key is processed
                    setTimeout(() => {
                        checkPageOverflow(pageNumber);
                    }, 100);
                } else if (e.key === 'Backspace') {
                    // Check for page merging after backspace
                    setTimeout(() => {
                        checkPageUnderflow(pageNumber);
                        mergePagesIfNeeded(pageNumber);
                    }, 100);
                } else if (e.key === 'Delete') {
                    // Check for page merging after delete
                    setTimeout(() => {
                        checkPageUnderflow(pageNumber);
                        mergePagesIfNeeded(pageNumber);
                    }, 100);
                }
            });
            
            // Paste event handling
            pageContent.addEventListener('paste', (e) => {
                setTimeout(() => {
                    checkPageOverflow(pageNumber);
                }, 200);
            });
            
            console.log(`🔧 Event listeners setup for page ${pageNumber}`);
        }

        // Real-time content monitoring (Enhanced)
        function monitorContentHeight() {
            for (let i = 1; i <= currentPageCount; i++) {
                checkPageOverflow(i);
                checkPageUnderflow(i);
            }
            
            // Clean up empty pages
            cleanupEmptyPages();
        }

        // Check if page has too little content and can be merged
        function checkPageUnderflow(pageNumber) {
            if (pageNumber <= 1) return; // Don't check first page
            
            const pageContent = document.getElementById(`pageContent-${pageNumber}`);
            if (!pageContent) return;
            
            const contentHeight = pageContent.scrollHeight;
            const MIN_PAGE_HEIGHT = 200; // Minimum content to justify a separate page
            
            console.log(`📉 Page ${pageNumber} underflow check: ${contentHeight}px (Min: ${MIN_PAGE_HEIGHT}px)`);
            
            if (contentHeight < MIN_PAGE_HEIGHT) {
                console.log(`🔄 Page ${pageNumber} has minimal content, checking for merge...`);
                mergePagesIfNeeded(pageNumber);
            }
        }

        // Merge current page with previous page if possible
        function mergePagesIfNeeded(pageNumber) {
            if (pageNumber <= 1 || pageNumber > currentPageCount) return;
            
            const currentPageContent = document.getElementById(`pageContent-${pageNumber}`);
            const previousPageContent = document.getElementById(`pageContent-${pageNumber - 1}`);
            
            if (!currentPageContent || !previousPageContent) return;
            
            const currentHeight = currentPageContent.scrollHeight;
            const previousHeight = previousPageContent.scrollHeight;
            const combinedHeight = currentHeight + previousHeight;
            
            console.log(`🔄 Merge check: Page ${pageNumber - 1} (${previousHeight}px) + Page ${pageNumber} (${currentHeight}px) = ${combinedHeight}px`);
            
            if (combinedHeight <= MAX_PAGE_HEIGHT) {
                console.log(`✅ Merging page ${pageNumber} into page ${pageNumber - 1}`);
                
                // Preserve cursor position
                const selection = window.getSelection();
                let cursorOffset = 0;
                let isInCurrentPage = false;
                
                if (selection.rangeCount > 0) {
                    const range = selection.getRangeAt(0);
                    if (currentPageContent.contains(range.commonAncestorContainer)) {
                        isInCurrentPage = true;
                        cursorOffset = range.startOffset;
                    }
                }
                
                // Move all content from current page to previous page (append at end)
                const elementsToMove = Array.from(currentPageContent.children);
                elementsToMove.forEach(element => {
                    previousPageContent.appendChild(element);
                });
                
                // Remove empty page
                const pageToRemove = document.getElementById(`page-${pageNumber}`);
                if (pageToRemove) {
                    pageToRemove.remove();
                }
                
                renumberPages(pageNumber);
                
                // Restore cursor position if it was in the moved content
                if (isInCurrentPage) {
                    setTimeout(() => {
                        const newRange = document.createRange();
                        const lastElement = previousPageContent.lastElementChild;
                        if (lastElement) {
                            newRange.setStart(lastElement, Math.min(cursorOffset, lastElement.childNodes.length));
                            newRange.collapse(true);
                            selection.removeAllRanges();
                            selection.addRange(newRange);
                            previousPageContent.focus();
                        }
                    }, 100);
                }
                
                console.log(`✅ Successfully merged pages`);
            }
        }

        // Renumber pages after deletion
        function renumberPages(deletedPageNumber) {
            const wrapper = document.getElementById('documentWrapper');
            const allPages = Array.from(wrapper.querySelectorAll('.document-page'));
            
            // Update page IDs and content IDs
            allPages.forEach((page, index) => {
                const newPageNumber = index + 1;
                page.id = `page-${newPageNumber}`;
                
                const pageContent = page.querySelector('.page-content');
                if (pageContent) {
                    pageContent.id = `pageContent-${newPageNumber}`;
                }
                
                const pageNumberElement = page.querySelector('.page-number');
                if (pageNumberElement) {
                    pageNumberElement.textContent = `Page ${newPageNumber}`;
                }
            });
            
            // Update current page count
            currentPageCount = allPages.length;
            console.log(`📄 Renumbered pages, new count: ${currentPageCount}`);
        }

        // Clean up completely empty pages
        function cleanupEmptyPages() {
            const wrapper = document.getElementById('documentWrapper');
            const allPages = Array.from(wrapper.querySelectorAll('.document-page'));
            
            // Don't remove if it's the only page
            if (allPages.length <= 1) return;
            
            let removedAny = false;
            
            // Check each page (except the first one)
            for (let i = allPages.length - 1; i >= 1; i--) {
                const page = allPages[i];
                const pageContent = page.querySelector('.page-content');
                
                if (pageContent) {
                    const isEmpty = pageContent.innerHTML.trim() === '' || 
                                   pageContent.innerHTML.trim() === '<br>' ||
                                   pageContent.innerHTML.trim() === '<p><br></p>';
                    
                    if (isEmpty) {
                        console.log(`🗑️ Removing empty page ${i + 1}`);
                        page.remove();
                        removedAny = true;
                    }
                }
            }
            
            if (removedAny) {
                renumberPages(0); // Renumber all pages
            }
        }

        // Check specific page for overflow
        function checkPageOverflow(pageNumber) {
            const pageContent = document.getElementById(`pageContent-${pageNumber}`);
            if (!pageContent) return;
            
            const contentHeight = pageContent.scrollHeight;
            console.log(`📏 Page ${pageNumber} height: ${contentHeight}px (Max: ${MAX_PAGE_HEIGHT}px)`);
            
            if (contentHeight > MAX_PAGE_HEIGHT) {
                console.log(`🚨 Page ${pageNumber} overflow detected!`);
                splitPageContent(pageNumber);
            }
        }

        // Split page content when overflow occurs
        /* function splitPageContent(pageNumber) {
            const currentPageContent = document.getElementById(`pageContent-${pageNumber}`);
            if (!currentPageContent || currentPageContent.children.length <= 1) {
                console.log(`Cannot split page ${pageNumber} - insufficient content`);
                return;
            }
            
            console.log(`🔄 Splitting page ${pageNumber}...`);
            
            // Get all content elements
            const allElements = Array.from(currentPageContent.children);
            let totalHeight = 0;
            let splitIndex = 0;
            
            // Find optimal split point
            for (let i = 0; i < allElements.length; i++) {
                const elementHeight = allElements[i].offsetHeight + 10; // Add margin
                if (totalHeight + elementHeight > MAX_PAGE_HEIGHT * 0.85) { // Leave some margin
                    splitIndex = Math.max(1, i); // Ensure at least one element stays
                    break;
                }
                totalHeight += elementHeight;
            }
            
            if (splitIndex === 0) {
                splitIndex = Math.ceil(allElements.length / 2);
            }
            
            // Create new page if it doesn't exist
            const nextPageNumber = pageNumber + 1;
            let nextPageContent = document.getElementById(`pageContent-${nextPageNumber}`);
            
            if (!nextPageContent) {
                createNewPage(nextPageNumber);
                nextPageContent = document.getElementById(`pageContent-${nextPageNumber}`);
            }
            
            // Move overflow elements to next page
            const elementsToMove = allElements.slice(splitIndex);
            const existingNextContent = Array.from(nextPageContent.children);
            
            // Clear next page content temporarily
            nextPageContent.innerHTML = '';
            
            // Add moved elements first
            elementsToMove.forEach(element => {
                nextPageContent.appendChild(element);
            });
            
            // Add back existing content
            existingNextContent.forEach(element => {
                nextPageContent.appendChild(element);
            });
            
            console.log(`✅ Moved ${elementsToMove.length} elements from page ${pageNumber} to page ${nextPageNumber}`);
            
            // Update page count
            currentPageCount = Math.max(currentPageCount, nextPageNumber);
            
            // Check if the new page also overflows
            setTimeout(() => {
                checkPageOverflow(nextPageNumber);
            }, 100);
        } */

        function splitPageContent(pageNumber) {
            const currentPageContent = document.getElementById(`pageContent-${pageNumber}`);
            if (!currentPageContent || currentPageContent.children.length <= 1) {
                console.log(`Cannot split page ${pageNumber} - insufficient content`);
                return;
            }
            
            console.log(`🔄 Splitting page ${pageNumber}...`);
            
            const allElements = Array.from(currentPageContent.children);
            let totalHeight = 0;
            let splitIndex = 0;
            
            // Find split point based on height
            for (let i = 0; i < allElements.length; i++) {
                const elementHeight = allElements[i].offsetHeight + 10;
                if (totalHeight + elementHeight > MAX_PAGE_HEIGHT * 0.85) {
                    splitIndex = Math.max(1, i);
                    break;
                }
                totalHeight += elementHeight;
            }
            
            if (splitIndex === 0) {
                splitIndex = Math.ceil(allElements.length / 2);
            }
            
            const nextPageNumber = pageNumber + 1;
            let nextPageContent = document.getElementById(`pageContent-${nextPageNumber}`);
            
            if (!nextPageContent) {
                createNewPage(nextPageNumber);
                nextPageContent = document.getElementById(`pageContent-${nextPageNumber}`);
            }
            
            // Store existing content from next page
            const existingNextContent = Array.from(nextPageContent.children);
            
            // Move elements from current page to next page
            const elementsToMove = allElements.slice(splitIndex);
            
            // Clear next page and add moved content first, then existing content
            nextPageContent.innerHTML = '';
            elementsToMove.forEach(element => {
                nextPageContent.appendChild(element);
            });
            existingNextContent.forEach(element => {
                nextPageContent.appendChild(element);
            });
            
            console.log(`✅ Moved ${elementsToMove.length} elements from page ${pageNumber} to page ${nextPageNumber}`);
            
            currentPageCount = Math.max(currentPageCount, nextPageNumber);
            
            setTimeout(() => {
                checkPageOverflow(nextPageNumber);
            }, 100);
        }

        /* function insertPageBreak() {
            const selection = window.getSelection();
            if (selection.rangeCount === 0) return;
            
            const range = selection.getRangeAt(0);
            const pageBreakDiv = document.createElement('div');
            pageBreakDiv.className = 'page-break-line';
            pageBreakDiv.contentEditable = false;
            
            range.insertNode(pageBreakDiv);
            range.setStartAfter(pageBreakDiv);
            range.collapse(true);
            selection.removeAllRanges();
            selection.addRange(range);
            
            // Force page check
            setTimeout(() => {
                checkAndCreatePages();
            }, 100);
        } */
        function insertPageBreak() {
            const selection = window.getSelection();
            if (selection.rangeCount === 0) return;
            
            const range = selection.getRangeAt(0);
            const currentPageContent = range.commonAncestorContainer.nodeType === 3 
                ? range.commonAncestorContainer.parentElement.closest('.page-content')
                : range.commonAncestorContainer.closest('.page-content');
            
            if (!currentPageContent) return;
            
            const currentPageNumber = parseInt(currentPageContent.id.split('-')[1]);
            
            // Create a temporary div to hold content after cursor
            const tempDiv = document.createElement('div');
            
            // Extract content after cursor position
            const rangeAfter = document.createRange();
            rangeAfter.setStart(range.startContainer, range.startOffset);
            rangeAfter.setEndAfter(currentPageContent.lastChild);
            
            const contentAfter = rangeAfter.extractContents();
            tempDiv.appendChild(contentAfter);
            
            // Create or get next page
            const nextPageNumber = currentPageNumber + 1;
            let nextPageContent = document.getElementById(`pageContent-${nextPageNumber}`);
            
            if (!nextPageContent) {
                createNewPage(nextPageNumber);
                nextPageContent = document.getElementById(`pageContent-${nextPageNumber}`);
            }
            
            // Move extracted content to next page (at the beginning)
            const existingContent = nextPageContent.innerHTML;
            nextPageContent.innerHTML = tempDiv.innerHTML + existingContent;
            
            // Place cursor at beginning of next page
            setTimeout(() => {
                const newRange = document.createRange();
                if (nextPageContent.firstChild) {
                    newRange.setStart(nextPageContent.firstChild, 0);
                } else {
                    const p = document.createElement('p');
                    p.innerHTML = '<br>';
                    nextPageContent.appendChild(p);
                    newRange.setStart(p, 0);
                }
                newRange.collapse(true);
                selection.removeAllRanges();
                selection.addRange(newRange);
                nextPageContent.focus();
            }, 100);
            
            currentPageCount = Math.max(currentPageCount, nextPageNumber);
        }
        function getAllContent() {
            let allContent = '';
            let title = '';
            
            // Get title
            const titleInput = document.getElementById('questionTitle');
            if (titleInput) {
                title = titleInput.value;
            }
            
            // Get content from all pages
            for (let i = 1; i <= currentPageCount; i++) {
                const pageContent = document.getElementById(`pageContent-${i}`);
                if (pageContent) {
                    allContent += pageContent.innerHTML;
                }
            }
            
            return { title, content: allContent };
        }

        // Save Current Content (Updated for multiple pages)
        function saveCurrentContent() {
            if (currentChapter < 0 || currentQuestion < 0) return;

            const allData = getAllContent();
            const title = allData.title;
            const content = allData.content;
            
            console.log('💾 Saving content:', { title, contentLength: content.length });
            
            // Update question title
            data.chapters[currentChapter].questions[currentQuestion].title = title;
            
            // Convert HTML to blocks and save
            const blocks = convertHTMLToBlocks(content);
            
            if (!data.chapters[currentChapter].questions[currentQuestion].answers) {
                data.chapters[currentChapter].questions[currentQuestion].answers = [{
                    id: 1,
                    user_id: 1,
                    author_first_name: "User",
                    author_last_name: "",
                    answer: JSON.stringify({ time: Date.now(), blocks: blocks })
                }];
            } else {
                data.chapters[currentChapter].questions[currentQuestion].answers[0].answer = JSON.stringify({ 
                    time: Date.now(), 
                    blocks: blocks 
                });
            }

            updateStatus('Auto-saved', 'status-saved');
        }

        // Setup Event Listeners (Updated for page merging)
        function setupEventListeners() {
            console.log('⚡ Setting up event listeners...');
            
            // Setup first page listeners
            setupPageEventListeners(1);
            
            // Auto-save on title change
            const titleInput = document.getElementById('questionTitle');
            if (titleInput) {
                titleInput.addEventListener('input', () => {
                    updateStatus('Editing...', 'status-saving');
                    clearTimeout(autoSaveTimer);
                    autoSaveTimer = setTimeout(() => {
                        saveCurrentContent();
                        loadSidebar(); // Refresh sidebar to show updated title
                    }, 2000);
                });
            }

            // Search functionality
            const searchInput = document.getElementById('searchInput');
            if (searchInput) {
                searchInput.addEventListener('input', (e) => {
                    filterQuestions(e.target.value);
                });
            }

            // Keyboard shortcuts
            document.addEventListener('keydown', (e) => {
                if (e.ctrlKey || e.metaKey) {
                    switch (e.key) {
                        case 'b':
                            e.preventDefault();
                            toggleFormat('bold');
                            break;
                        case 'i':
                            e.preventDefault();
                            toggleFormat('italic');
                            break;
                        case 'u':
                            e.preventDefault();
                            toggleFormat('underline');
                            break;
                        case 's':
                            e.preventDefault();
                            saveAllChanges();
                            break;
                        case 'p':
                            e.preventDefault();
                            printDocument();
                            break;
                        case 'z':
                            e.preventDefault();
                            execCommand('undo');
                            break;
                        case 'y':
                            e.preventDefault();
                            execCommand('redo');
                            break;
                        case 'Enter':
                            if (e.shiftKey) {
                                e.preventDefault();
                                insertPageBreak();
                            }
                            break;
                    }
                }
            });

            // Update toolbar state on selection change
            document.addEventListener('selectionchange', updateToolbarState);
            
            // Enhanced content monitoring - more frequent for better UX
            setInterval(monitorContentHeight, 2000); // Every 2 seconds
            
            console.log('✅ Event listeners setup complete');
        }

        // Formatting Functions
        function execCommand(command, value = null) {
            document.execCommand(command, false, value);
            updateToolbarState();
        }

        function toggleFormat(command) {
            execCommand(command);
        }

        function changeFontFamily(fontFamily) {
            execCommand('fontName', fontFamily);
        }

        function changeFontSize(size) {
            execCommand('fontSize', size);
        }

        function alignText(alignment) {
            const commands = {
                'left': 'justifyLeft',
                'center': 'justifyCenter', 
                'right': 'justifyRight',
                'justify': 'justifyFull'
            };
            execCommand(commands[alignment]);
        }

        function changeLineHeight(height) {
            const selection = window.getSelection();
            if (selection.rangeCount > 0) {
                const range = selection.getRangeAt(0);
                const element = range.commonAncestorContainer.nodeType === 3 
                    ? range.commonAncestorContainer.parentElement 
                    : range.commonAncestorContainer;
                element.style.lineHeight = height;
            }
        }

        function insertList(listType) {
            const command = listType === 'ul' ? 'insertUnorderedList' : 'insertOrderedList';
            execCommand(command);
        }

        function changeIndent(direction) {
            execCommand(direction);
        }

        function applyStyle(tag) {
            execCommand('formatBlock', tag);
        }

        function insertLink() {
            const url = prompt('Enter URL:');
            if (url) {
                execCommand('createLink', url);
            }
        }

        function insertImage() {
            const url = prompt('Enter image URL:');
            if (url) {
                execCommand('insertImage', url);
            }
        }

        function insertTable() {
            const rows = prompt('Number of rows:', '3');
            const cols = prompt('Number of columns:', '3');
            
            if (rows && cols) {
                let tableHTML = '<table style="width: 100%; border-collapse: collapse; margin: 12px 0;">';
                for (let i = 0; i < parseInt(rows); i++) {
                    tableHTML += '<tr>';
                    for (let j = 0; j < parseInt(cols); j++) {
                        tableHTML += '<td style="border: 1px solid #dadce0; padding: 8px 12px;">&nbsp;</td>';
                    }
                    tableHTML += '</tr>';
                }
                tableHTML += '</table>';
                
                execCommand('insertHTML', tableHTML);
            }
        }

        function showColorPicker(type) {
            const colorPicker = document.getElementById('colorPicker');
            colorPicker.click();
            
            colorPicker.onchange = function() {
                const color = this.value;
                if (type === 'textColor') {
                    execCommand('foreColor', color);
                } else {
                    execCommand('backColor', color);
                }
            };
        }

        function changeZoom(value) {
            const documentPage = document.getElementById('documentPage');
            documentPage.style.transform = `scale(${value / 100})`;
            documentPage.style.transformOrigin = 'top left';
        }

        function printDocument() {
            window.print();
        }

        // Update Toolbar State
        function updateToolbarState() {
            const commands = ['bold', 'italic', 'underline'];
            commands.forEach(command => {
                const button = document.getElementById(command + 'Btn');
                if (button) {
                    button.classList.toggle('active', document.queryCommandState(command));
                }
            });
        }

        // Filter Questions
        function filterQuestions(searchTerm) {
            const container = document.getElementById('questionsContainer');
            const items = container.querySelectorAll('.question-item');
            
            items.forEach(item => {
                const chapterText = item.querySelector('.question-chapter').textContent.toLowerCase();
                const questionText = item.querySelector('.question-title').textContent.toLowerCase();
                const matches = chapterText.includes(searchTerm.toLowerCase()) || 
                               questionText.includes(searchTerm.toLowerCase());
                
                item.style.display = matches ? 'block' : 'none';
            });
        }

        // Save All Changes
        function saveAllChanges() {
            saveCurrentContent();
            updateStatus('Saving...', 'status-saving');
            
            // Here you would typically send data to server
            // For now, just simulate save
            setTimeout(() => {
                updateStatus('All changes saved', 'status-saved');
            }, 1000);
        }

        // Update Status
        function updateStatus(message, className) {
            const statusText = document.getElementById('statusText');
            const statusIndicator = document.getElementById('statusIndicator');
            
            statusText.textContent = message;
            statusIndicator.className = 'status-indicator ' + className;
        }

        const pageBreakCSS = `
            .page-break-line {
                width: 100%;
                height: 1px;
                border-top: 1px dashed #ccc;
                margin: 20px 0;
                position: relative;
            }

            .page-break-line::before {
                content: "Page Break";
                position: absolute;
                top: -10px;
                left: 50%;
                transform: translateX(-50%);
                background: white;
                padding: 0 10px;
                font-size: 10px;
                color: #666;
            }

            @media print {
                .page-break-line {
                    page-break-after: always;
                    height: 0;
                    border: none;
                    margin: 0;
                }
                
                .page-break-line::before {
                    display: none;
                }
                
                .document-page {
                    page-break-after: always;
                    margin-bottom: 0;
                }
                
                .document-page:last-child {
                    page-break-after: auto;
                }
            }
            `;

        if (!document.getElementById('pageBreakStyles')) {
            const style = document.createElement('style');
            style.id = 'pageBreakStyles';
            style.textContent = pageBreakCSS;
            document.head.appendChild(style);
        }

        // Update the keyboard event listener for Enter key to handle page breaks properly
        document.addEventListener('keydown', (e) => {
            if (e.ctrlKey || e.metaKey) {
                switch (e.key) {
                    case 'b':
                        e.preventDefault();
                        toggleFormat('bold');
                        break;
                    case 'i':
                        e.preventDefault();
                        toggleFormat('italic');
                        break;
                    case 'u':
                        e.preventDefault();
                        toggleFormat('underline');
                        break;
                    case 's':
                        e.preventDefault();
                        saveAllChanges();
                        break;
                    case 'p':
                        e.preventDefault();
                        printDocument();
                        break;
                    case 'z':
                        e.preventDefault();
                        execCommand('undo');
                        break;
                    case 'y':
                        e.preventDefault();
                        execCommand('redo');
                        break;
                    case 'Enter':
                        if (e.shiftKey) {
                            e.preventDefault();
                            insertPageBreak();
                        }
                        break;
                }
            }
            
            // Handle regular Enter key for natural page flow
            if (e.key === 'Enter' && !e.ctrlKey && !e.metaKey && !e.shiftKey) {
                setTimeout(() => {
                    const selection = window.getSelection();
                    if (selection.rangeCount > 0) {
                        const range = selection.getRangeAt(0);
                        const pageContent = range.commonAncestorContainer.nodeType === 3 
                            ? range.commonAncestorContainer.parentElement.closest('.page-content')
                            : range.commonAncestorContainer.closest('.page-content');
                        
                        if (pageContent) {
                            const pageNumber = parseInt(pageContent.id.split('-')[1]);
                            checkPageOverflow(pageNumber);
                        }
                    }
                }, 100);
            }
        });

        function exportToPDF() {
            window.print();
        }


        // Handle cross-page content transfer
        function handleContentTransfer() {
            const pages = document.querySelectorAll('.document-page');
            
            pages.forEach((page, index) => {
                const content = page.querySelector('.page-content');
                
                content.addEventListener('input', function() {
                    redistributeContent();
                });
                
                content.addEventListener('keydown', function(e) {
                    if (e.key === 'Backspace' && content.innerText.trim() === '' && index > 0) {
                        // Move to previous page
                        const prevPage = pages[index - 1].querySelector('.page-content');
                        prevPage.focus();
                        // Position cursor at end
                        const range = document.createRange();
                        const selection = window.getSelection();
                        range.selectNodeContents(prevPage);
                        range.collapse(false);
                        selection.removeAllRanges();
                        selection.addRange(range);
                    }
                });
            });
        }

        // Improved Save Function - Replace the existing saveCurrentQuestion function in indexup.php

function saveAllChanges() {
    if (currentChapter === -1 || currentQuestion === -1 || isLoading) {
        console.log('❌ Cannot save - invalid state or loading');
        return;
    }

    console.log(`💾 Starting save for question ${currentChapter}-${currentQuestion}`);
    updateStatus('Saving...', 'status-saving');

    try {
        // Get title
        const titleInput = document.getElementById('questionTitle');
        const title = titleInput ? titleInput.value.trim() : '';

        // Collect content from all pages
        let allContent = '';
        for (let i = 1; i <= currentPageCount; i++) {
            const pageContent = document.getElementById(`pageContent-${i}`);
            if (pageContent) {
                const pageHtml = pageContent.innerHTML.trim();
                if (pageHtml && pageHtml !== '<p><br></p>' && pageHtml !== '<p></p>') {
                    if (i > 1 && allContent) {
                        allContent += '\n\n';
                    }
                    allContent += pageHtml;
                }
            }
        }

        // Update data structure
        if (!data.chapters[currentChapter]) {
            console.error('❌ Chapter not found:', currentChapter);
            updateStatus('Save failed - Chapter not found', 'status-error');
            return;
        }

        if (!data.chapters[currentChapter].questions[currentQuestion]) {
            console.error('❌ Question not found:', currentQuestion);
            updateStatus('Save failed - Question not found', 'status-error');
            return;
        }

        // Update title
        data.chapters[currentChapter].questions[currentQuestion].title = title;

        // Update content - ensure answers array exists
        if (!data.chapters[currentChapter].questions[currentQuestion].answers) {
            data.chapters[currentChapter].questions[currentQuestion].answers = [];
        }

        // Convert HTML to blocks format
        const blocks = convertHTMLToBlocks(allContent);
        const answerData = {
            time: Date.now(),
            blocks: blocks
        };

        if (data.chapters[currentChapter].questions[currentQuestion].answers.length === 0) {
            // Create new answer
            data.chapters[currentChapter].questions[currentQuestion].answers.push({
                id: 1,
                user_id: 1,
                author_first_name: "User",
                author_last_name: "",
                answer: JSON.stringify(answerData)
            });
        } else {
            // Update existing answer
            data.chapters[currentChapter].questions[currentQuestion].answers[0].answer = JSON.stringify(answerData);
        }

        console.log('📝 Data prepared for save:', {
            title: title,
            contentLength: allContent.length,
            blocksCount: blocks.length
        });

        // Send to server with improved error handling
        fetch('save.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'Cache-Control': 'no-cache'
            },
            body: JSON.stringify(data)
        })
        .then(response => {
            console.log('📡 Server response status:', response.status);
            
            if (!response.ok) {
                throw new Error(`HTTP error! status: ${response.status}`);
            }
            
            // Check if response is JSON
            const contentType = response.headers.get("content-type");
            if (contentType && contentType.indexOf("application/json") !== -1) {
                return response.json();
            } else {
                // If not JSON, get text and check if it's a success message
                return response.text().then(text => {
                    console.log('📄 Server response text:', text);
                    if (text.includes('successfully') || text.includes('saved')) {
                        return { success: true, message: text };
                    } else {
                        throw new Error('Unexpected server response: ' + text);
                    }
                });
            }
        })
        .then(result => {
            console.log('✅ Save result:', result);
            
            if (result && (result.success || result.message)) {
                updateStatus('Auto-saved successfully', 'status-saved');
                updateLastSaved();
                autoSaveCount++;
                console.log(`✅ Auto-save #${autoSaveCount} completed at ${new Date().toLocaleTimeString()}`);
                
                // Refresh sidebar to show updated title
                loadSidebar();
            } else {
                throw new Error(result ? result.message : 'Unknown server error');
            }
        })
        .catch(error => {
            console.error('❌ Save error:', error);
            updateStatus('Save failed - ' + error.message, 'status-error');
            
            // Show user-friendly error messages
            if (error.message.includes('Failed to fetch')) {
                updateStatus('Network error - check connection', 'status-error');
            } else if (error.message.includes('HTTP error')) {
                updateStatus('Server error - try again', 'status-error');
            }
        });

    } catch (error) {
        console.error('❌ Save preparation error:', error);
        updateStatus('Save failed - data error', 'status-error');
    }
}

// Improved updateStatus function with better visual feedback
function updateStatus(message, className) {
    const statusElement = document.getElementById('saveStatus');
    if (statusElement) {
        statusElement.textContent = message;
        statusElement.className = 'save-status ' + className;
        
        // Add timestamp for debugging
        console.log(`📊 Status: ${message} at ${new Date().toLocaleTimeString()}`);
        
        // Auto-clear error messages after 5 seconds
        if (className === 'status-error') {
            setTimeout(() => {
                if (statusElement.textContent === message) {
                    statusElement.textContent = 'Ready';
                    statusElement.className = 'save-status';
                }
            }, 5000);
        }
    }
}

// Helper function to update last saved time
function updateLastSaved() {
    const now = new Date();
    const timeString = now.toLocaleTimeString();
    
    // Update last saved indicator if it exists
    const lastSavedElement = document.getElementById('lastSaved');
    if (lastSavedElement) {
        lastSavedElement.textContent = `Last saved: ${timeString}`;
    }
    
    console.log(`💾 Last saved updated: ${timeString}`);
}

// Improved convertHTMLToBlocks function
function convertHTMLToBlocks(html) {
    if (!html || html.trim() === '') {
        return [{
            type: 'paragraph',
            data: { text: '' }
        }];
    }

    const blocks = [];
    const tempDiv = document.createElement('div');
    tempDiv.innerHTML = html;

    // Process each top-level element
    const elements = tempDiv.children;
    for (let element of elements) {
        const tagName = element.tagName.toLowerCase();
        const text = element.innerHTML.trim();

        if (text === '' || text === '<br>') {
            continue; // Skip empty elements
        }

        switch (tagName) {
            case 'h1':
            case 'h2':
            case 'h3':
            case 'h4':
            case 'h5':
            case 'h6':
                blocks.push({
                    type: 'header',
                    data: {
                        text: element.textContent.trim(),
                        level: parseInt(tagName.charAt(1))
                    }
                });
                break;
            case 'ul':
                const listItems = Array.from(element.querySelectorAll('li')).map(li => li.textContent.trim());
                blocks.push({
                    type: 'list',
                    data: {
                        style: 'unordered',
                        items: listItems
                    }
                });
                break;
            case 'ol':
                const orderedItems = Array.from(element.querySelectorAll('li')).map(li => li.textContent.trim());
                blocks.push({
                    type: 'list',
                    data: {
                        style: 'ordered',
                        items: orderedItems
                    }
                });
                break;
            default:
                blocks.push({
                    type: 'paragraph',
                    data: { text: text }
                });
        }
    }

    return blocks.length > 0 ? blocks : [{
        type: 'paragraph',
        data: { text: html }
    }];
}

    </script>
</body>
</html>