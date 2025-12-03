<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Endroid\QrCode\QrCode;
use Endroid\QrCode\Writer\PngWriter;
use Illuminate\Support\Facades\Cache;

class DeviceConnectionController extends Controller
{
    /**
     * Generate QR code for device connection.
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function generateQRCode(Request $request)
    {
        $user = auth()->user();
        
        // Generate a unique token for this QR code (expires in 5 minutes)
        $connectionToken = Str::random(64);
        
        // Store connection data in cache (5 minutes expiry)
        $connectionData = [
            'user_id' => $user->id,
            'email' => $user->email,
            'name' => $user->name,
            'api_url' => url('/api/v1'),
            'timestamp' => now()->toIso8601String(),
        ];
        
        Cache::put("qr_connection:{$connectionToken}", $connectionData, now()->addMinutes(5));
        
        // Create QR code data
        $qrData = json_encode([
            'type' => 'crm_device_connection',
            'token' => $connectionToken,
            'api_url' => url('/api/v1'),
        ]);
        
        // Generate QR code
        $qrCode = new QrCode($qrData);
        $writer = new PngWriter();
        $result = $writer->write($qrCode);
        
        // Return as base64 image
        return response()->json([
            'success' => true,
            'data' => [
                'qr_code' => base64_encode($result->getString()),
                'token' => $connectionToken,
                'expires_at' => now()->addMinutes(5)->toIso8601String(),
                'api_url' => url('/api/v1'),
            ],
        ]);
    }
    
    /**
     * Verify QR code and login.
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function connectDevice(Request $request)
    {
        $validated = $request->validate([
            'token' => 'required|string',
            'device_name' => 'required|string',
        ]);
        
        // Get connection data from cache
        $connectionData = Cache::get("qr_connection:{$validated['token']}");
        
        if (!$connectionData) {
            return response()->json([
                'success' => false,
                'message' => 'QR code expired or invalid. Please generate a new one.',
            ], 400);
        }
        
        // Find user
        $user = \Webkul\User\Models\User::find($connectionData['user_id']);
        
        if (!$user) {
            return response()->json([
                'success' => false,
                'message' => 'User not found',
            ], 404);
        }
        
        // Create token for device
        $token = $user->createToken($validated['device_name'])->plainTextToken;
        
        // Clear the QR code from cache
        Cache::forget("qr_connection:{$validated['token']}");
        
        return response()->json([
            'success' => true,
            'message' => 'Device connected successfully',
            'data' => [
                'token' => $token,
                'user' => [
                    'id' => $user->id,
                    'name' => $user->name,
                    'email' => $user->email,
                    'image_url' => $user->image_url ?? null,
                ],
                'api_url' => $connectionData['api_url'],
            ],
        ], 200);
    }
}

