<?php

require_once 'vendor/autoload.php';

use App\Services\RoomTypeReviewService;
use App\Repositories\RoomTypeReviewRepository;
use App\Models\User;
use App\Models\RoomType;

// Test validation
$testData = [
    'user_id' => 1,
    'room_type_id' => 1,
    'rating' => 5,
    'comment' => 'Test comment',
    'status' => 'approved',
    'is_anonymous' => '1'
];

try {
    $service = new RoomTypeReviewService(new RoomTypeReviewRepository());
    $validated = $service->validateReviewData($testData);
    echo "Validation passed!\n";
    print_r($validated);
} catch (Exception $e) {
    echo "Validation failed: " . $e->getMessage() . "\n";
}

// Test create review
try {
    $service = new RoomTypeReviewService(new RoomTypeReviewRepository());
    $review = $service->createReview($testData);
    echo "Review created successfully!\n";
    echo "Review ID: " . $review->id . "\n";
} catch (Exception $e) {
    echo "Create review failed: " . $e->getMessage() . "\n";
} 