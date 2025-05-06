<?php

namespace App\Http\Controllers;

use App\Models\AiFile;
use App\Models\AiResponse;
use App\Models\AiUsage;

use App\Services\ClaudeAIService;

use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Http\Request;
use Carbon\Carbon;

class AIController extends Controller
{
    protected $claudeAIService;

    public function __construct(ClaudeAIService $claudeAIService)
    {
        $this->claudeAIService = $claudeAIService;
    }

    public function getChatResponse(Request $request)
    {
        $speech = $request->input('speech');
        $language = $request->input('language') ?? 'python';

        $limitations = "1. The code must be syntactically correct and runnable in the specified language.
                        2. The code must not contain any external libraries or dependencies unless explicitly stated.
                        3. The code must be efficient and optimized for performance.
                        4. The code must not contain any comments or explanations, only the code itself.
                        5. The code must not include any sensitive information or credentials.
                        6. The user must only provide pseudocode, and the AI must not make any assumptions or interpretations beyond the provided pseudocode.
                        7. The AI must not provide any additional information or context beyond the requested code.
                        8. English-only responses are required.";

        $messages = [[
            "role" => "user",
            "content" => "You are a pseudocode-to-code converter. Convert the following pseudocode to code three separate times (each time, it will be a different solution for the specified problem) in the specified language: {$speech}. The language is {$language}. You must provide test cases that can pass all the generated codes, including constants for the simple function so that the user can just run the code to test. You have the following limitations that you must strictly follow: {$limitations}. If any of the limitations are reached, you must just respond with \"Unable to recognize pseudocode.\". Your response should strictly be in this JSON format:
            {
                \"codes\": [\"<code_1>\", \"<code_2>\", \"<code_3>\"],
                \"test_cases\": \"<test_cases>\",
                \"language\": \"<language>\"
            }"
        ]];

        $response = $this->claudeAIService->getResponse($messages);
        $content = json_decode($response['content'][0]['text'], true);

        if (!$content || !isset($content['codes']) || !is_array($content['codes']) || count($content['codes']) !== 3) {
            return response()->json(["error" => "Unable to recognize pseudocode."], 400);
        }

        $codes = $content['codes'];
        $testCases = $content['test_cases'];
        $lang = strtolower($content['language']);

        $extensions = [
            'python' => 'py',
            'php' => 'php',
            'javascript' => 'js',
            'java' => 'java',
            'csharp' => 'cs',
            'cpp' => 'cpp',
            'ruby' => 'rb',
            'go' => 'go',
        ];

        $ext = $extensions[$lang] ?? 'txt';
        $downloadUrls = [];
        $basePath = storage_path('app/ai-code');

        if (!file_exists($basePath)) {
            mkdir($basePath, 0777, true);
        }

        $uuid = Str::uuid()->toString();
        $combinedZipPath = $basePath . '/' . $uuid . '_all.zip';
        $combinedZip = new \ZipArchive();
        $combinedZip->open($combinedZipPath, \ZipArchive::CREATE | \ZipArchive::OVERWRITE);

        $aiResponse = AiResponse::create([
            'uuid' => $uuid,
            'prompt' => $speech,
            'language' => $lang,
            'codes' => $codes,
            'test_cases' => $testCases
        ]);

        foreach ($codes as $index => $code) {
            $filename = Str::uuid() . "_solution{$index}.zip";
            $zipPath = $basePath . '/' . $filename;

            $zip = new \ZipArchive();
            $zip->open($zipPath, \ZipArchive::CREATE | \ZipArchive::OVERWRITE);
            $zip->addFromString("solution{$index}.{$ext}", $code);
            $zip->addFromString("test_cases.{$ext}", $testCases);
            $zip->close();

            $downloadUrls[] = route('ai.download', ['filename' => $filename]);

            AiFile::create([
                'ai_response_id' => $aiResponse->id,
                'filename' => $filename,
                'path' => $zipPath,
                'type' => 'individual'
            ]);

            $combinedZip->addFromString("solution{$index}.{$ext}", $code);
        }

        $combinedZip->addFromString("test_cases.{$ext}", $testCases);
        $combinedZip->close();

        $combinedFilename = basename($combinedZipPath);
        $combinedDownloadUrl = route('ai.download', ['filename' => $combinedFilename]);

        AiFile::create([
            'ai_response_id' => $aiResponse->id,
            'filename' => $combinedFilename,
            'path' => $combinedZipPath,
            'type' => 'combined'
        ]);

        AiUsage::create([
            'ai_response_id' => $aiResponse->id,
            'ip' => $request->ip(),
            'user_agent' => $request->userAgent(),
            'action' => 'generate'
        ]);

        return response()->json([
            'codes' => $codes,
            'test_cases' => $testCases,
            'language' => $content['language'],
            'download_urls' => $downloadUrls,
            'download_all_url' => $combinedDownloadUrl
        ]);
    }
}