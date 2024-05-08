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
        $requestData = $request->json()->all(); //get the all request
        $messages = [];
        $success = true;

        // Loop data
        foreach ($requestData as $data) {
            $messageText = $data['message'];
            $priority = $data['priority'];
            $usernames = $data['usernames'];

            foreach ($usernames as $username) {
                // save to db
                $message = new Message();
                $message->message = $messageText;
                $message->username = $username;
                $message->priority = $priority;
                $message->save();

                // Tadd the message to array message
                $messages[] = $message;
            }
        }

        // push the message to the queue
        foreach ($messages as $message) {
            Queue::push(new SendTelegramMessage($message->id));
        }

        return response()->json([
            'message' => 'Messages successfully sent.',
            'success' => $success,
            'data' => $messages,
        ]);
    }
}
