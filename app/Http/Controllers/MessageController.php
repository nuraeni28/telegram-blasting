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

                $chatIdResponse = $this->getChatIdByUsername($username);

                // Check if chat ID retrieval was successful
                if ($chatIdResponse) {
                    // Check if the response was successful
                    if ($chatIdResponse->successful()) {
                        $chatId = $chatIdResponse->json('data.chat_id');
                        Queue::push(new SendTelegramMessage($message->id, $chatId));
                    } else {
                        // Handle the case where chat ID was not found
                        $success = false;
                        $failedUsernames[] = $username; // Store the failed username
                    }
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
            'message' => 'Messages sent to queue successfully.',
            'success' => $success,
            'data' => $messages,
        ]);
    }
    private function getChatIdByUsername($username)
    {
        // Lakukan request ke API Telegram untuk mendapatkan update
        $response = Http::get('https://api.telegram.org/bot6868575174:AAEsXXhaaXFZ-oYL7yYWcztwuya5EaXp7cc/getUpdates');

        // Check if the response is successful
        if ($response->successful()) {
            $responseData = $response->json();

            // Cari chat_id berdasarkan username
            foreach ($responseData['result'] as $result) {
                if (isset($result['message']['from']['username']) && $result['message']['from']['username'] === $username) {
                    return $result['message']['chat']['id'];
                }
            }
        } else {
            return null; // Return null if HTTP request fails
        }

        return null; // Jika username tidak ditemukan
    }
}
