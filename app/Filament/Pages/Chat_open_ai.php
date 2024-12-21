<?php

namespace App\Filament\Pages;

use App\Models\Message;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Form;
use Filament\Pages\Page;
use GuzzleHttp\Client;

class Chat_open_ai extends Page
{
    protected static ?string $navigationIcon = 'heroicon-o-document-text';

    protected static string $view = 'filament.pages.chat_open_ai';

    protected static ?string $title = 'چت کن gpt4';

    public ?array $data = [];

    public ?array $conversations = [];

    public function mount(): void
    {

        $this->form->fill();
    }

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                TextInput::make('prompt')
                    ->label(false)
                    ->placeholder('خب امروز چطور میتونم کمکت کنم ؟'),
            ])
            ->statePath('data');
    }

    public function submit()
    {
        $inputText = $this->form->getState()['prompt'];

        $client = new Client();

        try {

            $messages = Message::where('type', 'openai')->orderBy('created_at', 'desc')->take(3)->get();

            $all_messages = [];
            foreach ($messages as $message) {
                $all_messages[] = ['role' => 'user', 'content' => $message->prompt];
                $all_messages[] = ['role' => 'assistant', 'content' => $message->answer];
            }
            $all_messages[] = ['role' => 'user', 'content' => $inputText];

            // dd($all_messages);
            //https://api.openai.com/v1/chat/completions
            $response = $client->post('http://localhost:11434/api/generate', [
                'headers' => [
                    // 'Authorization' => 'Bearer ' . config('services.openai.api_key'),
                    'Content-Type' => 'application/json',
                ],
                'json' => [
                    "model" => "qwen2.5",
                    "prompt" => "سلام خوبی؟",
                    "stream" => false,
                    // "messages" => $all_messages
                ]

            ]);
            // dd($response->getBody());

            $result = json_decode($response->getBody(), true);
            dd($result);

            // if($result['status'] !== 200) {
            //     dd($result['status']);
            // }
            // $answer = $result['result'][0];
            $answer = $result['response'];

            Message::create([
                'type' => 'openai',
                'prompt' => $inputText,
                'answer' => $answer,
            ]);

            $this->conversations[] = $answer;
            $this->reset();
            // dd($this->conversations);
        } catch (\Exception $e) {
            dd($e->getMessage());
        }
        // $this->result = json_decode($response->getBody(), true);
    }

    public function getAllMessages()
    {
        return Message::where('type', 'openai')->orderBy('created_at', 'asc')->get()->toArray();
    }
}
