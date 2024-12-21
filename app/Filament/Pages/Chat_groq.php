<?php

namespace App\Filament\Pages;

use App\Models\Message;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Form;
use Filament\Pages\Page;
use GuzzleHttp\Client;

class Chat_groq extends Page
{
    protected static ?string $navigationIcon = 'heroicon-o-document-text';

    protected static string $view = 'filament.pages.chat_groq';

    protected static ?string $title = 'groq';

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

            $messages = Message::where('type', 'groq')->orderBy('created_at', 'desc')->take(3)->get();

            $all_messages = [];
            foreach ($messages as $message) {
                $all_messages[] = ['role' => 'user', 'content' => $message->prompt];
                $all_messages[] = ['role' => 'assistant', 'content' => $message->answer];
            }
            $all_messages[] = ['role' => 'user', 'content' => $inputText];

            //https://api.openai.com/v1/chat/completions
            $response = $client->post('https://api.groq.com/openai/v1/chat/completions', [
                'headers' => [
                    'Authorization' => 'Bearer ' . config('services.groq.api_key'),
                    'Content-Type' => 'application/json',
                ],
                'json' => [
                    "messages" => $all_messages,
                    "model" => "llama3-groq-70b-8192-tool-use-preview",
                    "temperature" => 1,
                    "max_tokens" => 1024,
                    "top_p" => 1,
                    "stream" => false,
                    "stop" => null
                ]

            ]);

            $result = json_decode($response->getBody(), true);
            // dd($result);

            // if($result['status'] !== 200) {
            //     dd($result['status']);
            // }
            // $answer = $result['result'][0];
            $answer = $result['choices'][0]['message']['content'];

            Message::create([
                'type' => 'groq',
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
        return Message::where('type', 'groq')->orderBy('created_at', 'asc')->get()->toArray();
    }
}
