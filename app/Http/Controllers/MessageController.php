<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Telegram\Bot\Laravel\Facades\Telegram;
use App\Models\Message;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Queue;
use App\Jobs\SendTelegramMessage;

class MessageController extends Controller
{
    public function blastMessage(Request $request)
    {
        $requestData = $request->json()->all(); // Mendapatkan semua data JSON dari permintaan
        $messages = [];

        // Loop melalui setiap permintaan dan siapkan data untuk mendorong ke antrian
        foreach ($requestData as $data) {
            $messageText = $data['message'];
            $priority = $data['priority'];
            $usernames = $data['usernames'];

            foreach ($usernames as $username) {
                // Simpan pesan ke dalam tabel Message
                $message = new Message();
                $message->message = $messageText;
                $message->username = $username;
                $message->priority = $priority; // Anda dapat menentukan prioritas di sini jika diperlukan
                $message->save();

                // Tambahkan pesan ke dalam daftar pesan
                $messages[] = $message;
            }
        }

        // Mendorong pekerjaan ke antrian untuk setiap pesan
        foreach ($messages as $message) {
            Queue::push(new SendTelegramMessage($message->id));
        }

        return response()->json([
            'message' => 'Messages successfully pushed to queue.',
        ]);
    }
}
