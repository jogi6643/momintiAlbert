<?php
require_once __DIR__ . '/vendor/autoload.php';

$mpdf = new \Mpdf\Mpdf(['format' => 'A5']);
$html = $_POST['html'] ?? 'No content received.';
$html = preg_replace('/<label class="removeurl".*?<\/label>/s', '', $html);
$html = preg_replace('/<input[^>]*class="[^"]*removeurl[^"]*"[^>]*>/', '', $html);
$mpdf->WriteHTML($html);
$mpdf->Output('meminto_book.pdf', 'I');
