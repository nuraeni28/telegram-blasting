<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Telegram\Bot\Laravel\Facades\Telegram;
use App\Models\Message;
use App\Models\User;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Queue;
use App\Jobs\SendTelegramMessage;

class MessageController extends Controller
{
    public function setWebHook()
    {
        try {
            // URL webhook
            $webhookUrl = 'https://ab84-125-162-211-162.ngrok-free.app/api/swiftbot/webhook';

            // check webhookUrl valid or not
            if ($webhookUrl) {
                $response = Telegram::setWebhook(['url' => $webhookUrl]);

                dd($response);
            } else {
                echo 'URL webhook tidak valid.';
            }
        } catch (\Exception $e) {
            echo 'Terjadi kesalahan: ' . $e->getMessage();
        }
    }
    public function commandHandlerWebHook()
    {
        $updates = Telegram::commandsHandler(true);
        $chat_id = $updates->getChat()->getId();
        $username = $updates->getChat()->getFirstName();

        if (strtolower($updates->getMessage()->getText() === 'halo')) {
            return Telegram::sendMessage([
                'chat_id' => $chat_id,
                'text' => 'Halo' . $username,
            ]);
        }
    }

    public function blastMessage(Request $request)
    {
        $requestData = $request->json()->all(); //get all requests
        $messages = [];
        $success = true;

        foreach ($requestData as $data) {
            $messageText = $data['message'];
            $priority = $data['priority'];
            $usernames = $data['usernames'];

            foreach ($usernames as $username) {
                $message = new Message();
                $message->message = $messageText;
                $message->username = $username;
                $message->priority = $priority;
                $message->save();

                $user = User::where('username', $username)->first();

                // Check if chat ID retrieval was successful
                if ($user !== null && is_int($user->chatId)) {
                    // Check if the response was successful

                    $chatId = $user->chatId;

                    dispatch(new SendTelegramMessage($message->id));
                } else {
                    // Handle the case where the HTTP request failed
                    $success = false;
                    $failedUsernames[] = $username; // Store the failed username
                }

                $messages[] = $message;
            }
        }

        if (!$success) {
            // If there are failed usernames, return a response indicating which usernames failed
            return response()->json(
                [
                    'message' => 'Some usernames were not found.',
                    'success' => $success,
                    'failed_usernames' => $failedUsernames,
                    'data' => [],
                ],
                404,
            ); // Use an appropriate HTTP status code
        }

        return response()->json([
            'message' => 'Messages successfully to sent.',
            'success' => $success,
            'data' => $messages,
        ]);
    }
}
