<?php

namespace App\Telegram\Commands;

use Telegram\Bot\Commands\Command;
use Telegram\Bot\Keyboard\Keyboard;
use Telegram\Bot\Laravel\Facades\Telegram;

class StartCommand extends Command
{
    protected string $name = 'start';
    protected string $pattern = '{first_name}';
    protected string $description = 'Start Command to get you started';

    public function handle()
    {
        $fallbackUsername = $this->getUpdate()->getMessage()->from->first_name;
        
        $first_name = $this->argument(
            'username',
            $fallbackUsername
        );

        $this->replyWithMessage([
            'text' => "Hello {$first_name}, \n\nWelcome To Swift Bot ",
        ]);
        
    }
}