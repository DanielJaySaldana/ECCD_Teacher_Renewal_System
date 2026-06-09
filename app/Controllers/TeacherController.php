<?php
declare(strict_types=1);

namespace App\Controllers;

use App\Lib\Auth;
use App\Lib\Csrf;
use App\Lib\Db;
use App\Lib\Flash;
use App\Lib\Notifier;
use App\Lib\Router;
use App\Lib\Uploader;
use App\Lib\Validator;
use App\Lib\View;

final class TeacherController
{
    public static function dashboard(): void
    {
        Auth::requireRole('teacher');
        $pdo = Db::pdo();
        $uid = Auth::user()['id'];

        $teacher = static::teacherRow($uid);

        $openSchedules = $pdo->query("SELECT * FROM renewal_schedules WHERE CURDATE() BETWEEN open_from AND due_until ORDER BY due_until ASC")
            ->fetchAll();

        $latestAppStmt = $pdo->prepare('SELECT * FROM renewal_applications WHERE teacher_user_id = ? ORDER BY submitted_at DESC, id DESC LIMIT 1');
        $latestAppStmt->execute([$uid]);
        $latestApp = $latestAppStmt->fetch() ?: null;

        View::render('teacher/dashboard', [
            'title' => 'Teacher Dashboard',
            'teacher' => $teacher,
            'openSchedules' => $openSchedules,
            'latestApp' => $latestApp,
        ]);
    }

    public static function profile(): void
    {
        Auth::requireRole('teacher');
        $teacher = static::teacherRow(Auth::user()['id']);
        View::render('teacher/profile', [
            'title' => 'My Profile',
            'teacher' => $teacher,
        ]);
    }

    public static function saveProfile(): void
    {
        Auth::requireRole('teacher');
        if (!Csrf::validate($_POST['_csrf'] ?? null)) {
            http_response_code(400);
            echo 'Bad Request (CSRF)';
            return;
        }

        $contactNo = trim((string)($_POST['contact_no'] ?? ''));
        $address = trim((string)($_POST['address'] ?? ''));
        $dateOfBirth = trim((string)($_POST['date_of_birth'] ?? ''));
        $placeOfBirth = trim((string)($_POST['place_of_birth'] ?? ''));
        $ageRaw = trim((string)($_POST['age'] ?? ''));
        $sex = trim((string)($_POST['sex'] ?? ''));

        $bachelorDegree = trim((string)($_POST['bachelor_degree'] ?? ''));
        $religion = trim((string)($_POST['religion'] ?? ''));
        $bloodType = trim((string)($_POST['blood_type'] ?? ''));
        $nationality = trim((string)($_POST['nationality'] ?? ''));
        $civilStatus = trim((string)($_POST['civil_status'] ?? ''));
        $weightKg = trim((string)($_POST['weight_kg'] ?? ''));
        $heightFt = trim((string)($_POST['height_ft'] ?? ''));

        $emergencyName = trim((string)($_POST['emergency_contact_name'] ?? ''));
        $emergencyNo = trim((string)($_POST['emergency_contact_no'] ?? ''));
        $emergencyAddress = trim((string)($_POST['emergency_contact_address'] ?? ''));

        if ($contactNo !== '' && !preg_match('/^\d+$/', $contactNo)) {
            Flash::set('danger', 'Contact No. must contain numbers only.');
            Router::redirect('/teacher/profile');
        }

        if ($emergencyNo !== '' && !preg_match('/^\d+$/', $emergencyNo)) {
            Flash::set('danger', 'Emergency contact number must contain numbers only.');
            Router::redirect('/teacher/profile');
        }

        if ($dateOfBirth !== '' && !Validator::dateYmd($dateOfBirth)) {
            Flash::set('danger', 'Date of Birth must be a valid date (YYYY-MM-DD).');
            Router::redirect('/teacher/profile');
        }

        $age = null;
        if ($ageRaw !== '') {
            if (!preg_match('/^\d{1,3}$/', $ageRaw)) {
                Flash::set('danger', 'Age must be a number.');
                Router::redirect('/teacher/profile');
            }
            $ageInt = (int)$ageRaw;
            if ($ageInt < 0 || $ageInt > 120) {
                Flash::set('danger', 'Age must be between 0 and 120.');
                Router::redirect('/teacher/profile');
            }
            $age = $ageInt;
        }

        if ($sex !== '' && !in_array($sex, ['Male', 'Female'], true)) {
            Flash::set('danger', 'Sex must be Male or Female.');
            Router::redirect('/teacher/profile');
        }

        if ($weightKg !== '' && !preg_match('/^\d{1,3}(\.\d{1,2})?$/', $weightKg)) {
            Flash::set('danger', 'Weight (kg) must be numeric (e.g. 52 or 52.5).');
            Router::redirect('/teacher/profile');
        }

        if ($heightFt !== '' && !preg_match("/^\d{1,2}'\d{1,2}$/", $heightFt)) {
            Flash::set('danger', 'Height (ft) must be like 5\'7 or 6\'5.');
            Router::redirect('/teacher/profile');
        }

        $pdo = Db::pdo();
        $uid = Auth::user()['id'];

        // Teachers can update most profile fields but cannot edit full name/email/password.
        $existing = static::teacherRow($uid);
        $fullName = trim((string)($existing['full_name'] ?? (Auth::user()['full_name'] ?? '')));
        if ($fullName === '') {
            $fullName = (string)(Auth::user()['email'] ?? 'Teacher');
        }

        $exists = $pdo->prepare('SELECT user_id FROM teachers WHERE user_id = ? LIMIT 1');
        $exists->execute([$uid]);

        if ($exists->fetch()) {
            $pdo->prepare('UPDATE teachers SET contact_no=?, address=?, date_of_birth=?, place_of_birth=?, age=?, sex=?, bachelor_degree=?, religion=?, blood_type=?, nationality=?, civil_status=?, weight_kg=?, height_ft=?, emergency_contact_name=?, emergency_contact_no=?, emergency_contact_address=?, updated_at=NOW() WHERE user_id=?')
                ->execute([
                    $contactNo,
                    $address,
                    $dateOfBirth !== '' ? $dateOfBirth : null,
                    $placeOfBirth !== '' ? $placeOfBirth : null,
                    $age,
                    $sex !== '' ? $sex : null,
                    $bachelorDegree !== '' ? $bachelorDegree : null,
                    $religion !== '' ? $religion : null,
                    $bloodType !== '' ? $bloodType : null,
                    $nationality !== '' ? $nationality : null,
                    $civilStatus !== '' ? $civilStatus : null,
                    $weightKg !== '' ? $weightKg : null,
                    $heightFt !== '' ? $heightFt : null,
                    $emergencyName !== '' ? $emergencyName : null,
                    $emergencyNo !== '' ? $emergencyNo : null,
                    $emergencyAddress !== '' ? $emergencyAddress : null,
                    $uid
                ]);
        } else {
            $pdo->prepare('INSERT INTO teachers (user_id, full_name, contact_no, address, date_of_birth, place_of_birth, age, sex, bachelor_degree, religion, blood_type, nationality, civil_status, weight_kg, height_ft, emergency_contact_name, emergency_contact_no, emergency_contact_address, created_at, updated_at) VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,NOW(),NOW())')
                ->execute([
                    $uid,
                    $fullName,
                    $contactNo,
                    $address,
                    $dateOfBirth !== '' ? $dateOfBirth : null,
                    $placeOfBirth !== '' ? $placeOfBirth : null,
                    $age,
                    $sex !== '' ? $sex : null,
                    $bachelorDegree !== '' ? $bachelorDegree : null,
                    $religion !== '' ? $religion : null,
                    $bloodType !== '' ? $bloodType : null,
                    $nationality !== '' ? $nationality : null,
                    $civilStatus !== '' ? $civilStatus : null,
                    $weightKg !== '' ? $weightKg : null,
                    $heightFt !== '' ? $heightFt : null,
                    $emergencyName !== '' ? $emergencyName : null,
                    $emergencyNo !== '' ? $emergencyNo : null,
                    $emergencyAddress !== '' ? $emergencyAddress : null,
                ]);
        }

        if (!empty($_FILES['profile_photo']) && is_array($_FILES['profile_photo']) && (int)($_FILES['profile_photo']['error'] ?? UPLOAD_ERR_NO_FILE) !== UPLOAD_ERR_NO_FILE) {
            $destDir = __DIR__ . '/../../storage/uploads/profile/' . $uid;
            $result = Uploader::store($_FILES['profile_photo'], $destDir, ['jpg', 'jpeg', 'png'], 2_000_000);
            if (!$result['ok']) {
                Flash::set('danger', (string)($result['error'] ?? 'Profile photo upload failed.'));
                Router::redirect('/teacher/profile');
            }
            $pdo->prepare('UPDATE teachers SET profile_photo_path=?, updated_at=NOW() WHERE user_id=?')
                ->execute([$result['stored_path'], $uid]);
        }

        static::notifyFocals(
            'teacher_profile_updated',
            'Teacher Profile Updated',
            static::teacherDisplayName($uid) . ' updated its profile information.',
            '/focal/teachers/view?user_id=' . $uid,
            ['teacher_user_id' => $uid]
        );

        Flash::set('success', 'Profile saved.');
        Router::redirect('/teacher/profile');
    }

    public static function apply(): void
    {
        Auth::requireRole('teacher');
        $pdo = Db::pdo();
        $uid = Auth::user()['id'];

        $teacher = static::teacherRow($uid);

        $appId = (int)($_GET['id'] ?? 0);
        $app = null;
        if ($appId > 0) {
            $stmt = $pdo->prepare("SELECT * FROM renewal_applications WHERE id=? AND teacher_user_id=? AND status IN ('draft','submitted') LIMIT 1");
            $stmt->execute([$appId, $uid]);
            $app = $stmt->fetch() ?: null;
        }
        if (!$app) {
            $stmt = $pdo->prepare("SELECT * FROM renewal_applications WHERE teacher_user_id=? AND status IN ('draft','submitted') ORDER BY updated_at DESC, id DESC LIMIT 1");
            $stmt->execute([$uid]);
            $app = $stmt->fetch() ?: null;
        }

        // Restriction: if latest approved exists and expiration date has not occurred, do not allow new application.
        if (!$app) {
            $appr = $pdo->prepare("SELECT id FROM renewal_applications WHERE teacher_user_id=? AND status='approved' ORDER BY reviewed_at DESC, id DESC LIMIT 1");
            $appr->execute([$uid]);
            if ($appr->fetch()) {
                $exp = (string)($teacher['expiration_date'] ?? "");
                if ($exp === "") {
                    Flash::set('danger', "You already have an approved renewal. You can reapply after your expiration date is set/occurs.");
                    Router::redirect('/teacher');
                }
                if ($exp > date('Y-m-d')) {
                    Flash::set('danger', "You already have an approved renewal. You can reapply after your expiration date (" . $exp . ").");
                    Router::redirect('/teacher');
                }
            }
        }

        $schedules = $pdo->query('SELECT * FROM renewal_schedules ORDER BY due_until DESC')->fetchAll();

        $docs = [];
        $present = [];
        if ($app) {
            $d = $pdo->prepare('SELECT * FROM renewal_documents WHERE application_id = ? ORDER BY uploaded_at DESC');
            $d->execute([(int)$app['id']]);
            $docs = $d->fetchAll();
            foreach ($docs as $doc) {
                $present[(string)$doc['doc_type']] = true;
            }
        }

        $missing = $app ? static::missingRequirements((int)$app['id'], $present) : [];

        View::render('teacher/apply', [
            'title' => 'Renewal Application',
            'teacher' => $teacher,
            'schedules' => $schedules,
            'app' => $app,
            'docs' => $docs,
            'present' => $present,
            'missing' => $missing,
        ]);
    }

    public static function saveApplication(): void
    {
        Auth::requireRole('teacher');
        if (!Csrf::validate($_POST['_csrf'] ?? null)) {
            http_response_code(400);
            echo 'Bad Request (CSRF)';
            return;
        }

        $pdo = Db::pdo();
        $uid = Auth::user()['id'];
        $teacher = static::teacherRow($uid);

        $scheduleId = (int)($_POST['schedule_id'] ?? 0);
        if ($scheduleId <= 0) {
            Flash::set('danger', 'Select a renewal schedule.');
            Router::redirect('/teacher/apply');
        }

        $appId = (int)($_POST['app_id'] ?? 0);
        $app = null;
        if ($appId > 0) {
            $stmt = $pdo->prepare("SELECT * FROM renewal_applications WHERE id=? AND teacher_user_id=? AND status IN ('draft','submitted') LIMIT 1");
            $stmt->execute([$appId, $uid]);
            $app = $stmt->fetch() ?: null;
        }
        if (!$app) {
            $stmt = $pdo->prepare("SELECT * FROM renewal_applications WHERE teacher_user_id=? AND status IN ('draft','submitted') ORDER BY updated_at DESC, id DESC LIMIT 1");
            $stmt->execute([$uid]);
            $app = $stmt->fetch() ?: null;
        }

        if (!$app) {
            // Restriction: prevent new application if approved and not expired.
            $appr = $pdo->prepare("SELECT id FROM renewal_applications WHERE teacher_user_id=? AND status='approved' ORDER BY reviewed_at DESC, id DESC LIMIT 1");
            $appr->execute([$uid]);
            if ($appr->fetch()) {
                $exp = (string)($teacher['expiration_date'] ?? "");
                if ($exp === "") {
                    Flash::set('danger', "You already have an approved renewal. You can reapply after your expiration date is set/occurs.");
                    Router::redirect('/teacher');
                }
                if ($exp > date('Y-m-d')) {
                    Flash::set('danger', "You already have an approved renewal. You can reapply after your expiration date (" . $exp . ").");
                    Router::redirect('/teacher');
                }
            }

            // New application must use an OPEN schedule.
            $open = $pdo->prepare('SELECT id FROM renewal_schedules WHERE id=? AND CURDATE() BETWEEN open_from AND due_until LIMIT 1');
            $open->execute([$scheduleId]);
            if (!$open->fetch()) {
                Flash::set('danger', 'That schedule is not open. Please select an open schedule.');
                Router::redirect('/teacher/apply');
            }

            $pdo->prepare("INSERT INTO renewal_applications (teacher_user_id, schedule_id, status, created_at, updated_at) VALUES (?, ?, 'draft', NOW(), NOW())")
                ->execute([$uid, $scheduleId]);
            $appId = (int)$pdo->lastInsertId();
        } else {
            $appId = (int)$app['id'];
            $pdo->prepare('UPDATE renewal_applications SET schedule_id=?, updated_at=NOW() WHERE id=? AND teacher_user_id=?')
                ->execute([$scheduleId, $appId, $uid]);
        }

        $destDir = __DIR__ . '/../../storage/uploads/' . $uid . '/' . $appId;
        $uploadedAny = false;
        $wasSubmitted = is_array($app) && (string)($app['status'] ?? '') === 'submitted';

        foreach (static::requirementUploadMap() as $docType => $fieldName) {
            if (empty($_FILES[$fieldName]) || !is_array($_FILES[$fieldName])) {
                continue;
            }
            if ((int)($_FILES[$fieldName]['error'] ?? UPLOAD_ERR_NO_FILE) === UPLOAD_ERR_NO_FILE) {
                continue;
            }

            $result = Uploader::store($_FILES[$fieldName], $destDir);
            if (!$result['ok']) {
                Flash::set('danger', (string)($result['error'] ?? 'Upload failed.'));
                Router::redirect('/teacher/apply?id=' . $appId);
            }

            $pdo->prepare('INSERT INTO renewal_documents (application_id, doc_type, original_name, stored_path, mime, size_bytes, uploaded_at)
                           VALUES (?, ?, ?, ?, ?, ?, NOW())')
                ->execute([
                    $appId,
                    $docType,
                    $result['original_name'],
                    $result['stored_path'],
                    $result['mime'],
                    (int)($result['size'] ?? 0),
                ]);
            $uploadedAny = true;
        }

        // Valid ID (one of)
        if (!empty($_FILES['req_valid_id']) && is_array($_FILES['req_valid_id']) && (int)($_FILES['req_valid_id']['error'] ?? UPLOAD_ERR_NO_FILE) !== UPLOAD_ERR_NO_FILE) {
            $type = (string)($_POST['valid_id_type'] ?? '');
            $docType = static::validIdTypeToDocType($type);
            if ($docType === null) {
                Flash::set('danger', 'Select a valid ID type.');
                Router::redirect('/teacher/apply?id=' . $appId);
            }

            $result = Uploader::store($_FILES['req_valid_id'], $destDir);
            if (!$result['ok']) {
                Flash::set('danger', (string)($result['error'] ?? 'Upload failed.'));
                Router::redirect('/teacher/apply?id=' . $appId);
            }

            $pdo->prepare('INSERT INTO renewal_documents (application_id, doc_type, original_name, stored_path, mime, size_bytes, uploaded_at)
                           VALUES (?, ?, ?, ?, ?, ?, NOW())')
                ->execute([
                    $appId,
                    $docType,
                    $result['original_name'],
                    $result['stored_path'],
                    $result['mime'],
                    (int)($result['size'] ?? 0),
                ]);
            $uploadedAny = true;
        }

        if ($uploadedAny && $wasSubmitted) {
            static::notifyFocals(
                'submitted_requirements_updated',
                'Submitted Requirements Updated',
                static::teacherDisplayName($uid) . ' updated submitted requirements for application #' . $appId . '.',
                '/focal/applications/view?id=' . $appId,
                ['teacher_user_id' => $uid, 'application_id' => $appId]
            );
        }

        Flash::set('success', $uploadedAny ? 'Requirements uploaded.' : 'Application saved.');
        Router::redirect('/teacher/apply?id=' . $appId);
    }

    public static function submitApplication(): void
    {
        Auth::requireRole('teacher');
        if (!Csrf::validate($_POST['_csrf'] ?? null)) {
            http_response_code(400);
            echo 'Bad Request (CSRF)';
            return;
        }

        $pdo = Db::pdo();
        $uid = Auth::user()['id'];

        $appId = (int)($_POST['app_id'] ?? 0);
        if ($appId <= 0) {
            Flash::set('danger', 'Application not found.');
            Router::redirect('/teacher/apply');
        }

        $stmt = $pdo->prepare("SELECT * FROM renewal_applications WHERE id=? AND teacher_user_id=? AND status IN ('draft','submitted') LIMIT 1");
        $stmt->execute([$appId, $uid]);
        $app = $stmt->fetch();
        if (!$app) {
            Flash::set('danger', 'Application not found.');
            Router::redirect('/teacher/apply');
        }

        $typesStmt = $pdo->prepare('SELECT DISTINCT doc_type FROM renewal_documents WHERE application_id=?');
        $typesStmt->execute([$appId]);
        $present = [];
        foreach ($typesStmt->fetchAll() as $r) {
            $present[(string)$r['doc_type']] = true;
        }

        $missing = static::missingRequirements($appId, $present);
        if ($missing) {
            Flash::set('danger', 'Missing requirements: ' . implode(', ', $missing) . '.');
            Router::redirect('/teacher/apply?id=' . $appId);
        }

        if ((string)$app['status'] === 'draft') {
            $pdo->prepare("UPDATE renewal_applications SET status='submitted', submitted_at=NOW(), updated_at=NOW() WHERE id=? AND teacher_user_id=?")
                ->execute([$appId, $uid]);

            Notifier::notify(
                $uid,
                'application_submitted',
                'Renewal Application Submitted',
                "You have submitted a renewal application, your renewal status is pending for the ECCD Focal's decision",
                '/teacher/applications',
                json_encode(['application_id' => $appId, 'status' => 'submitted'], JSON_UNESCAPED_SLASHES)
            );

            static::notifyFocals(
                'teacher_application_submitted',
                'Teacher Submitted Application',
                static::teacherDisplayName($uid) . ' submitted renewal application #' . $appId . '.',
                '/focal/applications/view?id=' . $appId,
                ['teacher_user_id' => $uid, 'application_id' => $appId, 'status' => 'submitted']
            );

            Flash::set('success', 'Application submitted. You can still edit it while it is pending.');
        } else {
            $pdo->prepare('UPDATE renewal_applications SET updated_at=NOW() WHERE id=? AND teacher_user_id=?')
                ->execute([$appId, $uid]);
            Flash::set('success', 'Application updated.');
        }

        Router::redirect('/teacher/applications');
    }

    public static function cancelApplication(): void
    {
        Auth::requireRole('teacher');
        if (!Csrf::validate($_POST['_csrf'] ?? null)) {
            http_response_code(400);
            echo 'Bad Request (CSRF)';
            return;
        }

        $pdo = Db::pdo();
        $uid = Auth::user()['id'];
        $appId = (int)($_POST['app_id'] ?? 0);
        if ($appId <= 0) {
            Router::redirect('/teacher/applications');
        }

        $stmt = $pdo->prepare("SELECT id, status FROM renewal_applications WHERE id=? AND teacher_user_id=? LIMIT 1");
        $stmt->execute([$appId, $uid]);
        $app = $stmt->fetch();
        if (!$app || (string)$app['status'] !== 'submitted') {
            Flash::set('danger', 'Only submitted applications can be cancelled.');
            Router::redirect('/teacher/applications');
        }

        $pdo->prepare("UPDATE renewal_applications SET status='cancelled', cancelled_at=NOW(), updated_at=NOW() WHERE id=? AND teacher_user_id=?")
            ->execute([$appId, $uid]);

        static::notifyFocals(
            'teacher_application_cancelled',
            'Teacher Cancelled Application',
            static::teacherDisplayName($uid) . ' cancelled renewal application #' . $appId . '.',
            '/focal/applications/view?id=' . $appId,
            ['teacher_user_id' => $uid, 'application_id' => $appId, 'status' => 'cancelled']
        );

        Flash::set('success', 'Application cancelled.');
        Router::redirect('/teacher/applications');
    }

    public static function applications(): void
    {
        Auth::requireRole('teacher');
        $pdo = Db::pdo();
        $uid = Auth::user()['id'];
        $stmt = $pdo->prepare('SELECT a.*, s.title AS schedule_title FROM renewal_applications a LEFT JOIN renewal_schedules s ON s.id = a.schedule_id WHERE a.teacher_user_id = ? ORDER BY a.created_at DESC');
        $stmt->execute([$uid]);
        $apps = $stmt->fetchAll();

        View::render('teacher/applications', ['title' => 'My Applications', 'apps' => $apps]);
    }

    public static function viewApplication(): void
    {
        Auth::requireRole('teacher');
        $pdo = Db::pdo();
        $uid = Auth::user()['id'];
        $appId = (int)($_GET['id'] ?? 0);
        if ($appId <= 0) {
            Router::redirect('/teacher/applications');
        }

        $stmt = $pdo->prepare('SELECT a.*, t.full_name, t.expiration_date, u.email, s.title AS schedule_title FROM renewal_applications a JOIN users u ON u.id = a.teacher_user_id LEFT JOIN teachers t ON t.user_id = u.id LEFT JOIN renewal_schedules s ON s.id = a.schedule_id WHERE a.id = ? AND a.teacher_user_id = ? LIMIT 1');
        $stmt->execute([$appId, $uid]);
        $app = $stmt->fetch();
        if (!$app) {
            Router::redirect('/teacher/applications');
        }

        $docsStmt = $pdo->prepare('SELECT * FROM renewal_documents WHERE application_id = ? ORDER BY uploaded_at DESC');
        $docsStmt->execute([$appId]);
        $docs = $docsStmt->fetchAll();

        View::render('teacher/application_view', [
            'title' => 'Review Application #' . (int)$app['id'],
            'app' => $app,
            'docs' => $docs,
        ]);
    }

    public static function download(): void
    {
        static::serveDoc((int)($_GET['id'] ?? 0), true);
    }

    public static function viewInline(): void
    {
        static::serveDoc((int)($_GET['id'] ?? 0), false);
    }

    private static function serveDoc(int $docId, bool $asAttachment): void
    {
        Auth::requireLogin();
        if ($docId <= 0) {
            http_response_code(404);
            echo 'Not found';
            return;
        }

        $pdo = Db::pdo();
        $stmt = $pdo->prepare('SELECT d.*, a.teacher_user_id FROM renewal_documents d JOIN renewal_applications a ON a.id = d.application_id WHERE d.id = ? LIMIT 1');
        $stmt->execute([$docId]);
        $doc = $stmt->fetch();
        if (!$doc) {
            http_response_code(404);
            echo 'Not found';
            return;
        }

        $u = Auth::user();
        $isTeacherOwner = $u['role'] === 'teacher' && (int)$doc['teacher_user_id'] === (int)$u['id'];
        $isFocal = $u['role'] === 'focal';
        if (!$isTeacherOwner && !$isFocal) {
            http_response_code(403);
            echo 'Forbidden';
            return;
        }

        $path = (string)$doc['stored_path'];
        if (!is_file($path)) {
            http_response_code(404);
            echo 'File missing on server';
            return;
        }

        header('X-Frame-Options: SAMEORIGIN');
        header('Content-Type: ' . ((string)$doc['mime'] ?: 'application/octet-stream'));
        header('Content-Length: ' . (string)filesize($path));
        $name = (string)($doc['original_name'] ?? 'document');
        $disp = $asAttachment ? 'attachment' : 'inline';
        header('Content-Disposition: ' . $disp . '; filename="' . addslashes($name) . '"');
        readfile($path);
        exit;
    }

    public static function profilePhoto(): void
    {
        Auth::requireLogin();
        $targetUserId = (int)($_GET['user_id'] ?? 0);
        if ($targetUserId <= 0) {
            http_response_code(404);
            echo 'Not found';
            return;
        }

        $u = Auth::user();
        $isSelf = $u['role'] === 'teacher' && (int)$u['id'] === $targetUserId;
        $isFocal = $u['role'] === 'focal';
        if (!$isSelf && !$isFocal) {
            http_response_code(403);
            echo 'Forbidden';
            return;
        }

        $pdo = Db::pdo();
        $roleStmt = $pdo->prepare('SELECT role FROM users WHERE id = ? LIMIT 1');
        $roleStmt->execute([$targetUserId]);
        $targetRole = (string)($roleStmt->fetchColumn() ?: '');
        if ($targetRole === '') {
            http_response_code(404);
            echo 'Not found';
            return;
        }

        $table = $targetRole === 'focal' ? 'focal' : 'teachers';
        $stmt = $pdo->prepare("SELECT profile_photo_path FROM {$table} WHERE user_id = ? LIMIT 1");
        $stmt->execute([$targetUserId]);
        $row = $stmt->fetch();
        $path = (string)($row['profile_photo_path'] ?? '');
        if ($path === '' || !is_file($path)) {
            http_response_code(404);
            echo 'Not found';
            return;
        }

        $mime = function_exists('mime_content_type') ? (string)mime_content_type($path) : 'image/jpeg';
        header('Content-Type: ' . ($mime ?: 'image/jpeg'));
        header('Content-Length: ' . (string)filesize($path));
        readfile($path);
        exit;
    }

    /** @return array<string, mixed> */
    private static function teacherRow(int $userId): array
    {
        $pdo = Db::pdo();
        $stmt = $pdo->prepare('SELECT * FROM teachers WHERE user_id = ? LIMIT 1');
        $stmt->execute([$userId]);
        $row = $stmt->fetch();
        return $row ?: [
            'user_id' => $userId,
            'full_name' => Auth::user()['full_name'],
            'contact_no' => '',
            'address' => '',
            'assigned_center' => '',
            'expiration_date' => null,
            'profile_photo_path' => null,
        ];
    }

    /** @param array<string, mixed> $meta */
    private static function notifyFocals(string $type, string $title, string $body, string $linkUrl, array $meta = []): void
    {
        $pdo = Db::pdo();
        $stmt = $pdo->query("SELECT id FROM users WHERE role = 'focal'");
        $focalIds = $stmt ? $stmt->fetchAll() : [];
        $metaJson = $meta === [] ? null : json_encode($meta, JSON_UNESCAPED_SLASHES);

        foreach ($focalIds as $row) {
            $focalId = (int)(is_array($row) ? ($row['id'] ?? 0) : 0);
            if ($focalId <= 0) {
                continue;
            }

            Notifier::notify($focalId, $type, $title, $body, $linkUrl, $metaJson ?: null);
        }
    }

    private static function teacherDisplayName(int $userId): string
    {
        $teacher = static::teacherRow($userId);
        $name = trim((string)($teacher['full_name'] ?? ''));
        if ($name !== '') {
            return $name;
        }

        $pdo = Db::pdo();
        $stmt = $pdo->prepare('SELECT email FROM users WHERE id = ? LIMIT 1');
        $stmt->execute([$userId]);
        $email = trim((string)($stmt->fetchColumn() ?: ''));

        return $email !== '' ? $email : 'A teacher';
    }

    /** @return array<string, string> doc_type => field_name */
    private static function requirementUploadMap(): array
    {
        return [
            'birth_certificate' => 'req_birth_certificate',
            'medical_certificate' => 'req_medical_certificate',
            'transcript_of_records' => 'req_transcript_of_records',
            'accomplishment_report' => 'req_accomplishment_report',
            'seminars_training' => 'req_seminars_training',
            'certificates' => 'req_certificates',
            'cedula' => 'req_cedula',
        ];
    }

    /** @return array<string, string> */
    private static function requirementNames(): array
    {
        return [
            'birth_certificate' => 'Birth Certificate',
            'medical_certificate' => 'Medical Certificate',
            'transcript_of_records' => 'Transcript of Records',
            'accomplishment_report' => 'Accomplishment Report',
            'seminars_training' => 'Seminars/Training',
            'certificates' => 'Certificates',
            'cedula' => 'Cedula',
        ];
    }

    /** @return array<string, string> */
    private static function validIdNames(): array
    {
        return [
            'valid_id_national_id' => 'National ID',
            'valid_id_drivers_license' => "Driver's License",
            'valid_id_passport' => 'Passport',
            'valid_id_sss_id' => 'SSS ID',
        ];
    }

    private static function validIdTypeToDocType(string $type): ?string
    {
        return match ($type) {
            'national_id' => 'valid_id_national_id',
            'drivers_license' => 'valid_id_drivers_license',
            'passport' => 'valid_id_passport',
            'sss_id' => 'valid_id_sss_id',
            default => null,
        };
    }

    /** @param array<string, bool> $present */
    private static function missingRequirements(int $appId, array $present): array
    {
        $missing = [];
        foreach (static::requirementNames() as $docType => $label) {
            if (empty($present[$docType])) {
                $missing[] = $label;
            }
        }

        $hasAnyValidId = false;
        foreach (array_keys(static::validIdNames()) as $docType) {
            if (!empty($present[$docType])) {
                $hasAnyValidId = true;
                break;
            }
        }
        if (!$hasAnyValidId) {
            $missing[] = 'Valid ID (choose 1: National ID, Driver\'s License, Passport, SSS ID)';
        }

        return $missing;
    }
}
