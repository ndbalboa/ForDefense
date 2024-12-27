<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Employee;
use App\Models\Document;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use App\Models\DocumentEmployeeName; 
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use App\Models\AcademicRank;
use App\Models\UniversityPosition;
use App\Models\Department;

class EmployeeController extends Controller
{
    /**
     * Display a listing of the employees with user relationship.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function index()
    {
        $employees = Employee::with('user')->paginate(10); 
        return response()->json($employees);
    }

    public function store(Request $request)
    {
        $validatedData = $request->validate([
            'lastName' => 'required|string|max:255',
            'firstName' => 'required|string|max:255',
            'middleName' => 'nullable|string|max:255',
            'sex' => 'required|in:Male,Female,Other',
            'civilStatus' => 'nullable|string|max:255',
            'dateOfBirth' => 'required|date',
            'religion' => 'nullable|string|max:255',
            'emailAddress' => 'required|email|unique:employees,emailAddress',
            'phoneNumber' => 'required|string|unique:employees,phoneNumber',
            'gsisId' => 'nullable|string|unique:employees,gsisId',
            'pagibigId' => 'nullable|string|unique:employees,pagibigId',
            'philhealthId' => 'nullable|string|unique:employees,philhealthId',
            'sssNo' => 'nullable|string|unique:employees,sssNo',
            'agencyEmploymentNo' => 'nullable|string|unique:employees,agencyEmploymentNo',
            'taxId' => 'nullable|string|unique:employees,taxId',
            'academicRank' => 'required|string|max:255',
            'universityPosition' => 'required|string|max:255',
            'permanent_street' => 'nullable|string|max:255',
            'permanent_barangay' => 'nullable|string|max:255',
            'permanent_city' => 'nullable|string|max:255',
            'permanent_province' => 'nullable|string|max:255',
            'permanent_country' => 'nullable|string|max:255',
            'permanent_zipcode' => 'nullable|string|max:10',
            'residential_street' => 'nullable|string|max:255',
            'residential_barangay' => 'nullable|string|max:255',
            'residential_city' => 'nullable|string|max:255',
            'residential_province' => 'nullable|string|max:255',
            'residential_country' => 'nullable|string|max:255',
            'residential_zipcode' => 'nullable|string|max:10',
            'department' => 'nullable|string|max:255',
        ]);

        Employee::create($validatedData);

        return response()->json(['message' => 'Employee added successfully'], 201);
    }
    
    public function show($id)
    {
        $employee = Employee::with('user')->find($id);

        if (!$employee) {
            return response()->json(['message' => 'Employee not found'], 404);
        }

        return response()->json(['employee' => $employee]);
    }
    public function update(Request $request, $id)
    {

        $employee = Employee::findOrFail($id);
    
        $validated = $request->validate([
            'lastName' => 'required|string|max:255',
            'firstName' => 'required|string|max:255',
            'middleName' => 'nullable|string|max:255',
            'sex' => 'required|in:Male,Female,Other',
            'civilStatus' => 'nullable|string|max:255',
            'dateOfBirth' => 'required|date',
            'religion' => 'nullable|string|max:255',
            'emailAddress' => 'required|email|unique:employees,emailAddress,' . $employee->id,
            'phoneNumber' => 'required|string|unique:employees,phoneNumber,' . $employee->id,
            'gsisId' => 'nullable|string|max:255|unique:employees,gsisId,' . $employee->id,
            'pagibigId' => 'nullable|string|max:255|unique:employees,pagibigId,' . $employee->id,
            'philhealthId' => 'nullable|string|max:255|unique:employees,philhealthId,' . $employee->id,
            'sssNo' => 'nullable|string|max:255|unique:employees,sssNo,' . $employee->id,
            'agencyEmploymentNo' => 'nullable|string|max:255|unique:employees,agencyEmploymentNo,' . $employee->id,
            'taxId' => 'nullable|string|max:255|unique:employees,taxId,' . $employee->id,
            'academicRank' => 'nullable|string|max:255',
            'universityPosition' => 'nullable|string|max:255',
            'permanent_street' => 'nullable|string|max:255',
            'permanent_barangay' => 'nullable|string|max:255',
            'permanent_city' => 'nullable|string|max:255',
            'permanent_province' => 'nullable|string|max:255',
            'permanent_country' => 'nullable|string|max:255',
            'permanent_zipcode' => 'nullable|string|max:255',
            'residential_street' => 'nullable|string|max:255',
            'residential_barangay' => 'nullable|string|max:255',
            'residential_city' => 'nullable|string|max:255',
            'residential_province' => 'nullable|string|max:255',
            'residential_country' => 'nullable|string|max:255',
            'residential_zipcode' => 'nullable|string|max:255',
            'department' => 'nullable|string|max:255',
        ]);
    
        $employee->update($validated);

        return response()->json(['employee' => $employee]);
    }
    
    // Upload a profile image for the employee
    public function uploadProfileImage(Request $request, $id)
    {
        $employee = Employee::findOrFail($id);

        $request->validate([
            'profileImage' => 'required|image|mimes:jpg,jpeg,png|max:2048',
        ]);

        $image = $request->file('profileImage');
        $imagePath = $image->store('profile_images', 'public');

        // Save the image path to the employee's profile
        $employee->profileImage = $imagePath;
        $employee->save();

        return response()->json(['message' => 'Profile image uploaded successfully.', 'profileImageUrl' => Storage::url($imagePath)]);
    }

    // Delete the employee's profile image
    public function deleteImage($id)
    {
        $employee = Employee::findOrFail($id);

        if ($employee->profileImage) {
            // Delete the image from storage
            Storage::delete('public/' . $employee->profileImage);

            // Remove the profile image path from the employee's record
            $employee->profileImage = null;
            $employee->save();

            return response()->json(['message' => 'Profile image deleted successfully.']);
        }

        return response()->json(['message' => 'No profile image to delete.'], 404);
    }
    public function fetchUserProfile()
    {
        $user = Auth::user();
        return response()->json($user);
    }

    /**
     * Update the authenticated user profile.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function updateUserProfile(Request $request)
    {
        try {
            $validatedData = $request->validate($this->getValidationRules());

            $user = Auth::user();
            $user->update($validatedData);

            return response()->json($user);

        } catch (\Exception $e) {
            Log::error('Error updating profile: ' . $e->getMessage());
            return response()->json(['error' => 'Unable to update profile'], 500);
        }
    }

    /**
     * Upload a profile image for the authenticated user.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function uploadImage(Request $request)
    {
        $request->validate([
            'profileImage' => 'required|image|mimes:jpeg,png,jpg,gif|max:2048',
        ]);

        if ($request->hasFile('profileImage')) {
            $file = $request->file('profileImage');
            $path = $file->store('profile_images', 'public');
            
            $user = Auth::user();
            $user->profileImage = $path;
            $user->save();

            return response()->json(['profileImage' => $path]);
        }

        return response()->json(['message' => 'No image uploaded'], 400);
    }

    public function destroy($id)
    {
        $employee = Employee::findOrFail($id);

        // Deactivate the employee
        $employee->is_active = false;
        $employee->save();

        // Also deactivate the associated user account
        if ($employee->user) {
            $employee->user->is_active = false;
            $employee->user->save();
        }

        return response()->json(['message' => 'Employee and associated user account have been deactivated.']);
    }

        public function deactivateEmployee($employeeId)
    {
        // Find the employee by ID
        $employee = Employee::findOrFail($employeeId);
        
        // Soft delete the employee
        $employee->delete();

        // Find the associated user by employee_id
        $user = User::where('employee_id', $employeeId)->first();

        if ($user) {
            // Permanently delete the associated user account
            $user->forceDelete();
        }

        return response()->json([
            'message' => 'Employee has been deactivated and associated user account has been permanently deleted.'
        ]);
    }

    public function getDeactivatedEmployees()
{
    try {
        $deactivatedEmployees = Employee::onlyTrashed()->with('user')->get();
        return response()->json($deactivatedEmployees, 200);
    } catch (\Exception $e) {
        // Log the error for debugging
        \Log::error("Error fetching deactivated employees: " . $e->getMessage());
        return response()->json(['error' => 'Failed to fetch deactivated employees'], 500);
    }
}
public function restore($id)
{
    // Find the employee with soft-deleted status
    $employee = Employee::withTrashed()->find($id);
    
    if ($employee) {
        // Restore the employee record
        $employee->restore();
        
        // Restore the user account associated with the employee
        $user = User::where('employee_id', $id)->first();
        
        if ($user) {
            $user->status = 'active'; // Set user account status to active
            $user->save(); // Save the changes
        }

        return response()->json(['message' => 'Employee and user account restored successfully.']);
    }

    return response()->json(['message' => 'Employee not found.'], 404);
}
    public function forceDelete($id)
    {
        $employee = Employee::withTrashed()->find($id);

        if (!$employee) {
            return response()->json(['message' => 'Employee not found'], 404);
        }

        $employee->forceDelete();

        return response()->json(['message' => 'Employee permanently deleted successfully']);
    }

    public function forceDeleteEmployee($id)
    {
        try {
            $employee = Employee::withTrashed()->findOrFail($id);
            $employee->forceDelete();
            return response()->json(['message' => 'Employee permanently deleted.']);
        } catch (\Exception $e) {
            \Log::error('Error permanently deleting employee: ' . $e->getMessage());
            return response()->json(['error' => 'Failed to permanently delete employee.'], 500);
        }
    }
    public function getDocuments($employeeId)
    {
        $documents = Document::where('employee_id', $employeeId)->get();
        return response()->json($documents);
    }

     // Fetch details of a specific employee
     public function view($id)
     {
         $employee = Employee::findOrFail($id);
         return response()->json(['employee' => $employee]);
     }
 
     // Fetch all documents associated with the employee
     public function documents($id)
    {
        $employee = Employee::findOrFail($id);
        
        // Normalize the employee's full name to lowercase
        $employeeName = strtolower($employee->firstName . ' ' . $employee->lastName);

        // Fetch documents where the employee name is stored in the employee_names JSON field
        $documents = Document::where(function ($query) use ($employeeName) {
            // Use JSON_CONTAINS with a case-insensitive comparison
            $query->whereJsonContains('employee_names', $employeeName)
                ->orWhere(function ($query) use ($employeeName) {
                    // Add a fallback to handle possible capital letters
                    $query->whereRaw('LOWER(employee_names) LIKE ?', "%{$employeeName}%");
                });
        })->get();

        return response()->json(['documents' => $documents]);
    }

    public function getEmployeeDocuments()
    {
        // Get the authenticated user
        $user = auth()->user();

        // Combine first name and last name as they are stored in employee_names
        $fullName = $user->firstName . ' ' . $user->lastName;

        // Query documents where the employee_names field contains the full name
        $documents = Document::whereJsonContains('employee_names', $fullName)->get();

        return response()->json($documents);
    }

        public function listRanks(): JsonResponse
        {
            return response()->json(AcademicRank::select('id', 'rank')->get());
        }
    
        /**
         * Fetch all university positions with id and name.
         *
         * @return JsonResponse
         */
        public function getUniversityPositions(): JsonResponse
        {
            return response()->json(UniversityPosition::select('id', 'position')->get());
        }
    
        /**
         * Fetch all departments with id and name.
         *
         * @return JsonResponse
         */
        public function getDepartments(): JsonResponse
        {
            return response()->json(Department::select('id', 'department')->get());
        }
    
  /**
     * Permanently delete a deactivated employee.
     *
     * @param  int  $employeeId
     * @return \Illuminate\Http\JsonResponse
     */
    public function deleteEmployeePermanently($employeeId)
    {
        $employee = Employee::withTrashed()->find($employeeId);

        if ($employee) {
            // Permanently delete the employee
            $employee->forceDelete();
            return response()->json(['message' => 'Employee deleted permanently.']);
        }

        return response()->json(['message' => 'Employee not found.'], 404);
    }

}
