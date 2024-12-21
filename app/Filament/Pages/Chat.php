<?php

namespace App\Filament\Pages;

use App\Models\Message;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Form;
use Filament\Pages\Page;
use GuzzleHttp\Client;

class Chat extends Page
{
    protected static ?string $navigationIcon = 'heroicon-o-document-text';

    protected static string $view = 'filament.pages.chat';

    protected static ?string $title = 'چت کن';

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

            $messages = Message::where('type','huggingface')->orderBy('created_at', 'desc')->take(3)->get();
            
            $all_messages = [];
            foreach ($messages as $message) {
                $all_messages[] = ['role' => 'user', 'content' => $message->prompt];
                $all_messages[] = ['role' => 'assistant', 'content' => $message->answer];
            }
            $all_messages[] = ['role' => 'user', 'content' => $inputText];

            $response = $client->post('https://api-inference.huggingface.co/models/Qwen/Qwen2.5-72B-Instruct/v1/chat/completions', [
                'headers' => [
                    'Authorization' => 'Bearer ' . config('services.huggingface.api_key'),
                    'Content-Type' => 'application/json',
                ],
                'json' => [
                    'messages' => $all_messages,
                    "temperature" => 0.5,
                    "max_tokens" => 2048,
                    "top_p" => 0.7
                ],
            ]);

            $result = json_decode($response->getBody(), true);

            $answer = $result['choices'][0]['message']['content'];

            Message::create([
                'type' => 'huggingface',
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

    public function getAllMessages(){
        return Message::where('type','huggingface')->orderBy('created_at', 'asc')->get()->toArray();
    }
}
