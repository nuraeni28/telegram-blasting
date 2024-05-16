<?php

namespace App\Telegram\Commands;

use Telegram\Bot\Commands\Command;
use Telegram\Bot\Keyboard\Keyboard;
use Telegram\Bot\Laravel\Facades\Telegram;

class HelpCommand extends Command
{
    protected string $name = 'help';
    protected string $description = 'Melihat perintah apa saja yang dapat dimasukan dan fungsinya';

    public function handle()
    {
        $this->replyWithMessage([
            'text' => "Perintah yang dapat dimasukan pada BOT Llen seperti berikut : \n/start\n/help\n/buat_laporan\n/lihat_laporan"
        ]);
        
    }
}