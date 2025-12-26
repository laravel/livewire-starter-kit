<?php

namespace App\Exceptions;

use Exception;

class CapacityExceededException extends Exception
{
    protected $remainingHours;
    protected $requiredHours;

    /**
     * Create a new exception instance.
     *
     * @param float $remainingHours
     * @param float $requiredHours
     * @param string|null $message
     */
    public function __construct(float $remainingHours, float $requiredHours, ?string $message = null)
    {
        $this->remainingHours = $remainingHours;
        $this->requiredHours = $requiredHours;

        if ($message === null) {
            $message = sprintf(
                'Insufficient capacity. Required: %.2f hours, Available: %.2f hours',
                $requiredHours,
                $remainingHours
            );
        }

        parent::__construct($message);
    }

    /**
     * Get the remaining hours available.
     *
     * @return float
     */
    public function getRemainingHours(): float
    {
        return $this->remainingHours;
    }

    /**
     * Get the required hours.
     *
     * @return float
     */
    public function getRequiredHours(): float
    {
        return $this->requiredHours;
    }

    /**
     * Get the capacity shortfall.
     *
     * @return float
     */
    public function getShortfall(): float
    {
        return $this->requiredHours - $this->remainingHours;
    }
}
