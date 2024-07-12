<?php

namespace App\Http\Controllers\DataJson;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Symfony\Component\HttpFoundation\Response;

class JsonController extends Controller
{
    public function manipulateJson()
    {
        $dataPath1 = storage_path('app/public/DataJson/Json1.json');
        $dataPath2 = storage_path('app/public/DataJson/Json2.json');
        $outputPath3 = storage_path('app/public/DataJson/Json3.json');
        $outputPath4 = storage_path('app/public/DataJson/Json4.json');

        if (!file_exists($dataPath1) || !file_exists($dataPath2)) {
            return response()->json(['error' => 'One or both files not found'], Response::HTTP_NOT_FOUND);
        }

        $json1 = file_get_contents($dataPath1);
        $json2 = file_get_contents($dataPath2);

        $data1 = json_decode($json1, true);
        $data2 = json_decode($json2, true);

        if ($data1['status'] !== 1 || $data2['status'] !== 1) {
            return response()->json(['error' => 'Data not retrieved successfully'], Response::HTTP_BAD_REQUEST);
        }

        // Create a map of workshop codes to workshop details
        $workshops = [];
        foreach ($data2['data'] as $workshop) {
            $workshops[$workshop['code']] = $workshop;
        }

        $mergedData = [];
        foreach ($data1['data'] as $booking) {
            $workshopCode = $booking['booking']['workshop']['code'];
            $workshopDetails = isset($workshops[$workshopCode]) ? $workshops[$workshopCode] : [
                'name' => '',
                'address' => '',
                'phone_number' => '',
                'distance' => 0,
            ];

            $mergedData[] = [
                'name' => $booking['name'],
                'email' => $booking['email'],
                'booking_number' => $booking['booking']['booking_number'],
                'book_date' => $booking['booking']['book_date'],
                'ahass_code' => $workshopCode,
                'ahass_name' => $booking['booking']['workshop']['name'],
                'ahass_address' => $workshopDetails['address'],
                'ahass_contact' => $workshopDetails['phone_number'],
                'ahass_distance' => $workshopDetails['distance'],
                'motorcycle_ut_code' => $booking['booking']['motorcycle']['ut_code'],
                'motorcycle' => $booking['booking']['motorcycle']['name'],
            ];
        }

        // Sort mergedData by ahass_distance ascending
        usort($mergedData, function($a, $b) {
            return $a['ahass_distance'] <=> $b['ahass_distance'];
        });

        $responsePayload = [
            'status' => 1,
            'message' => 'Data Successfully Retrieved and Sorted.',
            'data' => $mergedData,
        ];

        // Convert array to JSON for Json3.json
        $jsonResult3 = json_encode($responsePayload, JSON_PRETTY_PRINT);

        // Save JSON to Json3.json
        if (file_put_contents($outputPath3, $jsonResult3) === false) {
            return response()->json(['error' => 'Failed to save JSON file Json3.json'], Response::HTTP_INTERNAL_SERVER_ERROR);
        }

        // Convert array to JSON for Json4.json (without sorting)
        $responsePayload['message'] = 'Data Successfully Retrieved.';
        $jsonResult4 = json_encode($responsePayload, JSON_PRETTY_PRINT);

        // Save JSON to Json4.json
        if (file_put_contents($outputPath4, $jsonResult4) === false) {
            return response()->json(['error' => 'Failed to save JSON file Json4.json'], Response::HTTP_INTERNAL_SERVER_ERROR);
        }

        return response()->json($responsePayload);
    }
}
