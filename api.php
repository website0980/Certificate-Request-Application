<?php
require_once __DIR__ . '/includes/common.php';
require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/config/database.php';

set_exception_handler(function (Throwable $exception): void {
    if (headers_sent()) {
        echo json_encode(['error' => 'Server error: ' . $exception->getMessage()]);
        return;
    }
    jsonResponse(['error' => $exception->getMessage()], 500);
});

function validateApplicationInput(array $application): array
{
    $required = ['full_name', 'designation', 'office', 'purpose', 'request_date'];
    $errors = [];

    foreach ($required as $field) {
        if (empty($application[$field]) || !is_string($application[$field])) {
            $errors[] = "{$field} is required.";
        }
    }

    return $errors;
}

$action = $_GET['action'] ?? null;

switch ($action) {
    case 'save':
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            jsonResponse(['error' => 'Method not allowed.'], 405);
        }

        $application = [
            'full_name' => getRequestValue('full_name'),
            'designation' => getRequestValue('designation'),
            'office' => getRequestValue('office'),
            'purpose' => getRequestValue('purpose'),
            'request_date' => getRequestValue('request_date'),
            'csrf_token' => getRequestValue('csrf_token'),
        ];

        requireCsrfToken();

        $errors = validateApplicationInput($application);
        if ($errors) {
            jsonResponse(['error' => implode(' ', $errors)], 400);
        }

        if (Database::findDuplicate(
            $application['full_name'],
            $application['designation'],
            $application['office'],
            $application['purpose'],
            $application['request_date']
        )) {
            jsonResponse(['error' => 'A similar application was submitted recently. Please wait before submitting again.'], 409);
        }

        $result = Database::saveApplication(
            $application['full_name'],
            $application['designation'],
            $application['office'],
            $application['purpose'],
            $application['request_date']
        );

        jsonResponse(['success' => true, 'id' => $result['id'], 'reference_no' => $result['reference_no']]);
        break;

    case 'list':
        requireAdminApi();
        jsonResponse(Database::listApplications());
        break;

    case 'get':
        requireAdminApi();
        $id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);
        if (!$id) {
            jsonResponse(['error' => 'Submission id is required.'], 400);
        }

        $application = Database::getApplicationById($id);
        if (!$application) {
            jsonResponse(['error' => 'Submission not found.'], 404);
        }

        jsonResponse($application);
        break;

    case 'delete':
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            jsonResponse(['error' => 'Method not allowed.'], 405);
        }

        requireAdminApi();
        $id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);
        if (!$id) {
            jsonResponse(['error' => 'Submission id is required.'], 400);
        }

        if (Database::deleteApplication($id)) {
            jsonResponse(['success' => true]);
        }

        jsonResponse(['error' => 'Submission not found.'], 404);
        break;

    case 'mark_printed':
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            jsonResponse(['error' => 'Method not allowed.'], 405);
        }

        requireAdminApi();
        $id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);
        if (!$id) {
            jsonResponse(['error' => 'Submission id is required.'], 400);
        }

        if (Database::markAsPrinted($id)) {
            jsonResponse(['success' => true]);
        }

        jsonResponse(['error' => 'Submission not found.'], 404);
        break;

    default:
        jsonResponse(['error' => 'Invalid action. Use save, list, get, delete, or mark_printed.'], 400);
}
