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
        *{box-sizing: border-box;}
        body { font-family: sans-serif; padding: 0px; margin: 0; }
        h1{
            text-align: center;
        }
        .toc{
            text-align: center;
            margin: 5em 0;
            page-break-after: always;
        }
        .toc ul { list-style: none; padding-left: 0; }
        .toc ul li{margin:5px 0;}
        .toc li a { text-decoration: none; color: blue; }
        [contenteditable] { border: 1px dashed #aaa; padding: 5px; margin-bottom: 10px; }
        img { max-width: 100%; height: auto; }
        button { margin: 10px 10px 30px 0; padding: 10px 20px; font-size: 16px; }
        input.image-url, input.video-url { width: 100%; margin: 5px 0; }
        .two-image-block img{width: 100%;
    height: 420px;
    object-fit: cover;}
        .wrapper{max-width: 1200px; margin: auto; padding:0 15px;}
        @media print {
    .removeurl{
        display: none !important;
    }
    .video-link{
        margin-bottom: 0;
    }
}
    </style>
</head>
<body>

<div class="wrapper">
<h1>Preview & Edit Meminto Book</h1>


<form id="pdfForm" method="post" action="generate_pdf.php" target="_blank">
    <div id="content">
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
                    echo "<div data-type='image' data-index='{$i}' class='image-block'>
                            <img src='{$url}' class='editable-image'><br>
                            <label class='removeurl'>Image URL: <input type='text' class='image-url removeurl' value='{$url}'></label><br>
                            <small contenteditable='true'>{$caption}</small>
                          </div>";
                    break;

                case 'twoImage':
                    $img1 = $content['images'][0]['url'] ?? '';
                    $img2 = $content['images'][1]['url'] ?? '';
                    echo "<div data-type='twoImage' data-index='{$i}' class='two-image-block' style='display:flex; gap:10px;'>
                            <div>
                                <img src='{$img1}'><br>
                                <label class='removeurl'>Image 1 URL: <input type='text' class='twoimage-url removeurl' data-img='0' value='{$img1}'></label>
                            </div>
                            <div>
                                <img src='{$img2}'><br>
                                <label class='removeurl'>Image 2 URL: <input type='text' class='twoimage-url removeurl' data-img='1' value='{$img2}'></label>
                            </div>
                          </div>";
                    break;

                case 'video':
                    $url = $content['url'];
                    echo "<div data-type='video' data-index='{$i}' class='video-block'>
                            <p class='video-link'>YouTube video:</p>
                            <input type='text' class='video-url removeurl' value='{$url}'><br>
                            <a href='{$url}' target='_blank'>{$url}</a>
                          </div>";
                    break;

                default:
                    echo "<p>[Unknown block type: $type]</p>";
            }
        }
        ?>
    </div>

    <input type="hidden" name="html" id="htmlInput">
    <!-- <button type="submit" onclick="preparePDF()">Download PDF</button> -->
    <button type="button" onclick="saveEdits()">Save Changes</button>
    <button onclick="printDiv('content')">Download PDF</button>
</form>
</div>

<script>

function printDiv(divId) {
     var printContents = document.getElementById(divId).innerHTML;
     var originalContents = document.body.innerHTML;

     document.body.innerHTML = printContents;

     window.print();

     document.body.innerHTML = originalContents;
}


function preparePDF() {
    const html = document.getElementById('content').innerHTML;
    document.getElementById('htmlInput').value = html;
}

let originalData = <?php echo json_encode($data); ?>;

function saveEdits() {
    const blocks = [];

    originalData.blocks.forEach((block, i) => {
        const type = block.type;

        if (type === 'header') {
            const el = document.querySelector(`[data-type="header"][data-index="${i}"]`);
            if (el) {
                const level = el.tagName.replace('H', '');
                const text = el.innerText.trim();
                blocks.push({ type, data: { text, level: parseInt(level) } });
            }
        } else if (type === 'paragraph') {
            const el = document.querySelector(`[data-type="paragraph"][data-index="${i}"]`);
            if (el) {
                const text = el.innerText.trim();
                blocks.push({ type, data: { text } });
            }
        } else if (type === 'image') {
            const container = document.querySelector(`[data-type="image"][data-index="${i}"]`);
            const inputEl = container?.querySelector('.image-url');
            const captionEl = container?.querySelector('small');
            if (inputEl && captionEl) {
                const url = inputEl.value.trim();
                const caption = captionEl.innerText.trim();
                blocks.push({
                    type,
                    data: { images: [{ url, caption }] }
                });
            }
        } else if (type === 'twoImage') {
            const container = document.querySelector(`[data-type="twoImage"][data-index="${i}"]`);
            const inputs = container?.querySelectorAll('.twoimage-url');
            const images = [];
            inputs.forEach(input => {
                images.push({ url: input.value.trim() });
            });
            blocks.push({
                type,
                data: { images }
            });
        } else if (type === 'video') {
            const container = document.querySelector(`[data-type="video"][data-index="${i}"]`);
            const input = container?.querySelector('.video-url');
            if (input) {
                const url = input.value.trim();
                blocks.push({ type, data: { url } });
            }
        }
    });

    fetch('save_json.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ blocks })
    })
    .then(res => res.json())
    .then(data => {
        console.log('Save successful:', data);
        alert('Saved....');
        window.location.reload();
    })
    .catch(err => {
        console.error('Error saving data:', err);
        alert('Failed to save data');
    });
}
</script>

</body>
</html>
