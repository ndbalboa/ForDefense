<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\EmployeeController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\EmployeeProfileController;
use App\Http\Controllers\ScanController;
use App\Http\Controllers\DocumentController;
use App\Http\Controllers\ReportController;
use App\Http\Controllers\MailController;
use App\Http\Controllers\LogController;
use App\Http\Controllers\UploadDocumentController;
use App\Http\Controllers\DocumentTypeController;
use App\Http\Controllers\DepartmentController;
use App\Http\Controllers\BackupController;
use App\Http\Controllers\AcademicRankController;
use App\Http\Controllers\UniversityPositionController;

Route::post('/login', [LoginController::class, 'login']);
Route::post('verify-2fa', [LoginController::class, 'verify']);
Route::post('/logout', [LoginController::class, 'logout'])->middleware('auth:sanctum');
Route::get('/recent-activities', [LoginController::class, 'getRecentActivities']);
Route::get('/documents/counts', [DocumentController::class, 'getDocumentCounts']);
Route::get('/mails/count', [MailController::class, 'getMailCounts']);
Route::get('/logs/recent-activities', [LogController::class, 'getRecentActivities']);

Route::middleware('auth:sanctum')->prefix('admin')->group(function () {
    Route::get('/documents/type/{typeName}', [UploadDocumentController::class, 'fetchDocumentsByType']);
    Route::get('/documents/{documentId}', [DocumentController::class, 'getDocumentDetails']);

    Route::get('/all/documents', [DocumentController::class, 'getAllDocuments']);

    Route::get('/upload/document-types', [UploadDocumentController::class, 'getDocumentTypes']);
    Route::post('/upload/documents', [UploadDocumentController::class, 'uploadDocument']);

    Route::post('/store/document-types', [DocumentTypeController::class, 'store']);
    Route::get('/document-types', [DocumentTypeController::class, 'index']);
    Route::put('/update/document-types/{id}', [DocumentTypeController::class, 'update']);
    Route::delete('/delete/document-types/{id}', [DocumentTypeController::class, 'destroy']);


// Routes for Documents
    Route::post('/documents', [UploadDocumentController::class, 'storeDocs']);
    Route::get('/documents/{id}/download-watermarked', [DocumentController::class, 'downloadWatermarked']);


    Route::get('/list/documents/{documentTypeId}', [DocumentController::class, 'getDocumentsByType']);
    
    Route::delete('/employees/{employeeId}/deactivate', [EmployeeController::class, 'deactivateEmployee']);
    Route::get('/employees/no-user-or-deleted', [EmployeeController::class, 'getDeactivatedEmployees']);
    Route::post('users/{employeeId}/activate', [UserController::class, 'activateUser']);
    Route::post('/departments', [UserController::class, 'storeDepartment']);
    Route::delete('/employees/{id}', [EmployeeController::class, 'destroy']);
    Route::get('/employees/list', [EmployeeController::class, 'index']);
    Route::post('/employees/add', [EmployeeController::class, 'store']);
    Route::get('employees/{id}', [EmployeeController::class, 'show']);
    Route::delete('/employees/destroy/{employeeId}', [EmployeeController::class, 'deleteEmployeePermanently']);
    
    Route::put('/employees/update/{id}', [EmployeeController::class, 'update']);
    Route::post('/employees/{id}/upload-image', [EmployeeController::class, 'uploadImage']);
    Route::delete('/employees/{id}/delete-image', [EmployeeController::class, 'deleteImage']);

    Route::delete('/employees/{id}', [EmployeeController::class, 'destroy'])->name('employees.destroy'); 

    Route::post('/users', [UserController::class, 'store']);
    Route::post('/employees/{id}/restore', [EmployeeController::class, 'restore']);
    Route::delete('/employees/{id}/forceDelete', [EmployeeController::class, 'forceDeleteEmployee']);
    Route::post('/upload-scanned-document', [ScanController::class, 'upload']);
    Route::post('/documents/upload', [DocumentController::class, 'upload']);
    Route::post('/documents/save', [DocumentController::class, 'save']);
    Route::get('/employees/{employee}/documents', [DocumentController::class, 'getDocumentsByEmployee']); 
    Route::get('employees/{id}/documents', [DocumentController::class, 'getEmployeeDocuments']);
    Route::get('/documents/employee/{id}', [DocumentController::class, 'getEmployeeDocuments']);
    Route::get('/documents/type/{type}', [DocumentController::class, 'getDocumentsByType']);
    Route::get('/documents/{id}', [DocumentController::class, 'getDocumentById']);
    Route::get('/employees/{id}/documents', [EmployeeController::class, 'getDocuments']);
    Route::get('/employees/{id}/documents', [DocumentController::class, 'getEmployeeDocuments']);
    Route::get('/employees/{id}', [EmployeeController::class, 'view']);
    Route::get('/employees/{id}/documents', [EmployeeController::class, 'documents']);
    Route::get('/documents/download/{id}', [DocumentController::class, 'download']);
    Route::post('/search', [DocumentController::class, 'search']);
    Route::post('/advanced-search', [DocumentController::class, 'advancedSearch']);
    Route::put('/documents/update/{id}', [DocumentController::class, 'updateDocument']);
    Route::delete('/admin/documents/{id}', [DocumentController::class, 'destroyDocument']);
    Route::post('/upload-document', [DocumentController::class, 'uploadDocument']);
    Route::get('/reports/generate', [ReportController::class, 'generateReport']);
    Route::get('/reports', [ReportController::class, 'index']);
    Route::get('/travel-orders', [ReportController::class, 'generateTravelOrderReport']);
    Route::get('/listreport', [ReportsController::class, 'listReports']);
    Route::delete('/deletereport', [ReportsController::class, 'deleteReport']);
    Route::get('/employees', [ReportController::class, 'employeename']);
    Route::post('/generate-report', [ReportController::class, 'generatePDFReport']);
    Route::get('/generated-reports', [ReportController::class, 'fetchGeneratedReports']);
    Route::get('documentsbydaterange', [ReportController::class, 'getDocumentsByCreationDateRange']);
    Route::get('/reports', [ReportController::class, 'index']);
    Route::get('{id}/view', [ReportController::class, 'view']); 
    Route::get('{id}/download', [ReportController::class, 'download']); 
    Route::delete('/reports/{id}', [ReportController::class, 'destroy']);
    Route::delete('delete/documents/{id}', [DocumentController::class, 'destroyDocument']);

    Route::get('/mails/count', [MailController::class, 'count']);
    Route::get('/mails/details', [MailController::class, 'details']);
    Route::post('/mails', [MailController::class, 'store']);
    Route::get('/getmails', [MailController::class, 'index']);
    Route::get('/mails/{id}', [MailController::class, 'show']); 
    Route::put('/mails/{id}', [MailController::class, 'update']);
    Route::delete('/mails/{id}', [MailController::class, 'destroy']); 
    Route::get('/employeeselect', [MailController::class, 'getEmployees']);

    Route::get('/academic-ranks', [AcademicRankController::class, 'index']);
    Route::post('/store/academic-ranks', [AcademicRankController::class, 'store']);
    Route::put('/update/academic-ranks/{id}', [AcademicRankController::class, 'update']);
    Route::delete('/delete/academic-ranks/{id}', [AcademicRankController::class, 'destroy']);

    Route::post('/store/university-positions', [UniversityPositionController::class, 'store']);
    Route::get('/university-positions', [UniversityPositionController::class, 'index']);
    Route::put('/update/university-positions/{id}', [UniversityPositionController::class, 'update']);
    Route::delete('/delete/university-positions/{id}', [UniversityPositionController::class, 'destroy']);

    Route::post('/store/departments', [DepartmentController::class, 'store']);
    Route::get('/departments', [DepartmentController::class, 'index']);
    Route::put('/update/departments/{id}', [DepartmentController::class, 'update']);
    Route::delete('/delete/departments/{id}', [DepartmentController::class, 'destroy']);
    Route::get('/department/document-type-counts', [DepartmentController::class, 'getDocumentTypeCounts']);

    Route::get('/accounts/departments', [DepartmentController::class, 'fetchDepartments']);
    Route::get('/accounts/show/department-accounts/{id}', [DepartmentController::class, 'showDepartment']);
    Route::delete('/accounts/department-accounts/{id}', [DepartmentController::class, 'deleteAccount']);

    Route::get('/list/academic-ranks', [EmployeeController::class, 'listRanks']);
    Route::get('/list/university-positions', [EmployeeController::class, 'getUniversityPositions']);
    Route::get('/list/departments', [EmployeeController::class, 'getDepartments']);

    Route::get('/departments/{id}/thisdocuments', [DocumentController::class, 'getDocumentsByDepartment']);
    Route::get('/thisdepartments', [DocumentController::class, 'getDepartments']);

    Route::post('/backup/create', [BackupController::class, 'createBackup']);
    Route::post('/backup/restore', [BackupController::class, 'restoreBackup']);
    Route::get('/backup/list', [BackupController::class, 'listBackups']);
    Route::get('/backup/download/{backupFileName}', [BackupController::class, 'downloadBackup']);
    Route::delete('/backup/delete/{filename}', [BackupController::class, 'deleteBackup']);



});

Route::middleware('auth:sanctum')->group(function () {
    Route::get('/employee-profile', [EmployeeProfileController::class, 'show']);
    Route::post('/employee-profile', [EmployeeProfileController::class, 'update']);
    Route::get('/user/documents', [DocumentController::class, 'getUserDocuments']);
    Route::get('/user/profile', [UserController::class, 'fetchUserProfile']);
    Route::put('/user/profile', [UserController::class, 'updateUserProfile']);
    Route::post('/user/upload-image', [UserController::class, 'uploadImage']);
    Route::put('/user/change-credentials', [UserController::class, 'changeCredentials']);
    Route::get('/user/{userId}/documents', [DocumentController::class, 'getDocumentsForUser']);
    Route::get('/documents', [DocumentController::class, 'getAllDocuments']);
    Route::post('/user/search', [DocumentController::class, 'userSearch']);
    Route::post('/user/advanced-search', [DocumentController::class, 'userAdvancedSearch']); 
    Route::get('/listdocuments/type/{typeId}', [DocumentController::class, 'getDocumentsByTypeForUser']);
    Route::get('/documenttypes/{id}', [DocumentController::class, 'showDocumentType']);
    Route::get('/documents/all', [DocumentController::class, 'getAllUsersDocuments']);

    Route::get('/user/documents/counts', [DocumentController::class, 'getUserDocumentCounts']);
    Route::get('/department/document-types', [DepartmentController::class, 'indexs']);
    Route::get('/department-documents', [DepartmentController::class, 'getDepartmentDocuments']);
    Route::get('/department-documentstype', [DepartmentController::class, 'getDepartmentDocumentTypes']);
    Route::get('/view/department-account', [DepartmentController::class, 'view']);
    Route::put('/edit/department-account', [DepartmentController::class, 'edit']);


    
});
Route::middleware('auth:api')->get('/user', function (Request $request) {
    return $request->user();
});

Route::get('/documents/department/logged-in', [DocumentController::class, 'getDocumentsByLoggedInDepartment']);

