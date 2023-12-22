<?php

require_once 'config.php';
header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $data = json_decode(file_get_contents('php://input'), true);
    if ($data === null) {
        http_response_code(400);
        echo json_encode(array("error" => "Invalid JSON data received."));
    } else {
        createCourse($pdo, $data);
    }
} elseif ($_SERVER['REQUEST_METHOD'] === 'DELETE') {
    $data = json_decode(file_get_contents('php://input'), true);
    if ($data === null) {
        http_response_code(400);
        echo json_encode(array("error" => "Invalid JSON data received."));
    } else {
        deleteCourse($pdo, $data);
    }
} elseif ($_SERVER['REQUEST_METHOD'] === 'PUT') {
    $data = json_decode(file_get_contents('php://input'), true);
    if ($data === null) {
        http_response_code(400);
        echo json_encode(array("error" => "Invalid JSON data received."));
    } else {
        updateAvailableSeats($pdo, $data);
    }
} elseif ($_SERVER['REQUEST_METHOD'] === 'GET') {
    if (!empty($_GET)) {
        $filteredCourses = filterCourses($pdo, $_GET);
        echo json_encode($filteredCourses);
    } else {
        getCourses($pdo);
    }
} else {
    http_response_code(405);
    echo json_encode(array("error" => "Method not allowed."));
}


// Function to create a new course
function createCourse($pdo, $data) {
    if (isset($data['course_name'], $data['available_seats'], $data['subjects'])) {
        $courseName = $data['course_name'];
        $availableSeats = $data['available_seats'];
        $subjects = $data['subjects'];

        try {
            $pdo->beginTransaction();

            $stmt = $pdo->prepare("INSERT INTO courses (course_name, available_seats) VALUES (:course_name, :available_seats)");
            $stmt->bindParam(':course_name', $courseName);
            $stmt->bindParam(':available_seats', $availableSeats);
            $stmt->execute();

            $courseId = $pdo->lastInsertId();

            if (!$courseId) {
                $pdo->rollBack();
                echo json_encode(array("error" => "Errore durante l'inserimento del corso."));
                return;
            }

            foreach ($subjects as $subjectId) {
                $stmt = $pdo->prepare("INSERT INTO course_subjects (course_id, subject_id) VALUES (:course_id, :subject_id)");
                $stmt->bindParam(':course_id', $courseId);
                $stmt->bindParam(':subject_id', $subjectId);
                $stmt->execute();
            }

            $pdo->commit();
            echo json_encode(array("message" => "Corso creato con successo."));
        } catch (PDOException $e) {
            $pdo->rollBack();
            echo json_encode(array("error" => "Errore durante la creazione del corso: " . $e->getMessage()));
        }
    } else {
        echo json_encode(array("error" => "Assicurati di fornire nome del corso, posti disponibili e materie del corso."));
    }
}

// Function to delete a course
function deleteCourse($pdo, $data) {
    $courseId = isset($data['course_id']) ? $data['course_id'] : null;

    if ($courseId) {
        try {
            $stmt = $pdo->prepare("DELETE FROM courses WHERE course_id = :course_id");
            $stmt->bindParam(':course_id', $courseId);
            $stmt->execute();

            echo json_encode(array("message" => "Corso eliminato con successo."));
        } catch (PDOException $e) {
            die(json_encode(array("error" => "Errore durante l'eliminazione del corso: " . $e->getMessage())));
        }
    } else {
        echo json_encode(array("error" => "ID del corso non valido."));
    }
}

// Function to update available seats of a course
function updateAvailableSeats($pdo, $data) {
    $courseId = isset($data['course_id']) ? $data['course_id'] : null;
    $newAvailableSeats = isset($data['available_seats']) ? $data['available_seats'] : null;

    if ($courseId && $newAvailableSeats !== null) {
        try {
            $stmt = $pdo->prepare("UPDATE courses SET available_seats = :available_seats WHERE course_id = :course_id");
            $stmt->bindParam(':available_seats', $newAvailableSeats);
            $stmt->bindParam(':course_id', $courseId);
            $stmt->execute();

            echo json_encode(array("message" => "Posti disponibili del corso aggiornati con successo."));
        } catch (PDOException $e) {
            die(json_encode(array("error" => "Errore durante l'aggiornamento dei posti disponibili del corso: " . $e->getMessage())));
        }
    } else {
        echo json_encode(array("error" => "ID del corso o posti disponibili non validi."));
    }
}



// Funzione per filtrare i corsi in base ai parametri GET
function filterCourses($pdo, $filters) {
    $sql = "SELECT * FROM courses WHERE 1=1";

    if (isset($filters['course_name'])) {
        $sql .= " AND course_name LIKE :course_name";
    }
    if (isset($filters['subject_id'])) {
        $sql .= " AND EXISTS (SELECT 1 FROM course_subjects WHERE courses.course_id = course_subjects.course_id AND subject_id = :subject_id)";
    }
    if (isset($filters['available_seats'])) {
        $sql .= " AND available_seats >= :available_seats";
    }

    try {
        $stmt = $pdo->prepare($sql);

        if (isset($filters['course_name'])) {
            $courseName = '%' . $filters['course_name'] . '%';
            $stmt->bindParam(':course_name', $courseName, PDO::PARAM_STR);
        }
        if (isset($filters['subject_id'])) {
            $subjectId = $filters['subject_id'];
            $stmt->bindParam(':subject_id', $subjectId, PDO::PARAM_INT);
        }
        if (isset($filters['available_seats'])) {
            $availableSeats = $filters['available_seats'];
            $stmt->bindParam(':available_seats', $availableSeats, PDO::PARAM_INT);
        }


        $stmt->execute();
        $filteredCourses = $stmt->fetchAll(PDO::FETCH_ASSOC);

        if ($filteredCourses) {
            return $filteredCourses;
        } else {
            return array("message" => "Nessun corso trovato con i filtri specificati.");
        }
    } catch (PDOException $e) {
        die(json_encode(array("error" => "Errore durante il recupero dei corsi filtrati: " . $e->getMessage())));
    }
}

// Funzione per ottenere tutti i corsi
// Funzione per ottenere tutti i corsi con materie e relativi ID
// Funzione per ottenere tutti i corsi con materie e relativi ID e nomi
function getCourses($pdo) {
    try {
        $stmt = $pdo->query("SELECT courses.course_id, courses.course_name, courses.available_seats, GROUP_CONCAT(course_subjects.subject_id) AS subject_ids, GROUP_CONCAT(subjects.subject_name) AS subject_names
                             FROM courses
                             LEFT JOIN course_subjects ON courses.course_id = course_subjects.course_id
                             LEFT JOIN subjects ON course_subjects.subject_id = subjects.subject_id
                             GROUP BY courses.course_id");
        $courses = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $formattedCourses = [];

        if ($courses) {
            foreach ($courses as $course) {
                $formattedCourse = [
                    'course_id' => $course['course_id'],
                    'course_name' => $course['course_name'],
                    'available_seats' => $course['available_seats'],
                    'subjects' => []
                ];

                if ($course['subject_ids']) {
                    $subjectIds = explode(',', $course['subject_ids']);
                    $subjectNames = explode(',', $course['subject_names']);

                    foreach ($subjectIds as $key => $subjectId) {
                        $formattedCourse['subjects'][] = [
                            $subjectId => $subjectNames[$key]
                        ];
                    }
                }

                $formattedCourses[] = $formattedCourse;
            }

            echo json_encode($formattedCourses, JSON_PRETTY_PRINT); // JSON_PRETTY_PRINT per una formattazione più leggibile
        } else {
            echo json_encode(array("message" => "Nessun corso trovato."));
        }
    } catch (PDOException $e) {
        die(json_encode(array("error" => "Errore durante il recupero dei corsi: " . $e->getMessage())));
    }
}

?>
