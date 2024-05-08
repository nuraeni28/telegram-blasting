<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Telegram\Bot\Laravel\Facades\Telegram;
use App\Models\Message;
use Illuminate\Support\Facades\Http;

class MessageController extends Controller
{
    public function blastMessage(Request $request)
    {
        $requestData = $request->json()->all(); // get all JSON data from JSON
        $messageText = $requestData['message'];
        $usernames = $requestData['usernames'];

        $successCount = 0;
        $failedUsernames = [];

        foreach ($usernames as $username) {
            // get chat_id with telegram username
            $chatId = $this->getChatIdByUsername($username);

            if ($chatId) {
                // save in database
                $message = new Message();
                $message->message = $messageText;
                $message->username = $username;
                $message->save();
                // send message to client
                Telegram::sendMessage([
                    'chat_id' => $chatId,
                    'text' => $messageText,
                ]);
                $successCount++;
            } else {
                $failedUsernames[] = $username;
            }
        }

        if ($successCount > 0) {
            return response()->json([
                'message' => 'Message successfully sent to ' . $successCount . ' users',
                'failed_usernames' => $failedUsernames,
            ]);
        } else {
            return response()->json(['message' => 'Message failed to send to all users'], 404);
        }
    }
    private function getChatIdByUsername($username)
    {
        // Lakukan request ke API Telegram untuk mendapatkan update
        $response = Http::get('https://api.telegram.org/bot6868575174:AAEsXXhaaXFZ-oYL7yYWcztwuya5EaXp7cc/getUpdates');

        $responseData = $response->json();

        // Cari chat_id berdasarkan username
        foreach ($responseData['result'] as $result) {
            if (isset($result['message']['from']['username']) && $result['message']['from']['username'] === $username) {
                return $result['message']['chat']['id'];
            }
        }

        return null; // Jika username tidak ditemukan
    }
}
