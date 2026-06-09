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

final class FocalController
{
    public static function profile(): void
    {
        Auth::requireRole('focal');

        View::render('focal/profile', [
            'title' => 'My Profile',
            'profile' => static::profileRow((int)Auth::user()['id']),
        ]);
    }

    public static function saveProfile(): void
    {
        Auth::requireRole('focal');
        if (!Csrf::validate($_POST['_csrf'] ?? null)) {
            http_response_code(400);
            echo 'Bad Request (CSRF)';
            return;
        }

        $fullName = trim((string)($_POST['full_name'] ?? ''));
        $contactNo = trim((string)($_POST['contact_no'] ?? ''));
        $password = (string)($_POST['password'] ?? '');
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

        if ($fullName === '' || !Validator::maxLen($fullName, 120)) {
            Flash::set('danger', 'Full name is required (max 120).');
            Router::redirect('/focal/profile');
        }
        if ($contactNo !== '' && !preg_match('/^\d+$/', $contactNo)) {
            Flash::set('danger', 'Contact No. must contain numbers only.');
            Router::redirect('/focal/profile');
        }
        if ($password !== '' && !static::strongPassword($password)) {
            Flash::set('danger', 'Password must be at least 12 characters and include uppercase letters, lowercase letters, numbers, and special characters.');
            Router::redirect('/focal/profile');
        }
        if ($emergencyNo !== '' && !preg_match('/^\d+$/', $emergencyNo)) {
            Flash::set('danger', 'Emergency contact number must contain numbers only.');
            Router::redirect('/focal/profile');
        }
        if ($dateOfBirth !== '' && !Validator::dateYmd($dateOfBirth)) {
            Flash::set('danger', 'Date of Birth must be a valid date (YYYY-MM-DD).');
            Router::redirect('/focal/profile');
        }

        $age = null;
        if ($ageRaw !== '') {
            if (!preg_match('/^\d{1,3}$/', $ageRaw)) {
                Flash::set('danger', 'Age must be a number.');
                Router::redirect('/focal/profile');
            }
            $age = (int)$ageRaw;
            if ($age < 0 || $age > 120) {
                Flash::set('danger', 'Age must be between 0 and 120.');
                Router::redirect('/focal/profile');
            }
        }
        if ($sex !== '' && !in_array($sex, ['Male', 'Female'], true)) {
            Flash::set('danger', 'Sex must be Male or Female.');
            Router::redirect('/focal/profile');
        }
        if ($weightKg !== '' && !preg_match('/^\d{1,3}(\.\d{1,2})?$/', $weightKg)) {
            Flash::set('danger', 'Weight (kg) must be numeric (e.g. 52 or 52.5).');
            Router::redirect('/focal/profile');
        }
        if ($heightFt !== '' && !preg_match("/^\d{1,2}'\d{1,2}$/", $heightFt)) {
            Flash::set('danger', 'Height (ft) must be like 5\'7 or 6\'5.');
            Router::redirect('/focal/profile');
        }

        $pdo = Db::pdo();
        $uid = (int)Auth::user()['id'];

        $pdo->prepare('INSERT INTO focal (user_id, full_name, contact_no, address, date_of_birth, place_of_birth, age, sex, bachelor_degree, religion, blood_type, nationality, civil_status, weight_kg, height_ft, emergency_contact_name, emergency_contact_no, emergency_contact_address, created_at, updated_at) VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,NOW(),NOW()) ON DUPLICATE KEY UPDATE full_name=VALUES(full_name), contact_no=VALUES(contact_no), address=VALUES(address), date_of_birth=VALUES(date_of_birth), place_of_birth=VALUES(place_of_birth), age=VALUES(age), sex=VALUES(sex), bachelor_degree=VALUES(bachelor_degree), religion=VALUES(religion), blood_type=VALUES(blood_type), nationality=VALUES(nationality), civil_status=VALUES(civil_status), weight_kg=VALUES(weight_kg), height_ft=VALUES(height_ft), emergency_contact_name=VALUES(emergency_contact_name), emergency_contact_no=VALUES(emergency_contact_no), emergency_contact_address=VALUES(emergency_contact_address), updated_at=NOW()')
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

        if (!empty($_FILES['profile_photo']) && is_array($_FILES['profile_photo']) && (int)($_FILES['profile_photo']['error'] ?? UPLOAD_ERR_NO_FILE) !== UPLOAD_ERR_NO_FILE) {
            $destDir = __DIR__ . '/../../storage/uploads/profile/' . $uid;
            $result = Uploader::store($_FILES['profile_photo'], $destDir, ['jpg', 'jpeg', 'png'], 2_000_000);
            if (!$result['ok']) {
                Flash::set('danger', (string)($result['error'] ?? 'Profile photo upload failed.'));
                Router::redirect('/focal/profile');
            }
            $pdo->prepare('UPDATE focal SET profile_photo_path=?, updated_at=NOW() WHERE user_id=?')
                ->execute([$result['stored_path'], $uid]);
        }

        if ($password !== '') {
            $pdo->prepare('UPDATE users SET password_hash=?, updated_at=NOW() WHERE id=? AND role="focal"')
                ->execute([password_hash($password, PASSWORD_DEFAULT), $uid]);
        }

        Flash::set('success', 'Profile saved.');
        Router::redirect('/focal/profile');
    }

    public static function dashboard(): void
    {
        Auth::requireRole('focal');
        $pdo = Db::pdo();

        $pending = (int)($pdo->query("SELECT COUNT(*) AS c FROM renewal_applications WHERE status='submitted'")->fetch()['c'] ?? 0);
        $approved = (int)($pdo->query("SELECT COUNT(*) AS c FROM renewal_applications WHERE status='approved'")->fetch()['c'] ?? 0);
        $rejected = (int)($pdo->query("SELECT COUNT(*) AS c FROM renewal_applications WHERE status='rejected'")->fetch()['c'] ?? 0);
        $totalTeachers = (int)($pdo->query("SELECT COUNT(*) AS c FROM users WHERE role='teacher'")->fetch()['c'] ?? 0);
        $nextExpirations = $pdo->query("SELECT teacher_uid, full_name, expiration_date, DATEDIFF(expiration_date, CURDATE()) AS days_left FROM teachers WHERE expiration_date IS NOT NULL ORDER BY expiration_date ASC LIMIT 5")->fetchAll();

        View::render('focal/dashboard', [
            'title' => 'ECCD Focal Dashboard',
            'pending' => $pending,
            'approved' => $approved,
            'rejected' => $rejected,
            'totalTeachers' => $totalTeachers,
            'nextExpirations' => $nextExpirations,
        ]);
    }

    public static function teachers(): void
    {
        Auth::requireRole('focal');
        $pdo = Db::pdo();
        $rows = $pdo->query("SELECT u.id AS user_id, u.email, t.teacher_uid, t.full_name, t.contact_no, t.assigned_center, t.expiration_date, DATEDIFF(t.expiration_date, CURDATE()) AS expiring_days, t.profile_photo_path, EXISTS(SELECT 1 FROM renewal_applications a WHERE a.teacher_user_id = u.id AND a.status = 'approved') AS has_approved_renewal FROM users u LEFT JOIN teachers t ON t.user_id = u.id WHERE u.role = 'teacher' ORDER BY COALESCE(t.full_name, u.email) ASC")->fetchAll();

        View::render('focal/teachers', ['title' => 'Teachers', 'teachers' => $rows]);
    }

    public static function createTeacher(): void
    {
        Auth::requireRole('focal');
        if (!Csrf::validate($_POST['_csrf'] ?? null)) {
            http_response_code(400);
            echo 'Bad Request (CSRF)';
            return;
        }

        $fullName = trim((string)($_POST['full_name'] ?? ''));
        $teacherUid = trim((string)($_POST['teacher_uid'] ?? ''));
        $dob = trim((string)($_POST['date_of_birth'] ?? ''));
        $age = (int)($_POST['age'] ?? 0);
        $sex = (string)($_POST['sex'] ?? '');
        $address = trim((string)($_POST['address'] ?? ''));
        $assignedCenter = trim((string)($_POST['assigned_center'] ?? ''));
        $email = trim((string)($_POST['email'] ?? ''));
        $password = (string)($_POST['password'] ?? '');

        if ($fullName === '' || !Validator::maxLen($fullName, 120)) {
            Flash::set('danger', 'Full name is required (max 120).');
            Router::redirect('/focal/teachers');
        }
        if ($teacherUid === '' || !Validator::maxLen($teacherUid, 50)) {
            Flash::set('danger', 'Teacher ID is required (max 50).');
            Router::redirect('/focal/teachers');
        }
        if (!Validator::dateYmd($dob)) {
            Flash::set('danger', 'Date of Birth is required.');
            Router::redirect('/focal/teachers');
        }
        if ($age < 18 || $age > 80) {
            Flash::set('danger', 'Age must be between 18 and 80.');
            Router::redirect('/focal/teachers');
        }
        if (!in_array($sex, ['Male', 'Female'], true)) {
            Flash::set('danger', 'Sex must be Male or Female.');
            Router::redirect('/focal/teachers');
        }
        if ($address === '' || !Validator::maxLen($address, 180)) {
            Flash::set('danger', 'Address is required (max 180).');
            Router::redirect('/focal/teachers');
        }
        if ($assignedCenter === '' || !Validator::maxLen($assignedCenter, 120)) {
            Flash::set('danger', 'Assigned Child Development Center is required (max 120).');
            Router::redirect('/focal/teachers');
        }
        if (!Validator::email($email)) {
            Flash::set('danger', 'Enter a valid email.');
            Router::redirect('/focal/teachers');
        }
        if (!static::strongPassword($password)) {
            Flash::set('danger', 'Password must be at least 12 characters and include uppercase letters, lowercase letters, numbers, and special characters.');
            Router::redirect('/focal/teachers');
        }

        $pdo = Db::pdo();

        $existsEmail = $pdo->prepare('SELECT id FROM users WHERE email=? LIMIT 1');
        $existsEmail->execute([$email]);
        if ($existsEmail->fetch()) {
            Flash::set('danger', 'Email already exists.');
            Router::redirect('/focal/teachers');
        }

        $existsUid = $pdo->prepare('SELECT user_id FROM teachers WHERE teacher_uid=? LIMIT 1');
        $existsUid->execute([$teacherUid]);
        if ($existsUid->fetch()) {
            Flash::set('danger', 'Teacher ID already exists.');
            Router::redirect('/focal/teachers');
        }

        $pdo->beginTransaction();
        try {
            $pdo->prepare('INSERT INTO users (email, password_hash, role, created_at) VALUES (?,?,"teacher",NOW())')
                ->execute([$email, password_hash($password, PASSWORD_DEFAULT)]);
            $userId = (int)$pdo->lastInsertId();

            $pdo->prepare('INSERT INTO teachers (user_id, teacher_uid, full_name, date_of_birth, age, sex, address, assigned_center, created_at, updated_at) VALUES (?,?,?,?,?,?,?,?,NOW(),NOW())')
                ->execute([$userId, $teacherUid, $fullName, $dob, $age, $sex, $address, $assignedCenter]);

            $pdo->commit();
            Flash::set('success', 'Teacher account created.');
        } catch (\Throwable $e) {
            $pdo->rollBack();
            Flash::set('danger', 'Could not create account: ' . $e->getMessage());
        }

        Router::redirect('/focal/teachers');
    }
    public static function viewTeacher(): void
    {
        Auth::requireRole('focal');
        $userId = (int)($_GET['user_id'] ?? 0);
        if ($userId <= 0) {
            Router::redirect('/focal/teachers');
        }

        $pdo = Db::pdo();
        $u = $pdo->prepare('SELECT id, email FROM users WHERE id=? AND role="teacher" LIMIT 1');
        $u->execute([$userId]);
        $teacherUser = $u->fetch();
        if (!$teacherUser) {
            Router::redirect('/focal/teachers');
        }

        $t = $pdo->prepare('SELECT * FROM teachers WHERE user_id=? LIMIT 1');
        $t->execute([$userId]);
        $teacher = $t->fetch() ?: ['user_id' => $userId];


        $docsStmt = $pdo->prepare(
            "SELECT d.id, d.doc_type, d.original_name, d.uploaded_at, a.id AS application_id, a.reviewed_at\n" .
            "FROM renewal_documents d\n" .
            "JOIN renewal_applications a ON a.id = d.application_id\n" .
            "WHERE a.teacher_user_id = ? AND a.status = 'approved'\n" .
            "ORDER BY COALESCE(a.reviewed_at, d.uploaded_at) DESC, d.uploaded_at DESC, d.id DESC"
        );
        $docsStmt->execute([$userId]);
        $approvedDocuments = $docsStmt->fetchAll();

        View::render('focal/teacher_view', [
            'title' => 'Teacher Details',
            'teacherUser' => $teacherUser,
            'teacher' => $teacher,
            'approvedDocuments' => $approvedDocuments,
        ]);
    }

    public static function updateTeacher(): void
    {
        Auth::requireRole('focal');
        if (!Csrf::validate($_POST['_csrf'] ?? null)) {
            http_response_code(400);
            echo 'Bad Request (CSRF)';
            return;
        }

        $userId = (int)($_POST['user_id'] ?? 0);
        if ($userId <= 0) {
            Router::redirect('/focal/teachers');
        }

        $data = [
            'full_name' => trim((string)($_POST['full_name'] ?? '')),
            'email' => trim((string)($_POST['email'] ?? '')),
            'teacher_uid' => trim((string)($_POST['teacher_uid'] ?? '')),
            'assigned_center' => trim((string)($_POST['assigned_center'] ?? '')),
            'date_of_birth' => trim((string)($_POST['date_of_birth'] ?? '')),
            'age' => (int)($_POST['age'] ?? 0),
            'sex' => (string)($_POST['sex'] ?? ''),
            'address' => trim((string)($_POST['address'] ?? '')),
            'contact_no' => trim((string)($_POST['contact_no'] ?? '')),
            'place_of_birth' => trim((string)($_POST['place_of_birth'] ?? '')),
            'bachelor_degree' => trim((string)($_POST['bachelor_degree'] ?? '')),
            'religion' => trim((string)($_POST['religion'] ?? '')),
            'blood_type' => trim((string)($_POST['blood_type'] ?? '')),
            'nationality' => trim((string)($_POST['nationality'] ?? '')),
            'civil_status' => trim((string)($_POST['civil_status'] ?? '')),
            'weight_kg' => trim((string)($_POST['weight_kg'] ?? '')),
            'height_ft' => trim((string)($_POST['height_ft'] ?? '')),
            'emergency_contact_name' => trim((string)($_POST['emergency_contact_name'] ?? '')),
            'emergency_contact_no' => trim((string)($_POST['emergency_contact_no'] ?? '')),
            'emergency_contact_address' => trim((string)($_POST['emergency_contact_address'] ?? '')),
        ];

        if ($data['full_name'] === '' || !Validator::maxLen($data['full_name'], 120)) {
            Flash::set('danger', 'Full name is required (max 120).');
            Router::redirect('/focal/teachers/view?user_id=' . $userId);
        }
        if (!Validator::email($data['email']) || !Validator::maxLen($data['email'], 120)) {
            Flash::set('danger', 'Enter a valid email (max 120).');
            Router::redirect('/focal/teachers/view?user_id=' . $userId);
        }
        if ($data['teacher_uid'] === '' || !Validator::maxLen($data['teacher_uid'], 50)) {
            Flash::set('danger', 'Teacher ID is required (max 50).');
            Router::redirect('/focal/teachers/view?user_id=' . $userId);
        }
        if (!Validator::maxLen($data['assigned_center'], 120)) {
            Flash::set('danger', 'Assigned Child Development Center must be 120 characters or fewer.');
            Router::redirect('/focal/teachers/view?user_id=' . $userId);
        }
        if ($data['date_of_birth'] !== '' && !Validator::dateYmd($data['date_of_birth'])) {
            Flash::set('danger', 'Invalid Date of Birth.');
            Router::redirect('/focal/teachers/view?user_id=' . $userId);
        }
        if ($data['date_of_birth'] !== '') {
            $dobDate = new \DateTimeImmutable($data['date_of_birth']);
            $today = new \DateTimeImmutable('today');
            $data['age'] = $today->diff($dobDate)->y;
        } else {
            $data['age'] = 0;
        }
        if ($data['age'] !== 0 && ($data['age'] < 0 || $data['age'] > 120)) {
            Flash::set('danger', 'Invalid age.');
            Router::redirect('/focal/teachers/view?user_id=' . $userId);
        }
        if ($data['sex'] !== '' && !in_array($data['sex'], ['Male', 'Female'], true)) {
            Flash::set('danger', 'Invalid sex.');
            Router::redirect('/focal/teachers/view?user_id=' . $userId);
        }
        if ($data['contact_no'] !== '' && !preg_match('/^\d+$/', $data['contact_no'])) {
            Flash::set('danger', 'Contact No. must contain numbers only.');
            Router::redirect('/focal/teachers/view?user_id=' . $userId);
        }
        if ($data['weight_kg'] !== '' && !preg_match('/^\d{1,3}(\.\d{1,2})?$/', $data['weight_kg'])) {
            Flash::set('danger', 'Weight must be like 55 or 55.5 (kg).');
            Router::redirect('/focal/teachers/view?user_id=' . $userId);
        }
        if ($data['height_ft'] !== '' && !preg_match("/^\d{1,2}'\d{1,2}$/", $data['height_ft'])) {
            Flash::set('danger', 'Height must be like 5\'7 or 6\'5.');
            Router::redirect('/focal/teachers/view?user_id=' . $userId);
        }
        if ($data['emergency_contact_no'] !== '' && !preg_match('/^\d+$/', $data['emergency_contact_no'])) {
            Flash::set('danger', 'Emergency contact number must contain numbers only.');
            Router::redirect('/focal/teachers/view?user_id=' . $userId);
        }

        $pdo = Db::pdo();

        $userStmt = $pdo->prepare('SELECT id FROM users WHERE id=? AND role="teacher" LIMIT 1');
        $userStmt->execute([$userId]);
        if (!$userStmt->fetch()) {
            Flash::set('danger', 'Teacher account was not found.');
            Router::redirect('/focal/teachers');
        }

        $emailStmt = $pdo->prepare('SELECT id FROM users WHERE email=? AND id<>? LIMIT 1');
        $emailStmt->execute([$data['email'], $userId]);
        if ($emailStmt->fetch()) {
            Flash::set('danger', 'Email already exists.');
            Router::redirect('/focal/teachers/view?user_id=' . $userId);
        }

        $teacherUidStmt = $pdo->prepare('SELECT user_id FROM teachers WHERE teacher_uid=? AND user_id<>? LIMIT 1');
        $teacherUidStmt->execute([$data['teacher_uid'], $userId]);
        if ($teacherUidStmt->fetch()) {
            Flash::set('danger', 'Teacher ID already exists.');
            Router::redirect('/focal/teachers/view?user_id=' . $userId);
        }

        $pdo->beginTransaction();
        try {
            $pdo->prepare('UPDATE users SET email=?, updated_at=NOW() WHERE id=? AND role="teacher"')
                ->execute([$data['email'], $userId]);

            $pdo->prepare('INSERT INTO teachers (user_id, teacher_uid, full_name, assigned_center, date_of_birth, age, sex, address, contact_no, place_of_birth, bachelor_degree, religion, blood_type, nationality, civil_status, weight_kg, height_ft, emergency_contact_name, emergency_contact_no, emergency_contact_address, created_at, updated_at) VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,NOW(),NOW()) ON DUPLICATE KEY UPDATE teacher_uid=VALUES(teacher_uid), full_name=VALUES(full_name), assigned_center=VALUES(assigned_center), date_of_birth=VALUES(date_of_birth), age=VALUES(age), sex=VALUES(sex), address=VALUES(address), contact_no=VALUES(contact_no), place_of_birth=VALUES(place_of_birth), bachelor_degree=VALUES(bachelor_degree), religion=VALUES(religion), blood_type=VALUES(blood_type), nationality=VALUES(nationality), civil_status=VALUES(civil_status), weight_kg=VALUES(weight_kg), height_ft=VALUES(height_ft), emergency_contact_name=VALUES(emergency_contact_name), emergency_contact_no=VALUES(emergency_contact_no), emergency_contact_address=VALUES(emergency_contact_address), updated_at=NOW()')
                ->execute([
                    $userId,
                    $data['teacher_uid'],
                    $data['full_name'],
                    $data['assigned_center'] !== '' ? $data['assigned_center'] : null,
                    $data['date_of_birth'] !== '' ? $data['date_of_birth'] : null,
                    $data['age'] !== 0 ? $data['age'] : null,
                    $data['sex'] !== '' ? $data['sex'] : null,
                    $data['address'] !== '' ? $data['address'] : null,
                    $data['contact_no'] !== '' ? $data['contact_no'] : null,
                    $data['place_of_birth'] !== '' ? $data['place_of_birth'] : null,
                    $data['bachelor_degree'] !== '' ? $data['bachelor_degree'] : null,
                    $data['religion'] !== '' ? $data['religion'] : null,
                    $data['blood_type'] !== '' ? $data['blood_type'] : null,
                    $data['nationality'] !== '' ? $data['nationality'] : null,
                    $data['civil_status'] !== '' ? $data['civil_status'] : null,
                    $data['weight_kg'] !== '' ? $data['weight_kg'] : null,
                    $data['height_ft'] !== '' ? $data['height_ft'] : null,
                    $data['emergency_contact_name'] !== '' ? $data['emergency_contact_name'] : null,
                    $data['emergency_contact_no'] !== '' ? $data['emergency_contact_no'] : null,
                    $data['emergency_contact_address'] !== '' ? $data['emergency_contact_address'] : null,
                ]);

            $pdo->commit();
        } catch (\Throwable $e) {
            if ($pdo->inTransaction()) {
                $pdo->rollBack();
            }
            Flash::set('danger', 'Could not update teacher information: ' . $e->getMessage());
            Router::redirect('/focal/teachers/view?user_id=' . $userId);
        }

        Flash::set('success', 'Teacher information updated.');
        Router::redirect('/focal/teachers/view?user_id=' . $userId);
    }

    public static function updateTeacherPassword(): void
    {
        Auth::requireRole('focal');
        if (!Csrf::validate($_POST['_csrf'] ?? null)) {
            http_response_code(400);
            echo 'Bad Request (CSRF)';
            return;
        }

        $userId = (int)($_POST['user_id'] ?? 0);
        $password = (string)($_POST['password'] ?? '');

        if ($userId <= 0) {
            Router::redirect('/focal/teachers');
        }
        if (!static::strongPassword($password)) {
            Flash::set('danger', 'Password must be at least 12 characters and include uppercase letters, lowercase letters, numbers, and special characters.');
            Router::redirect('/focal/teachers/view?user_id=' . $userId);
        }

        $pdo = Db::pdo();
        $pdo->prepare('UPDATE users SET password_hash=?, updated_at=NOW() WHERE id=? AND role="teacher"')
            ->execute([password_hash($password, PASSWORD_DEFAULT), $userId]);

        Flash::set('success', 'Teacher password updated.');
        Router::redirect('/focal/teachers/view?user_id=' . $userId);
    }

    public static function updateTeacherExpiration(): void
    {
        Auth::requireRole('focal');
        if (!Csrf::validate($_POST['_csrf'] ?? null)) {
            http_response_code(400);
            echo 'Bad Request (CSRF)';
            return;
        }

        $userId = (int)($_POST['user_id'] ?? 0);
        $expiration = trim((string)($_POST['expiration_date'] ?? ''));

        if ($userId <= 0 || !Validator::dateYmd($expiration)) {
            Flash::set('danger', 'Enter a valid expiration date.');
            Router::redirect('/focal/teachers');
        }

        $pdo = Db::pdo();

        $teacherStmt = $pdo->prepare('SELECT 1 FROM users WHERE id=? AND role="teacher" LIMIT 1');
        $teacherStmt->execute([$userId]);
        if (!$teacherStmt->fetch()) {
            Flash::set('danger', 'Teacher account was not found.');
            Router::redirect('/focal/teachers');
        }

        $approvedStmt = $pdo->prepare("SELECT 1 FROM renewal_applications WHERE teacher_user_id=? AND status='approved' LIMIT 1");
        $approvedStmt->execute([$userId]);
        if (!$approvedStmt->fetch()) {
            Flash::set('danger', 'Expiration date can only be set after approval.');
            Router::redirect('/focal/teachers');
        }

        $pdo->prepare('UPDATE teachers SET expiration_date=?, updated_at=NOW() WHERE user_id=?')
            ->execute([$expiration, $userId]);

        Flash::set('success', 'Expiration date updated.');
        Router::redirect('/focal/teachers');
    }

    public static function deleteTeacherAccount(): void
    {
        Auth::requireRole('focal');
        if (!Csrf::validate($_POST['_csrf'] ?? null)) {
            http_response_code(400);
            echo 'Bad Request (CSRF)';
            return;
        }

        $userId = (int)($_POST['user_id'] ?? 0);
        if ($userId <= 0) {
            Router::redirect('/focal/teachers');
        }

        $pdo = Db::pdo();
        $teacherStmt = $pdo->prepare('SELECT u.id, t.profile_photo_path FROM users u LEFT JOIN teachers t ON t.user_id = u.id WHERE u.id=? AND u.role="teacher" LIMIT 1');
        $teacherStmt->execute([$userId]);
        $teacherRow = $teacherStmt->fetch();
        if (!$teacherRow) {
            Flash::set('danger', 'Teacher account was not found.');
            Router::redirect('/focal/teachers');
        }

        $storedPaths = [];
        $profilePath = trim((string)($teacherRow['profile_photo_path'] ?? ''));
        if ($profilePath !== '') {
            $storedPaths[] = $profilePath;
        }

        $docStmt = $pdo->prepare('SELECT d.stored_path FROM renewal_documents d JOIN renewal_applications a ON a.id = d.application_id WHERE a.teacher_user_id=?');
        $docStmt->execute([$userId]);
        foreach ($docStmt->fetchAll() as $doc) {
            $storedPath = trim((string)($doc['stored_path'] ?? ''));
            if ($storedPath !== '') {
                $storedPaths[] = $storedPath;
            }
        }

        $pdo->beginTransaction();
        try {
            $appIdsStmt = $pdo->prepare('SELECT id FROM renewal_applications WHERE teacher_user_id=?');
            $appIdsStmt->execute([$userId]);
            $applicationIds = array_map(static fn (array $row): int => (int)$row['id'], $appIdsStmt->fetchAll());

            if ($applicationIds !== []) {
                $placeholders = implode(',', array_fill(0, count($applicationIds), '?'));
                $pdo->prepare('DELETE FROM renewal_documents WHERE application_id IN (' . $placeholders . ')')
                    ->execute($applicationIds);
            }

            $pdo->prepare('DELETE FROM notifications WHERE user_id=?')->execute([$userId]);
            $pdo->prepare('DELETE FROM renewal_applications WHERE teacher_user_id=?')->execute([$userId]);
            $pdo->prepare('DELETE FROM teachers WHERE user_id=?')->execute([$userId]);
            $deleteUser = $pdo->prepare('DELETE FROM users WHERE id=? AND role="teacher"');
            $deleteUser->execute([$userId]);

            if ($deleteUser->rowCount() < 1) {
                throw new \RuntimeException('Teacher account was not deleted.');
            }

            $pdo->commit();
        } catch (\Throwable $e) {
            if ($pdo->inTransaction()) {
                $pdo->rollBack();
            }
            Flash::set('danger', 'Could not delete teacher account: ' . $e->getMessage());
            Router::redirect('/focal/teachers/view?user_id=' . $userId);
        }

        foreach (array_unique($storedPaths) as $storedPath) {
            if ($storedPath !== '' && is_file($storedPath)) {
                @unlink($storedPath);
            }
        }

        $directories = [
            __DIR__ . '/../../storage/uploads/profile/' . $userId,
            __DIR__ . '/../../storage/uploads/' . $userId,
        ];
        foreach ($directories as $directory) {
            if (!is_dir($directory)) {
                continue;
            }

            $iterator = new \RecursiveIteratorIterator(
                new \RecursiveDirectoryIterator($directory, \FilesystemIterator::SKIP_DOTS),
                \RecursiveIteratorIterator::CHILD_FIRST
            );
            foreach ($iterator as $item) {
                if ($item->isDir()) {
                    @rmdir($item->getPathname());
                } else {
                    @unlink($item->getPathname());
                }
            }
            @rmdir($directory);
        }

        Flash::set('success', 'Teacher account deleted successfully.');
        Router::redirect('/focal/teachers');
    }

    public static function schedules(): void
    {
        Auth::requireRole('focal');
        $pdo = Db::pdo();
        $rows = $pdo->query('SELECT * FROM renewal_schedules ORDER BY due_until DESC')->fetchAll();

        View::render('focal/schedules', ['title' => 'Renewal Schedules', 'schedules' => $rows]);
    }

    public static function createSchedule(): void
    {
        Auth::requireRole('focal');
        if (!Csrf::validate($_POST['_csrf'] ?? null)) {
            http_response_code(400);
            echo 'Bad Request (CSRF)';
            return;
        }

        $title = trim((string)($_POST['title'] ?? ''));
        $openFrom = trim((string)($_POST['open_from'] ?? ''));
        $dueUntil = trim((string)($_POST['due_until'] ?? ''));

        if ($title === '' || !Validator::maxLen($title, 80) || !Validator::dateYmd($openFrom) || !Validator::dateYmd($dueUntil)) {
            Flash::set('danger', 'Provide a title and valid dates.');
            Router::redirect('/focal/schedules');
        }
        if ($openFrom > $dueUntil) {
            Flash::set('danger', 'Open-from must be earlier than due-until.');
            Router::redirect('/focal/schedules');
        }

        $pdo = Db::pdo();
        $pdo->prepare('INSERT INTO renewal_schedules (title, open_from, due_until, created_by, created_at) VALUES (?,?,?,?,NOW())')
            ->execute([$title, $openFrom, $dueUntil, Auth::user()['id']]);

        Flash::set('success', 'Schedule created.');
        Router::redirect('/focal/schedules');
    }

    public static function deleteSchedule(): void
    {
        Auth::requireRole('focal');
        if (!Csrf::validate($_POST['_csrf'] ?? null)) {
            http_response_code(400);
            echo 'Bad Request (CSRF)';
            return;
        }

        $id = (int)($_POST['id'] ?? 0);
        if ($id <= 0) {
            Router::redirect('/focal/schedules');
        }

        $pdo = Db::pdo();
        $pdo->prepare('DELETE FROM renewal_schedules WHERE id=?')->execute([$id]);

        Flash::set('success', 'Schedule deleted.');
        Router::redirect('/focal/schedules');
    }

    public static function applications(): void
    {
        Auth::requireRole('focal');
        $pdo = Db::pdo();
        $rows = $pdo->query('SELECT a.*, t.teacher_uid, t.full_name, u.email, s.title AS schedule_title FROM renewal_applications a JOIN users u ON u.id = a.teacher_user_id LEFT JOIN teachers t ON t.user_id = u.id LEFT JOIN renewal_schedules s ON s.id = a.schedule_id WHERE a.status IN ("submitted","approved","rejected") ORDER BY a.submitted_at DESC, a.id DESC LIMIT 200')->fetchAll();

        View::render('focal/applications', ['title' => 'Applications', 'apps' => $rows]);
    }

    public static function clearRenewalHistory(): void
    {
        Auth::requireRole('focal');
        if (!Csrf::validate($_POST['_csrf'] ?? null)) {
            http_response_code(400);
            echo 'Bad Request (CSRF)';
            return;
        }

        $confirm = trim((string)($_POST['confirm_text'] ?? ''));
        if (strtoupper($confirm) !== 'CLEAR') {
            Flash::set('danger', 'Type CLEAR to confirm clearing the renewal history.');
            Router::redirect('/focal/applications');
        }

        $pdo = Db::pdo();
        $pdo->beginTransaction();
        try {
            $pdo->exec('DELETE FROM renewal_documents');
            $pdo->exec('DELETE FROM renewal_applications');
            $pdo->commit();
            Flash::set('success', 'Renewal history cleared.');
        } catch (\Throwable $e) {
            $pdo->rollBack();
            Flash::set('danger', 'Could not clear history: ' . $e->getMessage());
        }

        Router::redirect('/focal/applications');
    }

    public static function viewApplication(): void
    {
        Auth::requireRole('focal');
        $id = (int)($_GET['id'] ?? 0);
        if ($id <= 0) {
            Router::redirect('/focal/applications');
        }

        $pdo = Db::pdo();
        $stmt = $pdo->prepare('SELECT a.*, t.full_name, t.expiration_date, u.email, s.title AS schedule_title FROM renewal_applications a JOIN users u ON u.id = a.teacher_user_id LEFT JOIN teachers t ON t.user_id = u.id LEFT JOIN renewal_schedules s ON s.id = a.schedule_id WHERE a.id = ? LIMIT 1');
        $stmt->execute([$id]);
        $app = $stmt->fetch();
        if (!$app) {
            Router::redirect('/focal/applications');
        }

        $docsStmt = $pdo->prepare('SELECT * FROM renewal_documents WHERE application_id = ? ORDER BY uploaded_at DESC');
        $docsStmt->execute([$id]);
        $docs = $docsStmt->fetchAll();

        View::render('focal/application_view', ['title' => 'Review Application', 'app' => $app, 'docs' => $docs]);
    }

    public static function decideApplication(): void
    {
        Auth::requireRole('focal');
        if (!Csrf::validate($_POST['_csrf'] ?? null)) {
            http_response_code(400);
            echo 'Bad Request (CSRF)';
            return;
        }

        $appId = (int)($_POST['app_id'] ?? 0);
        $decision = (string)($_POST['decision'] ?? '');
        $remarks = trim((string)($_POST['remarks'] ?? ''));

        if ($appId <= 0 || !in_array($decision, ['approved', 'rejected'], true)) {
            Flash::set('danger', 'Invalid decision.');
            Router::redirect('/focal/applications');
        }

        $pdo = Db::pdo();
        $stmt = $pdo->prepare('SELECT id, teacher_user_id, status FROM renewal_applications WHERE id=? LIMIT 1');
        $stmt->execute([$appId]);
        $app = $stmt->fetch();
        if (!$app || (string)$app['status'] !== 'submitted') {
            Flash::set('danger', 'Only submitted applications can be decided.');
            Router::redirect('/focal/applications/view?id=' . $appId);
        }

        $teacherUserId = (int)$app['teacher_user_id'];

        $pdo->beginTransaction();
        try {
            $pdo->prepare('UPDATE renewal_applications SET status=?, reviewed_by=?, reviewed_at=NOW(), remarks=?, updated_at=NOW() WHERE id=?')
                ->execute([$decision, Auth::user()['id'], $remarks, $appId]);

            $newExpiration = null;
            if ($decision === 'approved') {
                // Auto-extend expiration by 1 year (from today if expired/unset, otherwise from current expiration).
                $pdo->prepare("UPDATE teachers SET expiration_date = DATE_ADD(CASE WHEN expiration_date IS NULL OR expiration_date < CURDATE() THEN CURDATE() ELSE expiration_date END, INTERVAL 1 YEAR), updated_at=NOW() WHERE user_id=?")
                    ->execute([$teacherUserId]);

                $expStmt = $pdo->prepare('SELECT expiration_date FROM teachers WHERE user_id=? LIMIT 1');
                $expStmt->execute([$teacherUserId]);
                $newExpiration = $expStmt->fetchColumn() ?: null;
            }

            $pdo->commit();
        } catch (\Throwable $e) {
            $pdo->rollBack();
            throw $e;
        }

        $body = $decision === 'approved'
            ? 'Your renewal application was approved.'
            : 'Your renewal application was rejected.';

        if ($remarks !== '') {
            $body .= " Remarks: " . $remarks;
        }

        if ($decision === 'approved' && !empty($newExpiration)) {
            $body .= ". Expiration date: " . (string)$newExpiration;
        }

        Notifier::notify(
            $teacherUserId,
            'application_status',
            'Renewal Application ' . strtoupper($decision),
            $body,
            '/teacher/applications',
            json_encode(['application_id' => $appId, 'status' => $decision, 'remarks' => $remarks], JSON_UNESCAPED_SLASHES)
        );

        Flash::set('success', 'Application marked as ' . $decision . '.');
        Router::redirect('/focal/applications/view?id=' . $appId);
    }

    public static function terminateApplication(): void
    {
        Auth::requireRole('focal');
        if (!Csrf::validate($_POST['_csrf'] ?? null)) {
            http_response_code(400);
            echo 'Bad Request (CSRF)';
            return;
        }

        $appId = (int)($_POST['app_id'] ?? 0);
        $confirm = trim((string)($_POST['confirm_text'] ?? ''));
        if ($appId <= 0 || $confirm !== 'TERMINATE') {
            Flash::set('danger', 'Type TERMINATE to confirm.');
            Router::redirect('/focal/applications');
        }

        $pdo = Db::pdo();
        $stmt = $pdo->prepare("SELECT id, teacher_user_id, status FROM renewal_applications WHERE id=? LIMIT 1");
        $stmt->execute([$appId]);
        $app = $stmt->fetch();
        if (!$app || (string)$app['status'] !== 'approved') {
            Flash::set('danger', 'Only approved applications can be terminated.');
            Router::redirect('/focal/applications/view?id=' . $appId);
        }

        $teacherUserId = (int)$app['teacher_user_id'];

        $pdo->beginTransaction();
        try {
            $pdo->prepare("UPDATE renewal_applications SET status='terminated', updated_at=NOW() WHERE id=?")
                ->execute([$appId]);

            // Clear the expiration once the approval is terminated.
            $pdo->prepare("UPDATE teachers SET expiration_date = NULL, updated_at=NOW() WHERE user_id=?")
                ->execute([$teacherUserId]);

            $pdo->commit();
        } catch (\Throwable $e) {
            $pdo->rollBack();
            throw $e;
        }

        Notifier::notify(
            $teacherUserId,
            'application_terminated',
            'Renewal Approval Terminated',
            'Your renewal approval has been terminated by the ECCD focal. Please contact the focal for details.',
            '/teacher',
            json_encode(['application_id' => $appId, 'status' => 'terminated'], JSON_UNESCAPED_SLASHES)
        );

        Flash::set('success', 'Approval terminated.');
        Router::redirect('/focal/applications/view?id=' . $appId);
    }

    public static function applicationsPdfReport(): void
    {
        Auth::requireRole('focal');
        $pdo = Db::pdo();

        $rows = $pdo->query(
            "SELECT a.id, a.status, a.remarks, a.created_at, a.submitted_at, a.reviewed_at, u.email AS teacher_email, t.teacher_uid, t.full_name, t.assigned_center, s.title AS schedule_title, s.open_from, s.due_until, fu.email AS reviewed_by_email\n" .
            "FROM renewal_applications a\n" .
            "JOIN users u ON u.id = a.teacher_user_id\n" .
            "LEFT JOIN teachers t ON t.user_id = u.id\n" .
            "LEFT JOIN renewal_schedules s ON s.id = a.schedule_id\n" .
            "LEFT JOIN users fu ON fu.id = a.reviewed_by\n" .
            "ORDER BY a.created_at DESC, a.id DESC\n" .
            "LIMIT 1000"
        )->fetchAll();

        $pdf = new \App\Lib\SimplePdf();
        $pdf->setFont('Courier', 9, 12);

        $pdf->addLine(APP_NAME);
        $pdf->addLine('Renewal Application History Report');
        $pdf->addLine('Generated: ' . date('Y-m-d H:i:s'));
        $pdf->addLine('');

        $fieldW = 22;
        $valueW = 70;
        $sep = '+' . str_repeat('-', $fieldW + 2) . '+' . str_repeat('-', $valueW + 2) . '+';

        $wrap = static function (string $text, int $width): array {
            $text = trim(preg_replace('/\s+/', ' ', $text) ?? '');
            if ($text === '') {
                return ['-'];
            }
            $wrapped = wordwrap($text, $width, "\n", true);
            return explode("\n", $wrapped);
        };

        $addRow = static function (\App\Lib\SimplePdf $pdf, string $field, string $value, int $fieldW, int $valueW, callable $wrap): void {
            $lines = $wrap($value, $valueW);
            foreach ($lines as $i => $vLine) {
                $f = $i === 0 ? $field : '';
                $pdf->addLine('| ' . str_pad(substr($f, 0, $fieldW), $fieldW) . ' | ' . str_pad(substr($vLine, 0, $valueW), $valueW) . ' |');
            }
        };

        if (empty($rows)) {
            $pdf->addLine('No renewal applications found.');
            $pdf->output('renewal_application_history_' . date('Ymd_His') . '.pdf');
        }

        foreach ($rows as $r) {
            $teacherName = trim((string)($r['full_name'] ?? ''));
            if ($teacherName === '') {
                $teacherName = (string)($r['teacher_email'] ?? '');
            }

            $pairs = [
                'Application ID' => '#' . (string)(int)$r['id'],
                'Status' => strtoupper((string)($r['status'] ?? '')),
                'Schedule' => (string)($r['schedule_title'] ?? '-'),
                'Schedule Open' => (string)($r['open_from'] ?? '-'),
                'Schedule Due' => (string)($r['due_until'] ?? '-'),
                'Teacher ID' => (string)($r['teacher_uid'] ?? '-'),
                'Teacher Name' => $teacherName,
                'Teacher Email' => (string)($r['teacher_email'] ?? '-'),
                'Assigned CDC' => (string)($r['assigned_center'] ?? '-'),
                'Created At' => (string)($r['created_at'] ?? '-'),
                'Submitted At' => (string)($r['submitted_at'] ?? '-'),
                'Reviewed At' => (string)($r['reviewed_at'] ?? '-'),
                'Reviewed By' => (string)($r['reviewed_by_email'] ?? '-'),
                'Remarks' => (string)($r['remarks'] ?? '-'),
            ];

            $pdf->addLine($sep);
            foreach ($pairs as $field => $value) {
                $addRow($pdf, (string)$field, (string)$value, $fieldW, $valueW, $wrap);
            }
            $pdf->addLine($sep);
            $pdf->addLine('');
        }   

        $pdf->output('renewal_application_history_' . date('Ymd_His') . '.pdf');
    }

    /** @return array<string, mixed> */
    private static function profileRow(int $userId): array
    {
        $pdo = Db::pdo();
        $stmt = $pdo->prepare('SELECT * FROM focal WHERE user_id = ? LIMIT 1');
        $stmt->execute([$userId]);
        $row = $stmt->fetch();

        return $row ?: [
            'user_id' => $userId,
            'full_name' => Auth::user()['full_name'] ?? '',
            'contact_no' => '',
            'address' => '',
            'expiration_date' => null,
            'profile_photo_path' => null,
        ];
    }

    private static function strongPassword(string $password): bool
    {
        return strlen($password) >= 12
            && preg_match('/[a-z]/', $password) === 1
            && preg_match('/[A-Z]/', $password) === 1
            && preg_match('/\d/', $password) === 1
            && preg_match('/[^A-Za-z0-9]/', $password) === 1;
    }

}
