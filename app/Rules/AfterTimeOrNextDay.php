<?php

namespace App\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;
use Carbon\Carbon;

class AfterTimeOrNextDay implements ValidationRule
{
    protected $startTime;
    protected $minimumHours;

    /**
     * Create a new rule instance.
     *
     * @param string $startTime Hora de inicio (formato H:i)
     * @param int $minimumHours Mínimo de horas de duración (default: 1)
     */
    public function __construct(string $startTime, int $minimumHours = 1)
    {
        $this->startTime = $startTime;
        $this->minimumHours = $minimumHours;
    }

    /**
     * Run the validation rule.
     *
     * @param  \Closure(string, ?string=): \Illuminate\Translation\PotentiallyTranslatedString  $fail
     */
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        try {
            $start = Carbon::parse($this->startTime);
            $end = Carbon::parse($value);

            // Si end < start, asumir que cruza medianoche
            if ($end->lessThan($start)) {
                $end->addDay();
            }

            $hours = $end->diffInHours($start, true);

            // Validar que haya al menos X horas de duración
            if ($hours < $this->minimumHours) {
                $fail("La hora de fin debe ser al menos {$this->minimumHours} hora(s) después de la hora de inicio.");
            }

        } catch (\Exception $e) {
            $fail('El formato de hora no es válido.');
        }
    }
}
