<?php

return [
    'version' => 'simulasi-v1.2',
    'timezone' => 'Asia/Jakarta',

    // Jadwal berdasarkan hari MULAI shift.
    // Jam akhir <= jam mulai berarti berakhir keesokan hari.
    'schedule' => [
        1 => [['07:00', '07:00']], // Senin
        2 => [['07:00', '07:00']], // Selasa
        3 => [['07:00', '07:00']], // Rabu
        4 => [['07:00', '07:00']], // Kamis
        5 => [['07:00', '07:00']], // Jumat
        6 => [['07:00', '19:00']], // Sabtu
        7 => [],                 // Minggu
    ],

    // Penanganan issue berhenti selama jeda berikut.
    'breaks' => [
        ['00:00', '00:30'],
        ['04:00', '04:15'],
        ['09:00', '09:15'],
        ['12:00', '12:30'],
        ['16:00', '16:15'],
        ['21:00', '21:15'],
    ],

    'rules' => [
        'SLA-01' => [
            'name' => 'Quality Verification',
            'clock' => 'working',
            'target_minutes' => 480,
        ],
        'SLA-02' => [
            'name' => 'Issue Assignment',
            'clock' => 'working',
            'target_minutes' => 240,
        ],
        'SLA-03' => [
            'name' => 'Initial Response',
            'clock' => 'working',
            'target_minutes' => 480,
        ],
        'SLA-04' => [
            'name' => '3C Submission',
            'clock' => 'calendar',
            'target_minutes' => 2880,
        ],
        'SLA-05' => [
            'name' => 'Quality 3C Review',
            'clock' => 'calendar',
            'target_minutes' => 1440,
        ],
        'SLA-06' => [
            'name' => '3C Revision',
            'clock' => 'calendar',
            'target_minutes' => 1440,
        ],
        'SLA-07' => [
            'name' => 'Corrective Action',
            'clock' => 'calendar',
            'target_minutes' => 4320,
        ],
        'SLA-08' => [
            'name' => 'Issue Closure',
            'clock' => 'calendar',
            'target_minutes' => 1440,
        ],
        'SLA-09' => [
            'name' => 'End-to-End Resolution',
            'clock' => 'calendar',
            'target_minutes' => 7200,
        ],
    ],
];
