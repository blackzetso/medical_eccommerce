<?php

namespace App\Http\Controllers\admin;

use App\Models\File;
use App\Services\BunnyService;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use ToshY\BunnyNet\Model\Api\Stream\ManageVideos\CreateVideo as StreamCreateVideo;
use ToshY\BunnyNet\Model\Api\Stream\ManageVideos\UploadVideo as StreamUploadVideo;
use Illuminate\Support\Facades\Log;
use RealRashid\SweetAlert\Facades\Alert;

class FileController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        //dd('file Controller');
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    public function uploadToBunny(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'file' => 'required|file|mimes:mp4,mov,avi,wmv|max:2048000', // 2GB max
            'lecture_id' => 'required|exists:lectures,id',
        ]);
        $bunnyService = new BunnyService();

        try {
            $file = $request->file('file');

            // 1) Create a video entry in the library to get the video GUID
            // Using direct cURL to avoid PHP-FPM stream issues
            $libraryId = config('services.bunny.stream_library_id');
            $apiKey = config('services.bunny.stream_api_key');

            $ch = curl_init("https://video.bunnycdn.com/library/{$libraryId}/videos");
            curl_setopt_array($ch, [
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_POST => true,
                CURLOPT_HTTPHEADER => [
                    'AccessKey: ' . $apiKey,
                    'Content-Type: application/json',
                    'Accept: application/json',
                ],
                CURLOPT_POSTFIELDS => json_encode([
                    'title' => $request->name,
                ]),
            ]);

            $rawBody = curl_exec($ch);
            $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            $curlError = curl_error($ch);
            curl_close($ch);

            Log::info('Bunny CreateVideo cURL', [
                'httpCode' => $httpCode,
                'rawBody' => $rawBody,
                'curlError' => $curlError,
            ]);

            $createData = json_decode($rawBody, true);
            $videoGuid = $createData['guid'] ?? $createData['id'] ?? null;

            if (! $videoGuid) {
                Log::error('Bunny CreateVideo returned no guid', [
                    'httpCode' => $httpCode,
                    'curlError' => $curlError,
                    'body' => $createData ?? $rawBody,
                ]);

                // Return a safe error to client (don't leak headers/secrets)
                return response()->json([
                    'message' => 'فشل في إنشاء مدخل الفيديو (no guid returned)',
                    'bunny_create_status' => $httpCode,
                    'bunny_create_body' => is_array($createData) ? $createData : (string) $rawBody,
                ], 500);
            }

            // 2) Upload the video binary/content to the created video
            $stream = fopen($file->getPathname(), 'r');

            $uploadResp = $bunnyService->client()->request(
                new StreamUploadVideo(
                    libraryId: (int) config('services.bunny.stream_library_id'),
                    videoId: $videoGuid,
                    body: $stream
                )
            );

            if (in_array($uploadResp->getStatusCode(), [200, 201, 204])) {
                // Create file record
                File::create([
                    'name' => $request->name,
                    'type' => 'bunny_stream',
                    'path' => config('services.bunny.stream_hostname') . '/' . $videoGuid,
                    'video_id' => $videoGuid,
                    'lecture_id' => $request->lecture_id,
                ]);

                // Return success with SweetAlert-compatible response
                return back()->with('success', 'تم رفع الفيديو بنجاح');
            }

            return response()->json(['message' => 'فشل في رفع الفيديو (upload request failed)'], 500);

        } catch (\Exception $e) {
            return response()->json([
                'message' => 'حدث خطأ أثناء رفع الفيديو: ' . $e->getMessage()
            ], 500);
        }
    }
    // END uploadToBunny

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        dd($request->all());
    }

    /**
     * Display the specified resource.
     */
    public function show(File $file)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(File $file)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, File $file)
    {
        $data = $request->validate([
            'name' => 'required|string|max:255',
            'lecture_id' => 'required|exists:lectures,id',
        ]);

        $file->update([
            'name' => $data['name'],
            'lecture_id' => $data['lecture_id'],
        ]);

        return back()->with('success', 'تم تعديل الملف بنجاح');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(File $file)
    {
        try {
            // If file is a Bunny Stream video, delete it from Bunny first
            if (($file->type ?? null) === 'bunny_stream' && ! empty($file->video_id)) {
                $libraryId = config('services.bunny.stream_library_id');
                $apiKey = config('services.bunny.stream_api_key');

                if ($libraryId && $apiKey) {
                    $ch = curl_init("https://video.bunnycdn.com/library/{$libraryId}/videos/{$file->video_id}");
                    curl_setopt_array($ch, [
                        CURLOPT_RETURNTRANSFER => true,
                        CURLOPT_CUSTOMREQUEST => 'DELETE',
                        CURLOPT_HTTPHEADER => [
                            'AccessKey: ' . $apiKey,
                            'Accept: application/json',
                        ],
                    ]);

                    $rawBody = curl_exec($ch);
                    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
                    $curlError = curl_error($ch);
                    curl_close($ch);

                    Log::info('Bunny DeleteVideo cURL', [
                        'httpCode' => $httpCode,
                        'rawBody' => $rawBody,
                        'curlError' => $curlError,
                        'videoId' => $file->video_id,
                    ]);

                    // Accept 200/204 as success. 404 means already gone - treat as success.
                    if (! in_array($httpCode, [200, 204, 404])) {
                        return response()->json([
                            'message' => 'فشل حذف الفيديو من Bunny: HTTP ' . $httpCode,
                        ], 500);
                    }
                } else {
                    Log::warning('Bunny delete skipped: missing libraryId or apiKey');
                }
            }

            $file->delete();
            return back()->with('success', 'تم حذف الملف بنجاح (وشمل حذف الفيديو من Bunny إن وجد)');
        } catch (\Throwable $e) {
            Log::error('File delete failed', [
                'file_id' => $file->id,
                'error' => $e->getMessage(),
            ]);
            return response()->json([
                'message' => 'حدث خطأ أثناء حذف الملف: ' . $e->getMessage(),
            ], 500);
        }
    }
}
