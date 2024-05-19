<?php

namespace App\Http\Services;

use GuzzleHttp\Client;
use thiagoalessio\TesseractOCR\TesseractOCR;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use App\Models\ApiResponse;
use OpenAI;

class ReceiptServices {
    public function getTextFromUrl (string $url): ApiResponse {

        // Download the image
        $client = new Client();
        $response = $client->get($url);

        if ($response->getStatusCode() != 200) {
            return response()->json(['error' => 'Unable to download image'], 400);
        }

        $imageContent = $response->getBody()->getContents();

         // Determine the file extension
        $contentType = $response->getHeaderLine('Content-Type');
        $extension = $this->getExtensionFromContentType($contentType);

        if (!$extension) {
            return new ApiResponse(false, null, 400, 'Unsupported image type');
        }

        // Generate a UUID for the image name
        $imageName = Str::uuid() . '.' . $extension;
        $imagePath = storage_path('app/public/' . $imageName);

        Storage::disk('public')->put($imageName, $imageContent);

        // Run OCR on the downloaded image
        $text = (new TesseractOCR($imagePath))->run();

        // Optionally, delete the image after processing
        // Storage::disk('public')->delete($imageName);
        return new ApiResponse(true, $text, null, null);
    }

    public function extractReceipt(string $text): ApiResponse {
        $message = "format this receipt into this format assuming the given format is in json format and without escape characters. only fill in if the details are available, if not just assign null.:

the receipt data: {$text}

the format:
receipt no: string
shop name : string
purchase date: datetime
purchase time: datetime
items: array
	name: string
	quantity: int
	price: decimal
total items quantity: int
tax: decimal
others deduction: decimal
total price: decimal
total scanned price: decimal";

        $client = OpenAI::client(env('OPENAI_API_KEY'));


        $response = $client->chat()->create([
            'model' => 'gpt-3.5-turbo',
            'messages' => [
                ['role' => 'user', 'content' => $message],
                ],
        ]);

        $content = trim($response['choices'][0]['message']['content']);
        $jsonData = json_decode($content, true); // Set `true` for associative array
        return new ApiResponse(true, $jsonData, null, null);
    }

    private function getExtensionFromContentType($contentType)
    {
        $mimeTypes = [
            'image/jpeg' => 'jpg',
            'image/png' => 'png',
            'image/gif' => 'gif',
            'image/bmp' => 'bmp',
            'image/tiff' => 'tiff'
        ];

        return $mimeTypes[$contentType] ?? null;
    }
}
