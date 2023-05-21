<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use DB;
use Session;
use Hash;
use \App\Mail\VerifyEmail;
use \App\Mail\ForgetPassEmail;
use Illuminate\Support\Facades\Redirect;

class StudentAuthController extends Controller
{


 public function sign_up_show()
 {
     return response()->json([
         'message' => 'Please use the appropriate API endpoint to sign up as a student.'
     ], 200);
 }

 public function sign_up_process(Request $req)
 {
     $data = [
         'Name' => $req->name,
         'Student_ID' => $req->student_id,
         'Session' => $req->session,
         'Contact_no' => $req->contact,
         'Email' => $req->email,
         'Username' => $req->username,
         'Password' => Hash::make($req->password),
         'Read' => "No",
         'Verify' => "No"
     ];

     $email_check = DB::table('students')->where('Email', $req->email)->count();
     $username_check = DB::table('students')->where('Username', $req->username)->count();
     $student_id_check = DB::table('students')->where('Student_ID', $req->student_id)->count();

     if ($student_id_check > 0) {
         return response()->json([
             'error' => 'Bad Request',
             'message' => 'Student ID already registered.'
         ], 400);
     }
     if ($username_check > 0) {
         return response()->json([
             'error' => 'Bad Request',
             'message' => 'Username already exists.'
         ], 400);
     }
     if ($email_check > 0) {
         return response()->json([
             'error' => 'Bad Request',
             'message' => 'Email already registered.'
         ], 400);
     }

     $email_code = rand(1000, 9999);
     $data['Confirmation_Code'] = $email_code;

     $image = $req->picture;
     $image_name = hexdec(uniqid());
     $ext = strtolower($image->getClientOriginalExtension());
     $image_full_name = $image_name . '.' . $ext;
     $upload_path = 'public/image/' . $req->student_id . '/';
     $image_url = $upload_path . $image_full_name;
     $upload = $image->move($upload_path, $image_full_name);
     $data['image'] = $image_url;

     if ($req->password != $req->confirm_password) {
         return response()->json([
             'error' => 'Bad Request',
             'message' => 'Password do not match.'
         ], 400);
     }

     $register = DB::table('students')->insert($data);

     if ($register) {
         $id = DB::getPdo()->lastInsertId();

         $details = [
             'title' => 'Library Management System',
             'body' => 'Your verification code - ' . $email_code
         ];

         // Send email verification

         return response()->json([
             'message' => 'Student registered successfully! Please verify your email.',
             'student_id' => $id
         ], 200);
     } else {
         return response()->json([
             'error' => 'Internal Server Error',
             'message' => 'Failed to register the student.'
         ], 500);
     }
 }

 public function verify_email($id)
 {
     Session::put('Student_id', $id);

     return response()->json([


'message' => 'Please use the appropriate API endpoint to verify the student email.'
], 200);
}



public function confirm_email(Request $req)
{
 $id = Session::get('Student_id');

 $student = DB::table('students')->where('id', $id)->first();

 if ($req->code == $student->Confirmation_Code) {
     $data = [
         'Verify' => "Panding"
     ];

     $update_status = DB::table('students')->where('id', $id)->update($data);
     Session::put('Student_id', null);

     return response()->json([
         'message' => 'Student account created and email verified!'
     ], 200);
 } else {
     return response()->json([
         'error' => 'Bad Request',
         'message' => 'Invalid Verification Code.'
     ], 400);
 }
}

public function forget_password()
 {
     return response()->json([
         'message' => 'Please use the appropriate API endpoint to initiate the password recovery process.'
     ], 200);
 }

 public function forget_password_process(Request $req)
 {
     $email = DB::table('students')->where('Email', $req->email)->count();

     if ($email == 0) {
         return response()->json([
             'error' => 'Bad Request',
             'message' => 'Email is not registered.'
         ], 400);
     }

     $auto_number = rand(10000000, 9999999999);

     Session::put('Student_Email', $req->email);
     Session::put('link_number', $auto_number);

     $details2 = [
         'title' => 'Library Management System',
         'body' => 'Please quickly change your password by link (Between 30 Minutes) - http://localhost:8000/student/recover-password/' . $auto_number
     ];

     // Send recovery email
  \Mail::to($req->email)->send(new \App\Mail\ForgetPassEmail($details2));

     return response()->json([
         'message' => 'Recovery email sent. Check your email.',
         'link_number' => $auto_number
     ], 200);
 }

 public function recover_password($link_number)
 {
     Session::put('link_number', $link_number);

     return response()->json([
         'message' => 'Please use the appropriate API endpoint to recover the student password.'
     ], 200);
 }

 public function recover_password_process(Request $req)
 {
     $email = Session::get('Student_Email');

     $student = DB::table('students')->where('Email', $email)->first();

     if ($req->new_password == $req->confirm_password) {
         $data = [
             'Password' => Hash::make($req->new_password)
         ];

         $update_password = DB::table('students')->where('Email', $email)->update($data);

         if ($update_password) {
             Session::put('link_number', null);

             return response()->json([
                 'message' => 'Password changed successfully!'
             ], 200);
         } else {
             return response()->json([
                 'error' => 'Internal Server Error',
                 'message' => 'Failed to change the password.'
             ], 500);
         }
     } else {
         return response()->json([
             'error' => 'Bad Request',
             'message' => 'Password do not match.'
         ], 400);
     }
 }
public function sign_in_process(Request $req)
 {
     $email = DB::table('students')->where('Email', $req->email)->count();
     $username = DB::table('students')->where('Username', $req->email)->count();

     if ($email > 0 || $username > 0) {
         $student = null;

         if ($email > 0) {
             $student = DB::table('students')->where('Email', $req->email)->first();
         } elseif ($username > 0) {
             $student = DB::table('students')->where('Username', $req->email)->first();
         }

         if (Hash::check($req->password, $student->Password)) {
             if ($student->Verify === "Panding") {
                 return response()->json([
                     'error' => 'Unauthorized',
                     'message' => 'Account not approved by admin.'
                 ], 401);
             } elseif ($student->Verify === "No") {
                 $id = $student->id;
                 $email_code = rand(1000, 9999);
                 $data = [
                     'Confirmation_Code' => $email_code
                 ];
                 $update_code = DB::table('students')->where('id', $id)->update($data);

                 $details = [
                     'title' => 'Library Management System',
                     'body' => 'Your verification code - ' . $email_code
                 ];

                 // Send verification email
             \Mail::to($req->email)->send(new \App\Mail\VerifyEmail($details));
                 return response()->json([
                     'message' => 'Verification code sent. Check your email.',
                     'student_id' => $id
                 ], 200);
             } else {
                 Session::put('Student_ID', $student->id);

                 return response()->json([
                     'message' => 'Logged in successfully.',
                     'student_id' => $student->id
                 ], 200);
             }
         } else {
             return response()->json([
                 'error' => 'Unauthorized',
                 'message' => 'Wrong password.'
             ], 401);
         }
     } else {
         return response()->json([
             'error' => 'Unauthorized',
             'message' => 'Wrong username or email.'
         ], 401);
     }
 }

 public function dashboard($student_id)
 {
     $student_status = Session::get('Student_ID');

     if ($student_status !== $student_id) {
         return response()->json([
             'error' => 'Unauthorized',
             'message' => 'Access denied.'
         ], 403);
     }

     $student = DB::table('students')->where('id', $student_id)->get();

     return response()->json($student, 200);
 }

 public function log_out($student_id)
 {
     $student_status = Session::get('Student_ID');

     if ($student_status !== $student_id) {
         return response()->json([
             'error' => 'Unauthorized',
             'message' => 'Access denied.'
         ], 403);
     }

     Session::put('Student_ID', null);

     return response()->json([
         'message' => 'Logged out successfully.'
     ], 200);
 }
public function change_password($student_id)
 {
     $student_status = Session::get('Student_ID');

     if ($student_status !== $student_id) {
         return response()->json([
             'error' => 'Unauthorized',
             'message' => 'Access denied.'
         ], 403);
     }

     $student = DB::table('students')->where('id', $student_id)->get();

     return response()->json($student, 200);
 }

 public function change_password_process(Request $req, $student_id)
 {
     $student = Session::get('Student_ID');
     $student_account = DB::table('students')->where('id', $student)->first();

     if (Hash::check($req->old_password, $student_account->Password)) {
         if ($req->new_password === $req->confirm_password) {
             $req->new_password = Hash::make($req->new_password);
             $data = [
                 'Password' => $req->new_password
             ];
             $update_password = DB::table('students')->where('id', $student)->update($data);

             if ($update_password) {
                 return response()->json([
                     'message' => 'Password changed successfully.'
                 ], 200);
             } else {
                 return response()->json([
                     'error' => 'Internal Server Error',
                     'message' => 'Failed to update the password.'
                 ], 500);
             }
         } else {
             return response()->json([
                 'error' => 'Bad Request',
                 'message' => 'New password and confirm password do not match.'
             ], 400);
         }
     } else {
         return response()->json([
             'error' => 'Unauthorized',
             'message' => 'Wrong old password.'
         ], 401);
     }
 }

 public function edit_info($student_id)
 {
     $student_status = Session::get('Student_ID');

     if ($student_status !== $student_id) {
         return response()->json([
             'error' => 'Unauthorized',
             'message' => 'Access denied.'
         ], 403);
     }

     $student = DB::table('students')->where('id', $student_id)->get();

     return response()->json($student, 200);
 }

 public function edit_info_process(Request $req, $student_id)
 {
     $username = $req->username;

     $check_username = DB::table('students')->where('Username', $username)
         ->where('id', '<>', $student_id)
         ->count();

     if ($check_username > 0) {
         return response()->json([
             'error' => 'Bad Request',
             'message' => 'Username already exists.'
         ], 400);
     }

     $data = [
         'Username' => $req->username,
         'Contact_no' => $req->contact,
         'Email' => $req->email
     ];

     $update_info = DB::table('students')->where('id', $student_id)
         ->update($data);

     if ($update_info) {
         return response()->json([
             'message' => 'Info updated successfully.'
         ], 200);
     } else {
         return response()->json([
             'error' => 'Internal Server Error',
             'message' => 'Failed to update info.'
         ], 500);
     }
 }
}
