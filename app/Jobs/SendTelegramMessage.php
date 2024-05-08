<?php

namespace App\Jobs;

use App\Models\Message;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Telegram\Bot\Laravel\Facades\Telegram;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class SendTelegramMessage implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $messageId;

    public function __construct($messageId)
    {
        $this->messageId = $messageId;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        Log::info('Processing queue job: ' . $this->messageId);

        // Ambil pesan dengan prioritas tinggi jika tersedia
        $highPriorityMessage = Message::where('priority', 'high')->whereNull('status')->orderBy('created_at', 'asc')->first();

        // Jika ada pesan dengan prioritas tinggi, kirim pesan tersebut ke Telegram
        if ($highPriorityMessage) {
            $this->sendMessage($highPriorityMessage);
            $this->updateMessageStatus($highPriorityMessage); // Tandai pesan sebagai "done"
            Log::info('High priority message sent: ' . $highPriorityMessage->id);
        } else {
            // Jika tidak ada pesan dengan prioritas tinggi, coba kirim pesan dengan prioritas rendah
            $lowPriorityMessage = Message::where('priority', 'low')->whereNull('status')->orderBy('created_at', 'asc')->first();

            if ($lowPriorityMessage) {
                $this->sendMessage($lowPriorityMessage);
                $this->updateMessageStatus($lowPriorityMessage); // Tandai pesan sebagai "done"
                Log::info('Low priority message sent: ' . $lowPriorityMessage->id);
            }
        }
    }

    private function sendMessage($message)
    {
        // Kirim pesan ke Telegram
        $chatId = $this->getChatIdByUsername($message->username);
        if ($chatId) {
            Telegram::sendMessage([
                'chat_id' => $chatId,
                'text' => $message->message,
            ]);
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
    private function updateMessageStatus($message)
    {
        // Perbarui status pesan menjadi "done"
        $message->status = 'done';
        $message->save();
    }
}
