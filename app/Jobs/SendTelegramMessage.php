<?php

namespace App\Jobs;

use App\Models\Message;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Telegram\Bot\Laravel\Facades\Telegram;
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
        $highPriorityMessages = Message::where('priority', 'high')->whereNull('status')->orderBy('created_at', 'asc')->get();

        // Jika ada pesan dengan prioritas tinggi, kirim pesan tersebut ke Telegram
        foreach ($highPriorityMessages as $message) {
            $user = User::where('username', $message->username)->first();
            if ($user && $user->chatId) {
                $this->sendMessage($message, $user->chatId);
                $this->updateMessageStatus($message); // Tandai pesan sebagai "done"
                Log::info('High priority message sent: ' . $message->id);
            }
        }

        // Jika tidak ada pesan dengan prioritas tinggi, coba kirim pesan dengan prioritas rendah
        $lowPriorityMessages = Message::where('priority', 'low')->whereNull('status')->orderBy('created_at', 'asc')->get();

        foreach ($lowPriorityMessages as $message) {
            $user = User::where('username', $message->username)->first();
            if ($user && $user->chatId) {
                $this->sendMessage($message, $user->chatId);
                $this->updateMessageStatus($message); // Tandai pesan sebagai "done"
                Log::info('Low priority message sent: ' . $message->id);
            }
        }
    }

    private function sendMessage($message, $chatId)
    {
        Telegram::sendMessage([
            'chat_id' => $chatId,
            'text' => $message->message,
        ]);
    }

    private function updateMessageStatus($message)
    {
        // Perbarui status pesan menjadi "done"
        $message->status = 'done';
        $message->save();
    }
}
