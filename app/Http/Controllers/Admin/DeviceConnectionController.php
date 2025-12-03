<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Str;
use BaconQrCode\Renderer\Image\SvgImageBackEnd;
use BaconQrCode\Renderer\ImageRenderer;
use BaconQrCode\Renderer\RendererStyle\RendererStyle;
use BaconQrCode\Writer;

class DeviceConnectionController extends Controller
{
    /**
     * Display the connect device page.
     *
     * @return \Illuminate\View\View
     */
    public function index()
    {
        return view('admin::settings.connect-device');
    }

    /**
     * Generate a QR code for device connection.
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function generateQrCode(Request $request)
    {
        $user = Auth::guard('user')->user();

        if (! $user) {
            return response()->json(['success' => false, 'message' => 'Unauthenticated'], 401);
        }

        $token = Str::random(60);
        $expiry = now()->addMinutes(5);

        // Get base URL without /api/v1
        $baseUrl = rtrim(url('/'), '/');

        // Store token with user ID and expiry (matching API controller format)
        Cache::put('qr_connection:' . $token, [
            'user_id' => $user->id,
            'expires_at' => $expiry->timestamp,
            'api_url' => $baseUrl, // Store base URL only
        ], $expiry);

        $qrData = json_encode([
            'type' => 'crm_device_connection',
            'token' => $token,
            'api_url' => $baseUrl, // Base URL without /api/v1
        ]);

        try {
            // Try BaconQrCode v3 approach (with Renderer)
            $renderer = new ImageRenderer(
                new RendererStyle(300),
                new SvgImageBackEnd()
            );
            $writer = new Writer($renderer);
            $qrCodeSvg = $writer->writeString($qrData);
        } catch (\Exception $e) {
            // Fallback for older BaconQrCode versions
            $writer = new Writer(new SvgImageBackEnd());
            $qrCodeSvg = $writer->writeString($qrData);
        }
        
        // Convert SVG to data URI
        $qrCodeDataUri = 'data:image/svg+xml;base64,' . base64_encode($qrCodeSvg);

        return response()->json([
            'success' => true,
            'qr_code_image' => $qrCodeDataUri,
            'expires_at' => $expiry->toDateTimeString(),
        ]);
    }
}

