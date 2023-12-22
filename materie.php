<?php
require_once 'config.php';
// Set the content type to JSON
header('Content-Type: application/json');

// Handle HTTP methods
$method = $_SERVER['REQUEST_METHOD'];

switch ($method) {
    case 'GET':
        try {
            // Read operation (fetch subjects)
            $stmt = $pdo->query('SELECT * FROM subjects');
            $result = $stmt->fetchAll(PDO::FETCH_ASSOC);
            echo json_encode($result, JSON_PRETTY_PRINT); // JSON_PRETTY_PRINT for formatted output
        } catch (PDOException $e) {
            http_response_code(500);
            echo json_encode(['error' => 'Database error: ' . $e->getMessage()], JSON_PRETTY_PRINT);
        }
        break;

    case 'POST':
        try {
            $data = json_decode(file_get_contents('php://input'), true);
            if (empty($data['subject_name'])) {
                http_response_code(400);
                echo json_encode(['error' => 'Subject name is required'], JSON_PRETTY_PRINT);
                break;
            }

            $subjectName = filter_var($data['subject_name'], FILTER_SANITIZE_STRING);
            $stmt = $pdo->prepare('INSERT INTO subjects (subject_name) VALUES (?)');
            $stmt->execute([$subjectName]);

            echo json_encode(['message' => 'Subject added successfully'], JSON_PRETTY_PRINT);
        } catch (PDOException $e) {
            http_response_code(500);
            echo json_encode(['error' => 'Database error: ' . $e->getMessage()], JSON_PRETTY_PRINT);
        }
        break;

    case 'PUT':
        try {
            $data = json_decode(file_get_contents('php://input'), true);
            if (empty($data['subject_id']) || empty($data['subject_name'])) {
                http_response_code(400);
                echo json_encode(['error' => 'Subject ID and name are required'], JSON_PRETTY_PRINT);
                break;
            }

            $id = $data['subject_id'];
            $subjectName = $data['subject_name'];

            $stmt = $pdo->prepare('UPDATE subjects SET subject_name=? WHERE subject_id=?');
            $stmt->execute([$subjectName, $id]);

            echo json_encode(['message' => 'Subject updated successfully'], JSON_PRETTY_PRINT);
        } catch (PDOException $e) {
            http_response_code(500);
            echo json_encode(['error' => 'Database error: ' . $e->getMessage()], JSON_PRETTY_PRINT);
        }
        break;

    case 'DELETE':
        try {
            if (empty($_GET['subject_id'])) {
                http_response_code(400);
                echo json_encode(['error' => 'Subject ID is required'], JSON_PRETTY_PRINT);
                break;
            }

            $id = $_GET['subject_id'];
            $stmt = $pdo->prepare('DELETE FROM subjects WHERE subject_id=?');
            $stmt->execute([$id]);

            echo json_encode(['message' => 'Subject deleted successfully'], JSON_PRETTY_PRINT);
        } catch (PDOException $e) {
            http_response_code(500);
            echo json_encode(['error' => 'Database error: ' . $e->getMessage()], JSON_PRETTY_PRINT);
        }
        break;

    default:
        // Invalid method
        http_response_code(405);
        echo json_encode(['error' => 'Method not allowed'], JSON_PRETTY_PRINT);
        break;
}
?>
