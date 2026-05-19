<?php

namespace App\Http\Controllers;

use App\Models\file;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;

class Secondcontroller extends Controller
{
    public function mailsend(Request $req)
    {
        $validated = $req->validate([
            'name' => 'required',
            'email' => 'required',
            'phone' => 'required|regex:/^([0-9\s\-\+\(\)]*)$/|min:10',
            'message' => 'required',
            'subject' => 'required',

        ]);
        $fromemail = $req['email'];
        $fromname = $req['name'];
        $subj = $req['subject'];
        $message = $req['message'];
        $toname = 'Barefoot';
        $to_name = 'barefoot';
        $to_email = 'barefootmartialarts@gmail.com';
        // $data = array('name' => "Cloudways (sender_name)", "body" => 'A test mail');
        $data = [
            'name' => $fromname,
            'email' => $fromemail,
            'msg' => $message,
            'subj' => $subj,

        ];



        // $user['to'] = 'barefootmartialarts@gmail.com';
        // $user['sub'] = $subj;
        // $user['from'] = $email;
        Mail::send('mail', $data, function ($message) use ($to_email, $toname, $fromemail, $fromname, $subj) {

            $message->from($fromemail, $fromname);
            $message->to($to_email, $toname);
            $message->subject($subj);
        });
        // Mail::send('contactus', $data, function ($mail) use ($user) {
        //     $mail->from($user['from']);
        //     $mail->subject($user['sub']);
        //     $mail->to($user['to']);
        // });

        return redirect('/contact')->with('sentmsg', 'Message sent successfully,We will contact you shortly');
    }
    public function contactus()
    {
        return view('contactus');
    }
    public function notice_home()
    {
        $notices = file::all();
        // // mysqli_fetch_lengths(fi)
        // return view('noticepage')->with('notice', $notices);
        return view('noticehomepage')->with('notice', $notices);
    }
    public function notice_main($id)
    {
        $notice = file::find($id);
        // dd($notice);
        return view('noticepage')->with('notice', $notice);

    }
    public function aboutus()
    {

        return view('aboutus');
    }

}