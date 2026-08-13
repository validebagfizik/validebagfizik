<?php
header('Content-Type: application/json; charset=utf-8');

$file = __DIR__ . '/data/comments.json';
$data = json_decode(file_get_contents('php://input'), true);

if (!$data || empty($data['text']) || empty($data['page'])) {
    echo json_encode(['success' => false, 'message' => 'Eksik veri gönderildi.']);
    exit;
}

$comments = file_exists($file) ? json_decode(file_get_contents($file), true) : [];

$newComment = [
    'id' => uniqid('c_'),
    'page' => $data['page'],
    'name' => strip_tags($data['name'] ?? 'Anonim'),
    'text' => strip_tags($data['text']),
    'status' => 'pending',
    'date' => date('d.m.Y H:i')
];

$comments[] = $newComment;
file_put_contents($file, json_encode($comments, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT));

echo json_encode(['success' => true, 'message' => 'Yorum başarıyla iletildi.']);