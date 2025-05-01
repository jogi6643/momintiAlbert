<?php
// Get the raw POST body
$data = json_decode(file_get_contents('php://input'), true);

// Check if it's valid
if ($data) {
    // Save it to a file
    file_put_contents('editor_data.json', json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));

    // Respond with JSON
    echo json_encode(['status' => 'success']);
} else {
    // Invalid JSON received
    http_response_code(400);
    echo json_encode(['status' => 'error', 'message' => 'Invalid JSON']);
}
?>
