<?php

namespace App\Telegram\Commands;

use Telegram\Bot\Commands\Command;
use Telegram\Bot\Keyboard\Keyboard;
use Telegram\Bot\Laravel\Facades\Telegram;
use App\Models\User;

class StartCommand extends Command
{
    protected string $name = 'start';
    protected string $pattern = '{first_name}';
    protected string $description = 'Start Command to get you started';

    public function handle()
    {
        $fallbackUsername = $this->getUpdate()->getMessage()->from->first_name;

        $first_name = $this->argument('username', $fallbackUsername);

        $message = $this->getUpdate()->getMessage();
        $username = $message->from->username;
        $chatId = $message->chat->id;

        $existingUser = User::where('chatId', $chatId)->first();
        // save db
        try {
            if (!$existingUser) {
                $user = new User();
                $user->username = $username;
                $user->chatId = $chatId;
                $user->save();
            }
        } catch (\Exception $e) {
            \Log::error('Error saving user to database: ' . $e->getMessage());
        }
        $this->replyWithMessage([
            'text' => "Hello {$first_name}, \n\nWelcome To Swift Bot ",
        ]);
    }
}
