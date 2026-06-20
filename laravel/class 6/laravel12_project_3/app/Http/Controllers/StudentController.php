<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Student;

class StudentController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()

    {
        $students = Student::all();
        return view('backend.students.index', compact('students'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('backend.students.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'fullName' => 'required|min:4|max:25',
            'gender'   => 'required',
            'email'    => 'required|email|unique:students,email',
            'phone'    => 'min:11|max:14',
            'district' => 'required',
            'photo' => 'required|image|mimes: jpeg,jpg,phg|max:2048',
            // 'subjects' => 'required|array|min:1',
        ]);

        $rand_number = rand(1,20);
        $text_lower = strtolower($request->photo->extension());
        $file_name= $rand_number.time().".".$text_lower;

        $request->photo->move(public_path('images'),$file_name);

        $student = new Student;
        $student->name = $request->fullName;
        $student->email = $request->email;
        $student->phone = $request->phone;
        $student->gender = $request->gender;
        $student->district = $request->district;

        // ভ্যালিডেশনের কারণে এখানে $request->subjects সবসময় একটি সঠিক অ্যারে হিসেবেই আসবে
        $student->subjects = implode(", ", $request->subjects);
        $student->photo = 'images/'.$file_name;

        $student->save();

        return redirect('/students')->with('success', 'Successfully Student Created');
    }


    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        $student = Student::find($id);
        return view('backend.students.show',['student'=>$student]);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        $student = Student::find($id);
        return view('backend.students.edit',['student'=>$student]);
        // return view('backend.students.edit', compact('student'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        $request->validate([
            'fullName' => 'required|min:4|max:25',
            'gender'   => 'required',
            'email'    => 'required|email',
            'phone'    => 'min:11|max:14',
            'district' => 'required',
        ]);

        $student =  Student::find($id);
        $student->name = $request->fullName;
        $student->email = $request->email;
        $student->phone = $request->phone;
        $student->gender = $request->gender;
        $student->district = $request->district;


        $student->subjects = implode(", ", $request->subjects);

        $student->update();

        return redirect('/students')->with('success', 'Successfully Student Updated');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $student = Student::find($id);
        $student->delete();
        return redirect()->back()->with('destroy', 'Student deleted successfully');
    }
}
