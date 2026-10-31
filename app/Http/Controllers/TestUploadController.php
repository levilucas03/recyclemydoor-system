<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class TestUploadController extends Controller
{
    public function multipart(Request $request)
    {
        $request->validate([
            'image' => 'required|image|max:5120',
        ]);

        $path = $request->file('image')
            ->store('test-uploads', 'public');

        return response()->json([
            'success' => true,
            'url' => Storage::disk('public')->url($path),
        ]);
    }

    public function raw(Request $request)
    {
        $data = $request->getContent();

        if (strlen($data) > 5 * 1024 * 1024) {
            throw ValidationException::withMessages([
                'image' => 'Image exceeds 5MB.',
            ]);
        }

        $mime = (new \finfo(FILEINFO_MIME_TYPE))
            ->buffer($data);

        $extensions = [
            'image/jpeg' => 'jpg',
            'image/png' => 'png',
            'image/webp' => 'webp',
            'image/gif' => 'gif',
        ];

        if (!isset($extensions[$mime])) {
            throw ValidationException::withMessages([
                'image' => 'Unsupported image format.',
            ]);
        }

        $filename = Str::uuid() . '.' . $extensions[$mime];

        $path = 'test-uploads/' . $filename;

        Storage::disk('public')->put($path, $data);

        return response()->json([
            'success' => true,
            'url' => Storage::disk('public')->url($path),
        ]);
    }
}
