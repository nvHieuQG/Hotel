<?php

namespace App\Events;

use App\Models\Promotion;

use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class PromotionUpdated
{
    use Dispatchable, SerializesModels;

    public $promotion;
    public $changes;

    /**
     * Create a new event instance.
     */
    public function __construct(Promotion $promotion, array $changes = [])
    {
        $this->promotion = $promotion;
        $this->changes = $changes;
    }


}
