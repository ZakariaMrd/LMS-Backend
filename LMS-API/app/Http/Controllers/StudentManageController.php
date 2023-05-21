<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use DB;
use Session;
use Illuminate\Support\Facades\Redirect;
use \App\Mail\ApproveMail;
use \App\Mail\RemoveStudentMail;
use \App\Mail\RejectMail;
use Hash;

class StudentManageController extends Controller
{


public function student_approve($id)
{
    $data = array();
    $data['Verify'] = "Approve";

    $student = DB::table('students')->where('id', $id)->first();
    $approve = DB::table('students')->where('id', $id)->update($data);

    if ($approve) {
        $details_approve = [
            'title' => 'Library Management System',
            'body' => 'Congrats! Your account is approved.Please login now...'
        ];

        \Mail::to($student->Email)->send(new \App\Mail\ApproveMail($details_approve));

        return response()->json(['message' => 'Successfully Approved!']);
    }
}

public function student_reject($id)
{
    $student = DB::table('students')->where('id', $id)->first();
    $reject = DB::table('students')->where('id', $id)->delete();

    if ($reject) {
        $details_reject = [
            'title' => 'Library Management System',
            'body' => 'Opps! Your account is rejected.Please try again...'
        ];

        \Mail::to($student->Email)->send(new \App\Mail\RejectMail($details_reject));

        return response()->json(['message' => 'Successfully Rejected!']);
    }
}

public function remove_student()
{
    $admin_status = Session::get('Admin_ID');

    if (! $admin_status) {
        return response()->json(['error' => 'Unauthorized'], 401);
    }

    $student = DB::table('students')->where('Verify', 'Approve')->get();

    return response()->json(compact('student'));
}

public function remove_student_process($id)
{
    $student = DB::table('students')->where('id', $id)->where('Verify', 'Approve')->first();
    $record = DB::table('records')->where('Student_ID', $student->Student_ID)
        ->where('Submission_Status', 'No')
        ->count();

    if ($record > 0) {
        return response()->json(['error' => 'This student has already borrowed books!'], 422);
    }

    $details_remove = [
        'title' => 'Library Management System',
        'body' => 'Opps! Your account is deleted by Admin'
    ];

    \Mail::to($student->Email)->send(new \App\Mail\RemoveStudentMail($details_remove));

    $remove_student = DB::table('students')->where('id', $id)->delete();

    if ($remove_student) {
        return response()->json(['message' => 'Successfully Removed Student!']);
    }
}
public function student_info()
{
    $admin_status = Session::get('Admin_ID');

    if (! $admin_status) {
        return response()->json(['error' => 'Unauthorized'], 401);
    }

    $student = DB::table('students')->where('Verify', 'Approve')->get();

    return response()->json(compact('student'));
}

public function student_details($id)
{
    $student = DB::table('students')->where('Verify', 'Approve')->where('id', $id)->first();
    $book = DB::table('records')->where('Student_ID', $student->Student_ID)
        ->where('Submission_Status', 'No')
        ->get();

    return response()->json(compact('student', 'book'));
}

public function notification()
{
    $admin_status = Session::get('Admin_ID');

    if (! $admin_status) {
        return response()->json(['error' => 'Unauthorized'], 401);
    }

    $notification = DB::table('students')->where('Verify', 'Panding')
        ->where('Read', 'No')
        ->get();

    $data = array();
    $data['Read'] = "Yes";
    $update = DB::table('students')->where('Read', 'No')->update($data);

    return response()->json(compact('notification'));
}

public function notify_count()
{
    $student = DB::table('students')->where('Verify', 'Panding')->where('Read', 'No')->count();

    return response()->json(['count' => $student]);
}
public function my_collection()
{
    $student_status = Session::get('Student_ID');

    if (! $student_status) {
        return response()->json(['error' => 'Unauthorized'], 401);
    }

    $student = DB::table('students')->where('id', $student_status)->first();

    $collection = DB::table('records')->where('Student_ID', $student->Student_ID)
        ->where('Submission_Status', 'No')
        ->get();

    return response()->json(compact('student', 'collection'));
}

public function my_submission()
{
    $student_status = Session::get('Student_ID');

    if (! $student_status) {
        return response()->json(['error' => 'Unauthorized'], 401);
    }

    $student = DB::table('students')->where('id', $student_status)->first();

    $submission = DB::table('records')->where('Student_ID', $student->Student_ID)
        ->where('Submission_Status', 'Yes')
        ->get();

    return response()->json(compact('student', 'submission'));
}


}
