<?php declare(strict_types=1);


use App\Controllers\AuthController;
use App\Controllers\FocalController;
use App\Controllers\NotificationsController;
use App\Controllers\TeacherController;

$router->get('/login', [AuthController::class, 'showLogin']);
$router->post('/login', [AuthController::class, 'login']);
$router->post('/logout', [AuthController::class, 'logout']);

$router->get('/notifications', [NotificationsController::class, 'index']);
$router->post('/notifications/read', [NotificationsController::class, 'markRead']);
$router->post('/notifications/clear', [NotificationsController::class, 'clear']);

$router->get('/teacher', [TeacherController::class, 'dashboard']);
$router->get('/teacher/profile', [TeacherController::class, 'profile']);
$router->post('/teacher/profile', [TeacherController::class, 'saveProfile']);
$router->get('/teacher/apply', [TeacherController::class, 'apply']);
$router->post('/teacher/apply', [TeacherController::class, 'saveApplication']);
$router->post('/teacher/apply/submit', [TeacherController::class, 'submitApplication']);
$router->post('/teacher/apply/cancel', [TeacherController::class, 'cancelApplication']);
$router->get('/teacher/applications', [TeacherController::class, 'applications']);
$router->get('/teacher/applications/view', [TeacherController::class, 'viewApplication']);
$router->get('/files/download', [TeacherController::class, 'download']);
$router->get('/files/view', [TeacherController::class, 'viewInline']);
$router->get('/files/profile-photo', [TeacherController::class, 'profilePhoto']);

$router->get('/focal', [FocalController::class, 'dashboard']);
$router->get('/focal/profile', [FocalController::class, 'profile']);
$router->post('/focal/profile', [FocalController::class, 'saveProfile']);
$router->get('/focal/teachers', [FocalController::class, 'teachers']);
$router->post('/focal/teachers/create', [FocalController::class, 'createTeacher']);
$router->get('/focal/teachers/view', [FocalController::class, 'viewTeacher']);
$router->post('/focal/teachers/update', [FocalController::class, 'updateTeacher']);
$router->post('/focal/teachers/update-password', [FocalController::class, 'updateTeacherPassword']);
$router->post('/focal/teachers/update-expiration', [FocalController::class, 'updateTeacherExpiration']);
$router->post('/focal/teachers/delete-account', [FocalController::class, 'deleteTeacherAccount']);
$router->get('/focal/schedules', [FocalController::class, 'schedules']);
$router->post('/focal/schedules/create', [FocalController::class, 'createSchedule']);
$router->post('/focal/schedules/delete', [FocalController::class, 'deleteSchedule']);
$router->get('/focal/applications', [FocalController::class, 'applications']);
$router->post('/focal/applications/clear-history', [FocalController::class, 'clearRenewalHistory']);
$router->get('/focal/reports/applications-pdf', [FocalController::class, 'applicationsPdfReport']);
$router->get('/focal/applications/view', [FocalController::class, 'viewApplication']);
$router->post('/focal/applications/decide', [FocalController::class, 'decideApplication']);
$router->post('/focal/applications/terminate', [FocalController::class, 'terminateApplication']);
