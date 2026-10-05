<?php

function donationFilePath(): string
{
    return __DIR__ . '/../data/donations.json';
}

function ensureDonationData(): array
{
    $directory = dirname(donationFilePath());
    if (!is_dir($directory)) {
        mkdir($directory, 0777, true);
    }

    $defaultDonations = [
        [
            'id' => 1,
            'donor_name' => 'Sarah Lee',
            'email' => 'sarah.lee@example.com',
            'amount' => 250,
            'campaign' => 'Education Support',
            'donation_date' => '2026-09-14',
            'payment_method' => 'Card'
        ],
        [
            'id' => 2,
            'donor_name' => 'Michael Chen',
            'email' => 'michael.chen@example.com',
            'amount' => 500,
            'campaign' => 'Healthcare Initiative',
            'donation_date' => '2026-09-21',
            'payment_method' => 'Bank Transfer'
        ],
        [
            'id' => 3,
            'donor_name' => 'Aisha Patel',
            'email' => 'aisha.patel@example.com',
            'amount' => 120,
            'campaign' => 'General Fund',
            'donation_date' => '2026-10-01',
            'payment_method' => 'Mobile Wallet'
        ]
    ];

    $path = donationFilePath();
    if (!file_exists($path)) {
        file_put_contents($path, json_encode($defaultDonations, JSON_PRETTY_PRINT));
        return $defaultDonations;
    }

    $content = file_get_contents($path);
    $donations = json_decode($content, true);

    if (!is_array($donations) || empty($donations)) {
        file_put_contents($path, json_encode($defaultDonations, JSON_PRETTY_PRINT));
        return $defaultDonations;
    }

    return $donations;
}

function getDonations(): array
{
    return ensureDonationData();
}

function saveDonations(array $donations): void
{
    $path = donationFilePath();
    file_put_contents($path, json_encode($donations, JSON_PRETTY_PRINT));
}

function formatCurrency(float $amount): string
{
    return '$' . number_format($amount, 2);
}

function getDashboardStats(array $donations): array
{
    $total = 0;
    $distinctDonors = [];
    $monthly = 0;
    $largest = 0;
    $currentMonth = (int) date('n');
    $currentYear = (int) date('Y');

    foreach ($donations as $donation) {
        $amount = (float) $donation['amount'];
        $total += $amount;
        $distinctDonors[$donation['email']] = true;

        if (!empty($donation['donation_date'])) {
            $date = new DateTime($donation['donation_date']);
            if ((int) $date->format('n') === $currentMonth && (int) $date->format('Y') === $currentYear) {
                $monthly += $amount;
            }
        }

        if ($amount > $largest) {
            $largest = $amount;
        }
    }

    return [
        'total' => $total,
        'donors' => count($distinctDonors),
        'monthly' => $monthly,
        'largest' => $largest,
    ];
}
