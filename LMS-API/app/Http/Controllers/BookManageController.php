<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Session;
use DB;
use Hash;
use \App\Mail\BookOrderMail;
use \App\Mail\BookReceiveMail;
use Illuminate\Support\Facades\Redirect;

class BookManageController extends Controller
{




    public function add_shelf()
    {
        $admin_status = Session::get('Admin_ID');

        if (!$admin_status) {
            return Redirect::to('/admin');
        }

        return response()->json(['message' => 'Success', 'data' => ['view' => 'admin.add_shelf']]);
    }

    public function add_shelf_process(Request $req)
    {
        $data = array();

        $data['Shelf_ID'] = $req->shelf_id;
        $data['Shelf_Location'] = $req->shelf_location;

        $unique_shelf = DB::table('shelfs')->where('Shelf_ID', $req->shelf_id)->count();

        if ($unique_shelf > 0) {
            return response()->json(['message' => 'Shelf ID already exists!', 'alert-type' => 'error']);
        }

        $add_shelf = DB::table('shelfs')->insert($data);

        if ($add_shelf) {
            return response()->json(['message' => 'Successfully added shelf!', 'alert-type' => 'success']);
        }
    }

    public function update_shelf()
    {
        $admin_status = Session::get('Admin_ID');

        if (!$admin_status) {
            return Redirect::to('/admin');
        }

        $shelf = DB::table('shelfs')->get();

        return response()->json(['message' => 'Success', 'data' => ['view' => 'admin.update_shelf', 'shelf' => $shelf]]);
    }

    public function edit_shelf($id)
    {
        $admin_status = Session::get('Admin_ID');

        if (!$admin_status) {
            return Redirect::to('/admin');
        }

        $shelf = DB::table('shelfs')->where('id', $id)->first();
        $books_amount = DB::table('books')->where('Shelf_ID', $shelf->Shelf_ID)->sum('amounts');
        $shelf = DB::table('shelfs')->where('id', $id)->get();

        return response()->json(['message' => 'Success', 'data' => ['view' => 'admin.edit_shelf', 'shelf' => $shelf, 'books_amount' => $books_amount]]);
    }

    public function edit_shelf_process(Request $req, $id)
    {
        $data = array();

        $data['Shelf_Location'] = $req->shelf_location;

        $update_shelf = DB::table('shelfs')->where('id', $id)->update($data);

        if ($update_shelf) {
            return response()->json(['message' => 'Successfully updated shelf!', 'alert-type' => 'success']);
        } else {
            return response()->json(['message' => 'Already same location exists!', 'alert-type' => 'error']);
        }
    }

	public function remove_shelf()
{
    $admin_status = Session::get('Admin_ID');

    if (!$admin_status) {
        return response()->json(['error' => 'Unauthorized'], 401);
    }

    $shelf = DB::table('shelfs')->get();

    return response()->json($shelf);
}

public function remove_shelf_process($id)
{
    $shelf = DB::table('shelfs')->where('id', $id)->first();
    $books_amount = DB::table('books')->where('Shelf_ID', $shelf->Shelf_ID)->sum('amounts');

    if ($books_amount > 0) {
        return response()->json(['message' => 'Already some books exist in this shelf!'], 422);
    }

    $books_shelf = DB::table('records')->where('Shelf_ID', $shelf->Shelf_ID)->count();

    if ($books_shelf > 0) {
        return response()->json(['message' => 'Already some books of the shelf exist in students!'], 422);
    }

    $delete_shelf = DB::table('shelfs')->where('id', $id)->delete();

    if ($delete_shelf) {
        return response()->json(['message' => 'Successfully deleted shelf!']);
    }
}

public function add_book()
{
    $admin_status = Session::get('Admin_ID');

    if (!$admin_status) {
        return response()->json(['error' => 'Unauthorized'], 401);
    }

    $shelf = DB::table('shelfs')->get();

    return response()->json($shelf);
}

public function add_book_process(Request $req)
{
    if ($req->amounts <= 0) {
        return response()->json(['message' => 'Amount of Book must be greater than zero'], 422);
    }

    $check_book = DB::table('books')->where('Book_ID', $req->book_id)->count();

    if ($check_book > 0) {
        return response()->json(['message' => 'Book ID already exists'], 422);
    }

    $data = [
        'Book_ID' => $req->book_id,
        'Book_Name' => $req->book_name,
        'Writer_Name' => $req->writer_name,
        'Category' => $req->category,
        'Shelf_ID' => $req->shelf_id,
        'Amounts' => $req->amounts
    ];

    $add_book = DB::table('books')->insert($data);

    if ($add_book) {
        return response()->json(['message' => 'Successfully added book']);
    }
}

public function update_book()
{
    $admin_status = Session::get('Admin_ID');

    if (!$admin_status) {
        return response()->json(['error' => 'Unauthorized'], 401);
    }

    $books = DB::table('books')->get();

    return response()->json($books);
}

public function edit_book($id)
{
    $admin_status = Session::get('Admin_ID');

    if (!$admin_status) {
        return response()->json(['error' => 'Unauthorized'], 401);
    }

    $books = DB::table('books')->where('id', $id)->get();
    $shelf = DB::table('shelfs')->get();

    return response()->json(['books' => $books, 'shelf' => $shelf]);
}

public function edit_book_process(Request $req, $id)
{
    if ($req->amounts < 0) {
        return response()->json(['message' => 'Amount of Book cannot be negative'], 422);
    }

    $data = [
        'Shelf_ID' => $req->shelf_id,
        'Amounts' => $req->amounts
    ];

    $update_book = DB::table('books')->where('id', $id)->update($data);

    if ($update_book) {
        return response()->json(['message' => 'Successfully updated book']);
    } else {
        return response()->json(['message' => 'Same data already exists'], 422);
    }
}

public function remove_book()
{
    $admin_status = Session::get('Admin_ID');

    if (!$admin_status) {
        return response()->json(['error' => 'Unauthorized'], 401);
    }

    $books = DB::table('books')->get();

    return response()->json($books);
}

public function remove_book_process($id)
{
    $book = DB::table('books')->where('id', $id)->first();
    $student_copy = DB::table('records')->where('Book_ID', $book->Book_ID)
        ->where('Submission_Status', 'No')
        ->count();

    if ($student_copy > 0) {
        return response()->json(['message' => 'Student already has this book'], 422);
    }

    if ($book->Amounts > 0) {
        return response()->json(['message' => 'Shelf already has this book'], 422);
    }

    $delete_book = DB::table('books')->where('id', $id)->delete();

    if ($delete_book) {
        return response()->json(['message' => 'Successfully deleted book']);
    }
}

public function book_order()
{
    $admin_status = Session::get('Admin_ID');

    if (!$admin_status) {
        return response()->json(['error' => 'Unauthorized'], 401);
    }

    $book_order = DB::table('records')->get();

    return response()->json($book_order);
}

public function add_order()
{
    $admin_status = Session::get('Admin_ID');

    if (! $admin_status) {
        return response()->json(['error' => 'Unauthorized'], 401);
    }

    return response()->json(['message' => 'Success']);
}

public function add_order_process(Request $req)
{
    $student = DB::table('students')->where('Verify', 'Approve')->where('Student_ID', $req->student_id)->count();

    if (! $student) {
        return response()->json(['message' => 'Wrong Student ID'], 422);
    }

    $book = DB::table('books')->where('Book_ID', $req->book_id)->count();

    if (! $book) {
        return response()->json(['message' => 'Wrong Book ID'], 422);
    }

    $again_order = DB::table('records')->where('Book_ID', $req->book_id)
        ->where('Student_ID', $req->student_id)
        ->where('Submission_Status', 'No')
        ->count();

    if ($again_order) {
        return response()->json(['message' => 'Sorry, This book is already ordered for the same student'], 422);
    }

    $data = [
        'Book_ID' => $req->book_id,
        'Student_ID' => $req->student_id,
        'Collection_Date' => date("d-m-Y"),
        'Submission_Status' => "No",
        'Submission_Date' => "N/A",
        'Read' => "No",
        'Expired_Date' => date('d-m-Y', strtotime("+6 months")),
    ];

    $add_order = DB::table('records')->Insert($data);

    if ($add_order) {
        $book = DB::table('books')->where('Book_ID', $req->book_id)->first();
        $data2 = ['Amounts' => $book->Amounts - 1];
        $remove_book = DB::table('books')->where('Book_ID', $req->book_id)->update($data2);

        if ($remove_book) {
            $student = DB::table('students')->where('Student_ID', $req->student_id)->first();
            $details_order = [
                'title' => 'Library Management System',
                'body' => 'Book ID - "'.$req->book_id.'" ordered for you. Expired Date - .'.date('d-m-Y', strtotime("+6 months"))
            ];

            \Mail::to($student->Email)->send(new \App\Mail\BookOrderMail($details_order));

            return response()->json(['message' => 'Successfully order completed']);
        }
    }
}

public function book_received()
{
    $admin_status = Session::get('Admin_ID');

    if (! $admin_status) {
        return response()->json(['error' => 'Unauthorized'], 401);
    }

    $book_order = DB::table('records')->where('Submission_Status', 'No')->get();

    return response()->json($book_order);
}
public function book_received_process($id)
{
    date_default_timezone_set("Asia/Dhaka");
    $today = date("d-m-Y");

    $data = [
        'Submission_Date' => $today,
        'Submission_Status' => "Yes"
    ];

    $update_status = DB::table('records')->where('id', $id)->update($data);

    if ($update_status) {
        $book = DB::table('records')->where('id', $id)->first();
        $book2 = DB::table('books')->where('Book_ID', $book->Book_ID)->first();
        $data2 = ['Amounts' => $book2->Amounts + 1];
        $add_book = DB::table('books')->where('Book_ID', $book2->Book_ID)->update($data2);
        $student = DB::table('students')->where('Verify', 'Approve')->where('Student_ID', $book->Student_ID)->first();
        $details_received = [
            'title' => 'Library Management System',
            'body' => 'Book ID - "'.$book->Book_ID.'" received by Admin.'
        ];
        \Mail::to($student->Email)->send(new \App\Mail\BookReceiveMail($details_received));

        return response()->json(['message' => 'Successfully received']);
    }
}

public function programming_book()
{
    $admin_status = Session::get('Admin_ID');

    if (! $admin_status) {
        return response()->json(['error' => 'Unauthorized'], 401);
    }

    $book = DB::table('books')->where('Catagory', 'Programming')->get();

    return response()->json($book);
}

public function networking_book()
{
    $admin_status = Session::get('Admin_ID');

    if (! $admin_status) {
        return response()->json(['error' => 'Unauthorized'], 401);
    }

    $book = DB::table('books')->where('Catagory', 'Networking')->get();

    return response()->json($book);
}


public function database_book_student()
{
    $student_status = Session::get('Student_ID');

    if (! $student_status) {
        return response()->json(['error' => 'Unauthorized'], 401);
    }

    $student = DB::table('students')->where('id', $student_status)->get();
    $book = DB::table('books')->where('Catagory', 'Database')->get();

    return response()->json(compact('student', 'book'));
}

public function electronics_book_student()
{
    $student_status = Session::get('Student_ID');

    if (! $student_status) {
        return response()->json(['error' => 'Unauthorized'], 401);
    }

    $student = DB::table('students')->where('id', $student_status)->get();
    $book = DB::table('books')->where('Catagory', 'Electronics')->get();

    return response()->json(compact('student', 'book'));
}

public function software_book_student()
{
    $student_status = Session::get('Student_ID');

    if (! $student_status) {
        return response()->json(['error' => 'Unauthorized'], 401);
    }

    $student = DB::table('students')->where('id', $student_status)->get();
    $book = DB::table('books')->where('Catagory', 'Software Development')->get();

    return response()->json(compact('student', 'book'));
}

public function shelf_list_student()
{
    $student_status = Session::get('Student_ID');

    if (! $student_status) {
        return response()->json(['error' => 'Unauthorized'], 401);
    }

    $student = DB::table('students')->where('id', $student_status)->get();
    $shelf = DB::table('shelfs')->get();

    return response()->json(compact('student', 'shelf'));
}
public function shelf_details($id)
{
    $admin_status = Session::get('Admin_ID');

    if (! $admin_status) {
        return response()->json(['error' => 'Unauthorized'], 401);
    }

    $shelf = DB::table('shelfs')->where('id', $id)->first();
    $book = DB::table('books')->where('Shelf_ID', $shelf->Shelf_ID)->get();
    $shelf = DB::table('shelfs')->where('id', $id)->get();

    return response()->json(compact('book', 'shelf'));
}

public function shelf_details_student($id)
{
    $student_status = Session::get('Student_ID');

    if (! $student_status) {
        return response()->json(['error' => 'Unauthorized'], 401);
    }

    $student = DB::table('students')->where('id', $student_status)->get();
    $shelf = DB::table('shelfs')->where('id', $id)->first();
    $book = DB::table('books')->where('Shelf_ID', $shelf->Shelf_ID)->get();
    $shelf = DB::table('shelfs')->where('id', $id)->get();

    return response()->json(compact('student', 'book', 'shelf'));
}

public function student_notification()
{
    $student_status = Session::get('Student_ID');

    if (! $student_status) {
        return response()->json(['error' => 'Unauthorized'], 401);
    }

    $student = DB::table('students')->where('id', $student_status)->first();
    $records = DB::table('records')->where('Student_ID', $student->Student_ID)
        ->where('Submission_Status', 'No')
        ->where('Read', 'No')
        ->get();

    date_default_timezone_set("Asia/Dhaka");
    $today = date("d-m-Y");

    $data = array();
    $data['Read'] = "Yes";

    $update_read = DB::table('records')->where('Student_ID', $student->Student_ID)
        ->where('Read', 'No')
        ->update($data);

    $student = DB::table('students')->where('id', $student_status)->get();

    return response()->json(compact('student', 'records'));
}

public function student_notify_count()
{
    $student_status = Session::get('Student_ID');

    if (! $student_status) {
        return response()->json(['error' => 'Unauthorized'], 401);
    }

    date_default_timezone_set("Asia/Dhaka");
    $today = date("d-m-Y");
    $today = strtotime($today);

    $student = DB::table('students')->where('id', $student_status)->first();
    $records = DB::table('records')->where('Student_ID', $student->Student_ID)
        ->where('Submission_Status', 'No')
        ->where('Read', 'No')
        ->get();

    $count = 0;

    foreach ($records as $row) {
        $Expired_Date = strtotime($row->Expired_Date);

        if ($Expired_Date <= $today) {
            $count++;
        }
    }

    if ($count == 0) {
        return response()->json(['count' => null]);
    }

    return response()->json(['count' => $count]);
}























}

