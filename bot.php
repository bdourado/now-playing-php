
<?php

use Discord\Discord;
use Discord\Parts\Channel\Message;
use React\EventLoop\Factory;
use Symfony\Component\Dotenv\Dotenv;
use Classes\Spotify;
use Classes\Helpers;

require __DIR__ . '/vendor/autoload.php';

$loop = Factory::create();

$dotenv = new Dotenv();
$dotenv->load(__DIR__.'/.env');

$discord = new Discord([
    'token' => $_ENV['DISCORD_TOKEN'],
    'loop' => $loop,
]);

$discord->on('message', function (Message $message, Discord $discord) {
    if (strpos(strtolower($message->content),Helpers::TRIGGER_WORD) !== false) {
        $spotify = new Spotify(); 
        
        $searchResult = $spotify->search($message->content);
        if (isset($searchResult)){
            $message->reply($searchResult);
        }
        
    }
});

$discord->run();