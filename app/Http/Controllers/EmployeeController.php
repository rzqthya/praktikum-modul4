<?php

namespace App\Http\Controllers;

use App\Models\Employee;
use App\Models\Position;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use RealRashid\SweetAlert\Facades\Alert;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\EmployeesExport;
use PDF;

class EmployeeController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        // $pageTitle = 'Employee List';

        // RAW SQL QUERY
        // $employees = DB::select('
        //     select *, employees.id as employee_id
        //     from employees
        //     left join positions on employees.position_id = positions.id
        // ');

        // Query Builder
        // $employees = DB::table('employees')
        //     ->select('employees.*', 'employees.id as employee_id', 'positions.*')
        //     ->leftJoin('positions', 'employees.position_id', '=', 'positions.id')
        //     ->get();

        // Eloquent
        // $employees = Employee::all();

        // return view('employee.index', [
        //     'pageTitle' => $pageTitle,
        //     'employees' => $employees
        // ]);

        $pageTitle = 'Employee List';

        confirmDelete();

        return view('employee.index', compact('pageTitle'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $pageTitle = 'Create Employee';

        // RAW SQL Query
        // $positions = DB::select('select * from positions');

        // QUERY BUILDER
        // $positions = DB::table('positions')->get();

        // Eloquent
        $positions = Position::all();

        return view('employee.create', compact('pageTitle', 'positions'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $messages = [
            'required' => ':Attribute harus diisi.',
            'email' => 'Isi :attribute dengan format yang benar',
            'numeric' => 'Isi :attribute dengan angka'
        ];

        $validator = Validator::make($request->all(), [
            'firstName' => 'required',
            'lastName' => 'required',
            'email' => 'required|email|unique:employees,email,',
            'age' => 'required|numeric',
        ], $messages);

        if ($validator->fails()) {
            return redirect()->back()->withErrors($validator)->withInput();
        }

        // Get File
        $file = $request->file('cv');

        if ($file != null) {
            $originalFilename = $file->getClientOriginalName();
            $encryptedFilename = $file->hashName();

            // Store File
            $file->store('public/files');
        }


        // INSERT QUERY Builder
        // DB::table('employees')->insert([
        //     'firstname'=> $request->firstName,
        //     'lastname'=> $request->lastName,
        //     'email'=> $request->email,
        //     'age'=> $request->age,
        //     'position_id'=> $request->position,
        // ]);

        // ELOQUENT
        $employee = New Employee;
        $employee->firstname = $request->firstName;
        $employee->lastname = $request->lastName;
        $employee->email = $request->email;
        $employee->age = $request->age;
        $employee->position_id = $request->position;

        if ($file != null) {
            $employee->original_filename = $originalFilename;
            $employee->encrypted_filename = $encryptedFilename;
        }

        $employee->save();

        Alert::success('Added Successfully', 'Employee Data Added Successfully.');

        return redirect()->route('employees.index');
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        $pageTitle = 'Employee Detail';

        // RAW SQL QUERY
        // $employee = collect(DB::select('
        //     select *, employees.id as employee_id, positions.name as position_name
        //     from employees
        //     left join positions on employees.position_id = positions.id
        //     where employees.id = ?', [$id]))->first();

        // QUERY BUILDER
        // $employee = DB::table('employees')
        //     ->select('employees.*', 'employees.id as employee_id', 'positions.name as position_name')
        //     ->leftjoin('positions', 'employees.position_id', '=', 'positions.id')
        //     ->where('employees.id', $id)
        //     ->first();

        // Eloquent
        $employee = Employee::find($id);

        return view('employee.show', compact('pageTitle', 'employee'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        $pageTitle = 'Edit Employee';

        // QUERY BUILDER
        // $employee = DB::table('employees')
        //     ->select('employees.*', 'employees.id as employee_id', 'positions.name as position_name')
        //     ->leftjoin('positions', 'employees.position_id', '=', 'positions.id')
        //     ->where('employees.id', $id)
        //     ->first();

        // $positions = DB::table('positions')->get();

        // Eloquent
        $employee = Employee::find($id);
        $positions = Position::all();

        return view('employee.edit', compact('pageTitle', 'employee', 'positions'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        $messages = [
            'required' => ':Attribute harus diisi.',
            'email' => 'Isi :attribute dengan format yang benar',
            'numeric' => 'Isi :attribute dengan angka'
        ];

        $validator = Validator::make($request->all(), [
            'firstName' => 'required',
            'lastName' => 'required',
            'email' => 'required|email',
            'age' => 'required|numeric',
        ], $messages);

        if ($validator->fails()) {
            return redirect()->back()->withErrors($validator);
        }

        // QUERY BUILDER
        // DB::table('employees')
        //     ->where('id', $id)->update([
        //     'firstname' => $request->firstName,
        //     'lastname' => $request->lastName,
        //     'email' => $request->email,
        //     'age' => $request->age,
        //     'position_id' => $request->position,
        // ]);

        // GET FILE
        $file = $request->file('cv');

        // kode 1
        // if ($file != null){
        //     $employee = Employee::find($id);
        //     $encryptedFilename = 'public/files/'.$employee->encrypted_filename;
        //     Storage::delete($encryptedFilename);
        // }
        // if ($file != null) {
        //     $originalFilename = $file->getClientOriginalName();
        //     $encryptedFilename = $file->hashName();

        //     // Store File
        //     $file->Store('public/files');
        // }

        // kode 2
        if ($file != null) {
            $originalFilename = $file->getClientOriginalName();
            $encryptedFilename = $file->hashName();

            // Store File
            $file->store('public/files');

            // Hapus file lama jika ada
            $employee = Employee::find($id);
            if ($employee->encrypted_filename) {
                Storage::delete('public/files/'.$employee->encrypted_filename);
            }
        }

        // ELOQUENT
        $employee = Employee::find($id);
        $employee->firstname = $request->firstName;
        $employee->lastname = $request->lastName;
        $employee->email = $request->email;
        $employee->age = $request->age;
        $employee->position_id = $request->position;

        if ($file != null) {
            $employee->original_filename = $originalFilename;
            $employee->encrypted_filename = $encryptedFilename;
        }

        $employee->save();

        Alert::success('Changed Successfully', 'Employee Data Changed Successfully.');

        return redirect()->route('employees.index');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        // Eloquent
        Employee::find($id)->delete();

        // QUERY BUILDER
        // DB::table('employees')
        // ->where('id', $id)
        // ->delete();

        // kode 1
        // $employee = Employee::find($id);
        // $encryptedFilename = 'public/files/'.$employee->encrypted_filename;
        // Storage::delete($encryptedFilename);

        // kode 2
        if ($employee->encrypted_filename) {
            Storage::delete('public/files/'.$employee->encrypted_filename);
        }

        $employee->delete();

        Alert::success('Deleted Successfully', 'Employee Data Deleted Successfully.');

        return redirect()->route('employees.index');
    }

    public function downloadFile($employeeId)
    {
        $employee = Employee::find($employeeId);
        $encryptedFilename = 'public/files/'.$employee->encrypted_filename;
        $downloadFilename = Str::lower($employee->firstname.'_'.$employee->lastname.'_cv.pdf');

        if(Storage::exists($encryptedFilename)) {
            return Storage::download($encryptedFilename, $downloadFilename);
        }
    }

    public function getData(Request $request)
    {
        $employees = Employee::with('position');

        if ($request->ajax()) {
            return datatables()->of($employees)
                ->addIndexColumn()
                ->addColumn('actions', function($employee) {
                    return view('employee.actions', compact('employee'));
                })
                ->toJson();
        }
    }

    public function exportExcel()
    {
        return Excel::download(new EmployeesExport, 'employees.xlsx');
    }

    public function exportPdf()
    {
        $employees = Employee::all();

        $pdf = PDF::loadView('employee.export_pdf', compact('employees'));

        return $pdf->download('employees.pdf');
    }
}
