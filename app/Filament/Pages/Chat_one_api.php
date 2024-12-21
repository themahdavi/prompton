<?php

namespace App\Filament\Pages;

use App\Models\Message;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Form;
use Filament\Pages\Page;
use GuzzleHttp\Client;

class Chat_one_api extends Page
{
    protected static ?string $navigationIcon = 'heroicon-o-document-text';

    protected static string $view = 'filament.pages.chat_one_api';

    protected static ?string $title = 'چت کن ایرانی';

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

            $messages = Message::where('type','oneapi')->orderBy('created_at', 'desc')->take(3)->get();

            $all_messages = [];
            foreach ($messages as $message) {
                $all_messages[] = ['role' => 'user', 'content' => $message->prompt];
                $all_messages[] = ['role' => 'assistant', 'content' => $message->answer];
            }
            $all_messages[] = ['role' => 'user', 'content' => $inputText];
            
            $response = $client->post('https://api.one-api.ir/chatbot/v1/gpt4o/', [
                'headers' => [
                    'one-api-token' => config('services.oneapi.api_key'),
                    'Content-Type' => 'application/json',
                    'Accept' => 'application/json',
                ],
                'json' => $all_messages
                
            ]);
            
            $result = json_decode($response->getBody(), true);

            if($result['status'] !== 200) {
                dd($result['status']);
            }
            $answer = $result['result'][0];

            Message::create([
                'type' => 'oneapi',
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
        return Message::where('type','oneapi')->orderBy('created_at', 'asc')->get()->toArray();
    }
}
