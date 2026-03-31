<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class ContactController extends Controller
{
    public function index()
    {
        return view('contact.index');
    }

    public function send(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'phone' => 'required|string|max:20',
            'email' => 'nullable|email',
            'course_id' => 'nullable|exists:courses,id',
            'message' => 'nullable|string',
        ]);

        // TODO: Lưu hoặc gửi email
        // Có thể lưu vào database hoặc gửi email tới admin

        return back()->with('success', 'Cảm ơn bạn! Chúng tôi sẽ liên hệ lại trong thời gian soonest.');
    }
}
