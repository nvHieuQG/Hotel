<?php

namespace App\Events;

use App\Models\Promotion;

use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class PromotionExpired
{
    use Dispatchable, SerializesModels;

    public $promotion;

    /**
     * Create a new event instance.
     */
    public function __construct(Promotion $promotion)
    {
        $this->promotion = $promotion;
    }


}
