<?php
require_once __DIR__ . '/vendor/autoload.php';

$json = file_get_contents('editor_data.json');
$data = json_decode($json, true);

$mpdf = new \Mpdf\Mpdf(['format' => 'A5']);

$html = '';
$toc = '';

foreach ($data['blocks'] as $block) {
    $type = $block['type'];
    $content = $block['data'];

    switch ($type) {
        case 'header':
            $level = $content['level'];
            $text = $content['text'];
            $anchor = 'h' . md5($text);
            $html .= "<h{$level} id='{$anchor}'>{$text}</h{$level}>";
            $toc  .= "<li><a href='#{$anchor}'>{$text}</a></li>";
            break;

        case 'paragraph':
            $text = $content['text'];
            $html .= "<p>{$text}</p>";
            break;

        case 'image':
            $img = $content['images'][0];
            $url = $img['url'];
            $caption = $img['caption'] ?? '';
            $html .= "<div style='text-align:center;'><img src='{$url}' style='max-width:100%; height:auto;'><br><small>{$caption}</small></div>";
            break;

        case 'twoImage':
            $img1 = $content['images'][0]['url'];
            $img2 = $content['images'][1]['url'];
            $html .= "
                <div style='display:flex; justify-content:space-between;'>
                    <img src='{$img1}' style='width:48%; height:auto;'>
                    <img src='{$img2}' style='width:48%; height:auto;'>
                </div>
            ";
            break;

        case 'video':
            $url = $content['url'];
            $html .= "<p>YouTube video: <a href='{$url}'>{$url}</a></p>";
            break;

        default:
            $html .= "<p>[Unknown block: {$type}]</p>";
    }
}

// Add title page
$mpdf->WriteHTML("<h1>My Meminto Book</h1><p>Generated from Editor.js JSON</p>");
$mpdf->AddPage();

// Add table of contents
$mpdf->WriteHTML("<h2>Table of Contents</h2><ul>{$toc}</ul>");
$mpdf->AddPage();

// Add content
$mpdf->WriteHTML($html);

// Output the PDF
$mpdf->Output('meminto_book.pdf', 'I');
?>
