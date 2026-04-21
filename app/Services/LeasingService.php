<?php

namespace App\Services;

use App\Models\Booking;
use Illuminate\Support\Facades\Log;

class LeasingService
{
    /**
     * Pool multiple applications to leasing
     *
     * @param  array  $applications  Array of Booking IDs
     */
    public function poolApplications(array $applications): array
    {
        $results = [];

        foreach ($applications as $applicationId) {
            try {
                $result = $this->submitApplication($applicationId);
                $results[] = $result;
            } catch (\Exception $e) {
                Log::error("Error pooling application {$applicationId}: ".$e->getMessage());
                $results[] = [
                    'application_id' => $applicationId,
                    'success' => false,
                    'message' => $e->getMessage(),
                ];
            }
        }

        return $results;
    }

    /**
     * Submit single application to leasing
     */
    public function submitApplication(int $applicationId): array
    {
        $application = Booking::with('car', 'consumer', 'showroom')
            ->findOrFail($applicationId);

        // TODO: Implement actual integration with leasing system API
        // This is a placeholder that should be replaced with actual API calls

        // Example structure for leasing API integration:
        /*
        $leasingData = [
            'consumer_name' => $application->consumer->name,
            'consumer_email' => $application->consumer->email,
            'consumer_phone' => $application->consumer->phone,
            'car_brand' => $application->car->brand->name,
            'car_model' => $application->car->car_model,
            'car_price' => $application->price,
            'down_payment' => $application->down_payment,
            'installment_amount' => $application->installment_amount,
            'application_documents' => $application->application_documents,
        ];

        // Make API call to leasing system
        $response = Http::post(config('leasing.api_url') . '/applications', $leasingData);

        if ($response->successful()) {
            $application->leasing_status = Booking::LEASING_STATUS_REVIEW;
            $application->pooled_at = now();
            $application->save();

            return [
                'application_id' => $applicationId,
                'success' => true,
                'message' => 'Application submitted to leasing successfully',
                'leasing_reference' => $response->json()['reference_id'] ?? null,
            ];
        }
        */

        // Placeholder implementation
        $application->leasing_status = Booking::LEASING_STATUS_REVIEW;
        $application->pooled_at = now();
        $application->save();

        return [
            'application_id' => $applicationId,
            'success' => true,
            'message' => 'Application submitted to leasing successfully (placeholder)',
            'note' => 'Actual leasing API integration needs to be implemented',
        ];
    }

    /**
     * Check application status from leasing
     */
    public function checkStatus(int $applicationId): array
    {
        $application = Booking::findOrFail($applicationId);

        // TODO: Implement actual API call to check status from leasing system
        /*
        $response = Http::get(config('leasing.api_url') . '/applications/' . $application->leasing_reference_id);

        if ($response->successful()) {
            $leasingData = $response->json();

            // Update application status based on leasing response
            $application->leasing_status = $this->mapLeasingStatus($leasingData['status']);
            $application->leasing_notes = $leasingData['notes'] ?? null;
            $application->save();

            return [
                'application_id' => $applicationId,
                'status' => $application->leasing_status,
                'notes' => $application->leasing_notes,
            ];
        }
        */

        // Placeholder
        return [
            'application_id' => $applicationId,
            'status' => $application->leasing_status,
            'notes' => $application->leasing_notes,
            'note' => 'Actual leasing API integration needs to be implemented',
        ];
    }

    /**
     * Submit appeal to leasing
     */
    public function submitAppeal(int $applicationId, string $reason): array
    {
        $application = Booking::findOrFail($applicationId);

        if (! $application->canAppeal()) {
            return [
                'success' => false,
                'message' => 'Application cannot be appealed',
            ];
        }

        // TODO: Implement actual API call to submit appeal
        /*
        $response = Http::post(config('leasing.api_url') . '/applications/' . $application->leasing_reference_id . '/appeal', [
            'reason' => $reason,
        ]);

        if ($response->successful()) {
            $application->leasing_status = Booking::LEASING_STATUS_APPEALED;
            $application->appealed_at = now();
            $application->leasing_notes = $reason;
            $application->save();

            return [
                'success' => true,
                'message' => 'Appeal submitted successfully',
            ];
        }
        */

        // Placeholder
        $application->leasing_status = Booking::LEASING_STATUS_APPEALED;
        $application->appealed_at = now();
        $application->leasing_notes = $reason;
        $application->save();

        return [
            'success' => true,
            'message' => 'Appeal submitted successfully (placeholder)',
            'note' => 'Actual leasing API integration needs to be implemented',
        ];
    }

    /**
     * Map leasing system status to our status enum
     */
    private function mapLeasingStatus(string $leasingStatus): string
    {
        $statusMap = [
            'pending' => Booking::LEASING_STATUS_PENDING,
            'review' => Booking::LEASING_STATUS_REVIEW,
            'approved' => Booking::LEASING_STATUS_APPROVED,
            'rejected' => Booking::LEASING_STATUS_REJECTED,
            'appealed' => Booking::LEASING_STATUS_APPEALED,
        ];

        return $statusMap[$leasingStatus] ?? Booking::LEASING_STATUS_PENDING;
    }
}
