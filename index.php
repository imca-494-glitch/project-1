<?php
require __DIR__ . '/includes/data.php';

$donations = getDonations();
$search = $_GET['search'] ?? '';
$filterCampaign = $_GET['campaign'] ?? 'All';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['donation_submit'])) {
    $name = trim($_POST['donor_name'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $amount = (float) ($_POST['amount'] ?? 0);
    $campaign = trim($_POST['campaign'] ?? 'General Fund');
    $date = trim($_POST['donation_date'] ?? date('Y-m-d'));
    $method = trim($_POST['payment_method'] ?? 'Card');

    if ($name !== '' && $email !== '' && $amount > 0 && $date !== '') {
        $donations[] = [
            'id' => time() + count($donations),
            'donor_name' => $name,
            'email' => $email,
            'amount' => $amount,
            'campaign' => $campaign,
            'donation_date' => $date,
            'payment_method' => $method,
        ];

        saveDonations($donations);
    }

    header('Location: index.php');
    exit;
}

if (isset($_GET['delete'])) {
    $deleteId = (int) ($_GET['delete'] ?? 0);
    $donations = array_values(array_filter($donations, function ($donation) use ($deleteId) {
        return (int) $donation['id'] !== $deleteId;
    }));
    saveDonations($donations);
    header('Location: index.php');
    exit;
}

if ($search !== '' || $filterCampaign !== 'All') {
    $donations = array_values(array_filter($donations, function ($donation) use ($search, $filterCampaign) {
        $matchesSearch = $search === '' || stripos($donation['donor_name'], $search) !== false || stripos($donation['campaign'], $search) !== false;
        $matchesCampaign = $filterCampaign === 'All' || $donation['campaign'] === $filterCampaign;
        return $matchesSearch && $matchesCampaign;
    }));
}

$stats = getDashboardStats(getDonations());
$campaigns = ['All','General Fund','Education Support','Healthcare Initiative','Disaster Relief'];
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Donation Management System</title>
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>
    <div class="page-shell">
        <header class="topbar">
            <div>
                <p class="eyebrow">Nonprofit Operations</p>
                <h1>Donation Management System</h1>
            </div>
            <div class="status-pill">Live Dashboard</div>
        </header>

        <section class="stats-grid">
            <article class="stat-card accent-blue">
                <span>Total Donations</span>
                <strong><?php echo formatCurrency((float) $stats['total']); ?></strong>
            </article>
            <article class="stat-card accent-green">
                <span>Donors</span>
                <strong><?php echo (int) $stats['donors']; ?></strong>
            </article>
            <article class="stat-card accent-gold">
                <span>This Month</span>
                <strong><?php echo formatCurrency((float) $stats['monthly']); ?></strong>
            </article>
            <article class="stat-card accent-red">
                <span>Largest Gift</span>
                <strong><?php echo formatCurrency((float) $stats['largest']); ?></strong>
            </article>
        </section>

        <main class="content-grid">
            <section class="panel">
                <div class="panel-header">
                    <h2>Add Donation</h2>
                </div>

                <form method="POST" action="index.php" class="donation-form">
                    <div class="form-group">
                        <label for="donor_name">Donor Name</label>
                        <input type="text" id="donor_name" name="donor_name" placeholder="Enter donor name" required>
                    </div>

                    <div class="form-group">
                        <label for="email">Email</label>
                        <input type="email" id="email" name="email" placeholder="donor@example.com" required>
                    </div>

                    <div class="form-group">
                        <label for="amount">Amount</label>
                        <input type="number" id="amount" name="amount" min="1" step="0.01" placeholder="0.00" required>
                    </div>

                    <div class="form-group">
                        <label for="campaign">Campaign</label>
                        <select id="campaign" name="campaign">
                            <option value="General Fund">General Fund</option>
                            <option value="Education Support">Education Support</option>
                            <option value="Healthcare Initiative">Healthcare Initiative</option>
                            <option value="Disaster Relief">Disaster Relief</option>
                        </select>
                    </div>

                    <div class="form-group">
                        <label for="donation_date">Donation Date</label>
                        <input type="date" id="donation_date" name="donation_date" value="<?php echo date('Y-m-d'); ?>" required>
                    </div>

                    <div class="form-group">
                        <label for="payment_method">Payment Method</label>
                        <select id="payment_method" name="payment_method">
                            <option value="Card">Card</option>
                            <option value="Bank Transfer">Bank Transfer</option>
                            <option value="Cash">Cash</option>
                            <option value="Mobile Wallet">Mobile Wallet</option>
                        </select>
                    </div>

                    <button type="submit" name="donation_submit" class="primary-btn">Save Donation</button>
                </form>
            </section>

            <section class="panel">
                <div class="panel-header records-header">
                    <h2>Donation Records</h2>
                </div>

                <form method="GET" action="index.php" class="filter-bar">
                    <input type="text" name="search" id="searchInput" placeholder="Search donor or campaign" value="<?php echo htmlspecialchars($search); ?>">
                    <select name="campaign" id="campaignFilter">
                        <?php foreach ($campaigns as $option): ?>
                            <option value="<?php echo htmlspecialchars($option); ?>" <?php echo $filterCampaign === $option ? 'selected' : ''; ?>>
                                <?php echo htmlspecialchars($option === 'All' ? 'All Campaigns' : $option); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                    <button type="submit" class="secondary-btn">Apply</button>
                </form>

                <div class="table-wrap">
                    <table>
                        <thead>
                            <tr>
                                <th>Donor</th>
                                <th>Campaign</th>
                                <th>Amount</th>
                                <th>Date</th>
                                <th>Method</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody id="donationTableBody">
                            <?php if (empty($donations)): ?>
                                <tr>
                                    <td colspan="6" class="empty-state">No donations found.</td>
                                </tr>
                            <?php else: ?>
                                <?php foreach ($donations as $donation): ?>
                                    <tr>
                                        <td>
                                            <div class="donor-name"><?php echo htmlspecialchars($donation['donor_name']); ?></div>
                                            <small><?php echo htmlspecialchars($donation['email']); ?></small>
                                        </td>
                                        <td><span class="campaign-tag <?php echo $donation['campaign'] === 'General Fund' ? 'green' : 'orange'; ?>"><?php echo htmlspecialchars($donation['campaign']); ?></span></td>
                                        <td><?php echo formatCurrency((float) $donation['amount']); ?></td>
                                        <td><?php echo htmlspecialchars(date('M d, Y', strtotime($donation['donation_date']))); ?></td>
                                        <td><?php echo htmlspecialchars($donation['payment_method']); ?></td>
                                        <td>
                                            <a href="index.php?delete=<?php echo (int) $donation['id']; ?>" class="delete-btn" onclick="return confirm('Delete this donation?');">Delete</a>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </section>
        </main>
    </div>

    <script src="assets/js/script.js"></script>
</body>
</html>
