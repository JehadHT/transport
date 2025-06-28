<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;

class ChatAIController extends Controller
{
    public function handle(Request $request)
    {
        $userMessage = $request->input('message');

        // نستخدم GPT لاستخراج from و to
        $json = $this->extractWithGPT($userMessage);

        // نفكّ الـ JSON إلى مصفوفة
        $data = json_decode($json, true);

        // جهّز النص للعرض
        $from = $data['from']   ?? 'غير معروف';
        $to   = $data['to']     ?? 'غير معروف';

        return response()->json([
            'reply' => "📍 نقطة الانطلاق: {$from}  \n📍 نقطة الوصول: {$to}",
        ]);
    }

    protected function extractWithGPT(string $text): string
{
    $systemPrompt = <<<EOT
أنت مساعد ذكي لاستخراج معلومات من جمل عربية. 
أجب بصيغة JSON فقط:
{
    "from": "نقطة الانطلاق",
    "to":   "نقطة الوصول"
}
EOT;

    $response = Http::withToken(env('OPENAI_API_KEY'))
        ->post('https://api.openai.com/v1/chat/completions', [
            'model' => 'gpt-4o',
            'messages' => [
                ['role' => 'system',  'content' => $systemPrompt],
                ['role' => 'user',    'content' => $text],
            ],
            'temperature' => 0.0,
        ]);

    // أطبع الرد الكامل للتصحيح
    logger()->info('OpenAI Raw Response:', $response->json());

    // أعد ما في choices (إن وجد)
    $json = $response->json();
    if (isset($json['choices'][0]['message']['content'])) {
        return trim($json['choices'][0]['message']['content']);
    } else {
        return '{"from":"غير معروف","to":"غير معروف"}';
    }
}

}
