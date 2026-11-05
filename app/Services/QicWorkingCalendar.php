<?php

namespace App\Services;

use DateTimeImmutable;
use DateTimeInterface;
use DateTimeZone;
use InvalidArgumentException;

class QicWorkingCalendar
{
    public function secondsBetween(
        DateTimeInterface $start,
        DateTimeInterface $end
    ): int {
        $timezone = new DateTimeZone(config('qic_sla.timezone'));

        $start = DateTimeImmutable::createFromInterface($start)
            ->setTimezone($timezone);

        $end = DateTimeImmutable::createFromInterface($end)
            ->setTimezone($timezone);

        if ($end < $start) {
            throw new InvalidArgumentException(
                'Waktu selesai tidak boleh sebelum waktu mulai.'
            );
        }

        $total = 0;

        // Mulai sehari sebelumnya agar kelanjutan shift malam ikut terbaca.
        $day = $start->setTime(0, 0)->modify('-1 day');
        $lastDay = $end->setTime(0, 0);

        while ($day <= $lastDay) {
            $weekday = (int) $day->format('N');

            $schedule = config('qic_sla.schedule', []);

            foreach ($schedule[$weekday] ?? [] as [$from, $to]) {
                $windowStart = $this->at($day, $from);
                $windowEnd = $this->at($day, $to);

                if ($windowEnd <= $windowStart) {
                    $windowEnd = $windowEnd->modify('+1 day');
                }

                // Potong jendela kerja sesuai periode yang diminta.
                $left = max(
                    $start->getTimestamp(),
                    $windowStart->getTimestamp()
                );

                $right = min(
                    $end->getTimestamp(),
                    $windowEnd->getTimestamp()
                );

                if ($right <= $left) {
                    continue;
                }

                $seconds = $right - $left;

                // Periksa jeda pada kedua tanggal untuk shift lintas malam.
                $breakDay = $windowStart->setTime(0, 0);
                $lastBreakDay = $windowEnd->setTime(0, 0);

                while ($breakDay <= $lastBreakDay) {
                    foreach (config('qic_sla.breaks', []) as [$a, $b]) {
                        $breakStart = $this->at($breakDay, $a);
                        $breakEnd = $this->at($breakDay, $b);

                        if ($breakEnd <= $breakStart) {
                            $breakEnd = $breakEnd->modify('+1 day');
                        }

                        $seconds -= max(
                            0,
                            min($right, $breakEnd->getTimestamp())
                            - max($left, $breakStart->getTimestamp())
                        );
                    }

                    $breakDay = $breakDay->modify('+1 day');
                }

                $total += $seconds;
            }

            $day = $day->modify('+1 day');
        }

        return $total;
    }

    private function at(
        DateTimeImmutable $day,
        string $time
    ): DateTimeImmutable {
        [$hour, $minute] = array_map('intval', explode(':', $time));

        return $day->setTime($hour, $minute, 0);
    }
}
