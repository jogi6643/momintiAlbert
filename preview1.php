<?php
$json = file_get_contents('editor_data.json');
$data = json_decode($json, true);


?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Edit & Export Meminto</title>
    <style>
        body { font-family: sans-serif; padding: 20px; }
        .toc ul { list-style: none; padding-left: 0; }
        .toc li a { text-decoration: none; color: blue; }
        [contenteditable] { border: 1px dashed #aaa; padding: 5px; margin-bottom: 10px; }
        img { max-width: 100%; height: auto; }
        button { margin: 10px 10px 30px 0; padding: 10px 20px; font-size: 16px; }
    </style>
</head>
<body>

<h1>Preview & Edit Meminto Book</h1>

<div class="toc">
    <h2>Table of Contents</h2>
    <ul>
        <?php
        foreach ($data['blocks'] as $i => $block) {
            if ($block['type'] === 'header') {
                $text = htmlspecialchars($block['data']['text']);
                $id = "section_$i";
                echo "<li><a href='#$id'>$text</a></li>";
            }
        }
        ?>
    </ul>
</div>

<hr>

<form id="pdfForm" method="post" action="generate_pdf.php" target="_blank">
    <div id="content">
        <?php
        foreach ($data['blocks'] as $i => $block) {
            $type = $block['type'];
            $content = $block['data'];

            switch ($type) {
                case 'header':
                    echo "<h{$content['level']} id='section_$i' contenteditable='true' data-type='header' data-index='{$i}'>{$content['text']}</h{$content['level']}>"; break;
                case 'paragraph':
                    echo "<p contenteditable='true' data-type='paragraph' data-index='{$i}'>{$content['text']}</p>"; break;
                case 'image':
                    $img = $content['images'][0] ?? [];
                    $url = $img['url'] ?? '';
                    $caption = $img['caption'] ?? '';
                    if ($url) {
                        echo "<div><img src='{$url}'><br><small contenteditable='true' data-type='image' data-index='{$i}'>{$caption}</small></div>";
                    } else {
                        echo "<div><strong>[Missing image URL]</strong></div>";
                    }
                    break;
                case 'twoImage':
                    $img1 = $content['images'][0]['url'] ?? '';
                    $img2 = $content['images'][1]['url'] ?? '';
                    echo "<div style='display:flex; gap:10px;'>
                            <img src='{$img1}' style='width:48%;'>
                            <img src='{$img2}' style='width:48%;'>
                          </div>";
                    break;
                case 'video':
                    $url = $content['url'];
                    echo "<p>YouTube video: <a href='{$url}' target='_blank'>{$url}</a></p>";
                    break;
                default:
                    echo "<p>[Unknown block type: $type]</p>";
            }
        }
        ?>
    </div>
    <input type="hidden" name="html" id="htmlInput">
    <button type="submit" onclick="preparePDF()">Download PDF</button>
    <button type="button" onclick="saveEdits()">Save Changes to JSON</button>
</form>

<script>
function preparePDF() {
    const html = document.getElementById('content').innerHTML;
    document.getElementById('htmlInput').value = html;
}


let originalData = <?php echo json_encode($data); ?>;

function saveEdits() {
    const editableElements = document.querySelectorAll('[contenteditable][data-type]');

    editableElements.forEach(el => {
        const type = el.getAttribute('data-type');
        const index = parseInt(el.getAttribute('data-index'));
        const content = el.innerText.trim();

        if (!originalData.blocks[index]) return;

        if (type === 'header') {
            const level = el.tagName.replace('H', '');
            originalData.blocks[index] = {
                type: 'header',
                data: {
                    text: content,
                    level: parseInt(level)
                }
            };
        } else if (type === 'paragraph') {
            originalData.blocks[index] = {
                type: 'paragraph',
                data: {
                    text: content
                }
            };
        } else if (type === 'image') {
            originalData.blocks[index] = {
                type: 'image',
                data: {
                    images: [{
                        url: el.previousElementSibling.src,
                        caption: content
                    }]
                }
            };
        }
    });

    // Send updated full data including preserved non-editable blocks
    fetch('save_json.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json'
        },
        body: JSON.stringify(originalData)
    })
    .then(res => res.json())
    .then(data => {
        console.log('Save successful:', data);
        alert('Changes saved!');
    })
    .catch(err => {
        console.error('Error saving data:', err);
        alert('Failed to save changes.');
    });
}

</script>

</body>
</html>
