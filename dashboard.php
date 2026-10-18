
<?php
require_once 'includes/functions.php';
require_login();

$uid = current_user_id();
$role = current_role();

$weather = null;
$weatherError = '';

$location = trim($_GET['location'] ?? 'Vavuniya');

$apiKey = 'ccf7d77b0cf9c91d35ccbd4c431fe0bd';

$locations = [
    'Vavuniya' => 'Vavuniya',
    'Colombo' => 'Colombo',
    'Kandy' => 'Kandy',
    'Jaffna' => 'Jaffna',
    'Galle' => 'Galle',
    'Anuradhapura' => 'Anuradhapura',
    'Kurunegala' => 'Kurunegala',
    'Homagama' => 'Homagama'
];



if (isset($_GET['check_weather'])) {
    if (!array_key_exists($location, $locations)) {
        $weatherError = 'Please select a valid location.';
    } else {
        $url = 'https://api.openweathermap.org/data/2.5/weather?q='
            . urlencode($location . ',LK')
            . '&appid=' . urlencode($apiKey)
            . '&units=metric';

        $context = stream_context_create([
            'http' => [
                'timeout' => 10,
                'ignore_errors' => true
            ]
        ]);

        $response = @file_get_contents($url, false, $context);

        if ($response === false) {
            $weatherError =
                'Unable to connect to the weather service. Check your internet connection.';
        } else {
            $data = json_decode($response, true);

            if (($data['cod'] ?? '') != 200) {
                $weatherError =
                    $data['message'] ?? 'Unable to retrieve weather data.';
            } else {
                $weather = [
                    'city' => $data['name'] ?? $location,
                    'temp' => $data['main']['temp'] ?? 0,
                    'feels_like' => $data['main']['feels_like'] ?? 0,
                    'humidity' => $data['main']['humidity'] ?? 0,
                    'wind' => $data['wind']['speed'] ?? 0,
                    'condition' => ucfirst(
                        $data['weather'][0]['main'] ?? 'Unknown'
                    ),
                    'description' => ucfirst(
                        $data['weather'][0]['description'] ?? ''
                    )
                ];

                $condition = strtolower($weather['condition']);

                if (in_array($condition, ['thunderstorm', 'tornado'], true)) {
                    $weather['advice'] = 'NOT RECOMMENDED';
                    $weather['advice_text'] =
                        'Stop exposed outdoor work and follow site safety procedures.';
                } elseif (in_array(
                    $condition,
                    ['rain', 'drizzle', 'snow'],
                    true
                )) {
                    $weather['advice'] = 'USE CAUTION';
                    $weather['advice_text'] =
                        'Precipitation may make surfaces slippery and affect excavation, concrete work and machinery. Assess site conditions before continuing.';
                } elseif (in_array(
                    $condition,
                    ['clear', 'clouds'],
                    true
                )) {
                    $weather['advice'] = 'GENERALLY SUITABLE';
                    $weather['advice_text'] =
                        'Conditions may be suitable for outdoor work. Check heat, wind, lightning risk and site safety before starting.';
                } else {
                    $weather['advice'] = 'CHECK SITE CONDITIONS';
                    $weather['advice_text'] =
                        'Review current weather and site hazards before approving construction activities.';
                }
            }
        }
    }
}



if ($role === 'admin') {
    $projects = (int)$pdo->query(
        'SELECT COUNT(*) FROM projects'
    )->fetchColumn();

    $users = (int)$pdo->query(
        'SELECT COUNT(*) FROM users'
    )->fetchColumn();

    $equipment = (int)$pdo->query(
        'SELECT COUNT(*) FROM equipment'
    )->fetchColumn();

    $pending = (int)$pdo->query(
        "SELECT COUNT(*) FROM rentals WHERE status = 'Pending'"
    )->fetchColumn();

    $totalRentals = (int)$pdo->query(
        'SELECT COUNT(*) FROM rentals'
    )->fetchColumn();

} else {
    $stmt = $pdo->prepare(
        'SELECT COUNT(*) FROM projects WHERE client_id = ?'
    );
    $stmt->execute([$uid]);
    $projects = (int)$stmt->fetchColumn();

    $stmt = $pdo->prepare(
        'SELECT COUNT(*) FROM rentals WHERE client_id = ?'
    );
    $stmt->execute([$uid]);
    $totalRentals = (int)$stmt->fetchColumn();

    $stmt = $pdo->prepare(
        "SELECT COUNT(*) FROM rentals
         WHERE client_id = ? AND status = 'Pending'"
    );
    $stmt->execute([$uid]);
    $pending = (int)$stmt->fetchColumn();

    $equipment = (int)$pdo->query(
        'SELECT COUNT(*) FROM equipment'
    )->fetchColumn();

    $users = 0;
}



$availabilityLabels = [];
$availabilityValues = [];

$availabilityStmt = $pdo->query(
    'SELECT availability, COUNT(*) AS total
     FROM equipment
     GROUP BY availability'
);

foreach ($availabilityStmt->fetchAll(PDO::FETCH_ASSOC) as $row) {
    $availabilityLabels[] = $row['availability'] ?: 'Unknown';
    $availabilityValues[] = (int)$row['total'];
}



$rentalLabels = [];
$rentalValues = [];

if ($role === 'admin') {
    $rentalStmt = $pdo->query(
        'SELECT status, COUNT(*) AS total
         FROM rentals
         GROUP BY status'
    );
} else {
    $rentalStmt = $pdo->prepare(
        'SELECT status, COUNT(*) AS total
         FROM rentals
         WHERE client_id = ?
         GROUP BY status'
    );
    $rentalStmt->execute([$uid]);
}

foreach ($rentalStmt->fetchAll(PDO::FETCH_ASSOC) as $row) {
    $rentalLabels[] = $row['status'] ?: 'Unknown';
    $rentalValues[] = (int)$row['total'];
}

$summaryLabels = ['Projects', 'Equipment', 'Rental Requests'];

$summaryValues = [
    $projects,
    $equipment,
    $totalRentals
];

require_once 'includes/header.php';
?>

<div class="page-heading">
    <div>
        <p class="eyebrow">YOUR WORKSPACE</p>
        <h1>Hello, <?= clean($_SESSION['user']['name']) ?>!</h1>
        <p>
            Account type:
            <b><?= clean(ucfirst($role)) ?></b>
        </p>
    </div>
</div>



<section class="stats">

    <article class="stat">
        <span>Projects <?= $role === 'admin' ? 'Total' : 'Assigned' ?></span>
        <strong><?= $projects ?></strong>
        <small class="stat-caption">Project overview</small>
    </article>

    <article class="stat">
        <span>Equipment Listings</span>
        <strong><?= $equipment ?></strong>
        <small class="stat-caption">Registered equipment</small>
    </article>

    <article class="stat">
        <span>Pending Rentals</span>
        <strong><?= $pending ?></strong>
        <small class="stat-caption">Awaiting a decision</small>
    </article>

    <?php if ($role === 'admin'): ?>
        <article class="stat">
            <span>Registered Users</span>
            <strong><?= $users ?></strong>
            <small class="stat-caption">System accounts</small>
        </article>
    <?php endif; ?>

</section>



<section class="construction-banner">
    <div class="banner-content">
        <span class="banner-tag">CONSTRUCTION MANAGEMENT</span>

        <h2>Build Smarter.<br>Manage Better.</h2>

        <p>
            Manage construction projects, monitor equipment availability
            and track rental requests from one dashboard.
        </p>

        <a href="equipment.php" class="banner-button">
            Explore Equipment &rarr;
        </a>
    </div>

    <div class="banner-image"
         role="img"
         aria-label="Construction site and heavy machinery">
    </div>
</section>







<?php if ($role === 'admin'): ?>



<section class="weather-card">

    <div class="weather-title-row">
        <div>
            <h2>🌦️ Live Construction Weather</h2>
            <p>
                Check current weather conditions before planning
                construction activities at your selected location.
            </p>
        </div>

        <div class="weather-title-icon">☁️</div>
    </div>

    <form method="GET" action="dashboard.php" class="weather-form">

        <div class="location-field">
            <label for="location">Select Construction Location</label>

            <select name="location" id="location" required>
                <?php foreach ($locations as $key => $value): ?>
                    <option
                        value="<?= clean($key) ?>"
                        <?= $location === $key ? 'selected' : '' ?>>
                        <?= clean($value) ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>

        <button
            type="submit"
            name="check_weather"
            value="1"
            class="button">
            Check Weather
        </button>
    </form>

    <?php if ($weatherError): ?>
        <div class="weather-error">
            <strong>Weather unavailable:</strong>
            <?= clean($weatherError) ?>
        </div>
    <?php endif; ?>

    <?php if ($weather): ?>

        <div class="weather-result">

            <h3>
                📍 <?= clean($weather['city']) ?> — Current Weather
            </h3>

            <div class="weather-condition">
                <div class="weather-temperature">
                    <?= round($weather['temp'], 1) ?>°C
                </div>

                <div>
                    <strong><?= clean($weather['condition']) ?></strong>
                    <div class="weather-description">
                        <?= clean($weather['description']) ?>
                    </div>
                </div>
            </div>

            <div class="weather-details">

                <div class="weather-detail">
                    <span>🌡️ Feels Like</span>
                    <strong>
                        <?= round($weather['feels_like'], 1) ?>°C
                    </strong>
                </div>

                <div class="weather-detail">
                    <span>💧 Humidity</span>
                    <strong><?= (int)$weather['humidity'] ?>%</strong>
                </div>

                <div class="weather-detail">
                    <span>💨 Wind Speed</span>
                    <strong><?= round($weather['wind'], 1) ?> m/s</strong>
                </div>

            </div>

            <?php
            $adviceClass = 'caution';

            if ($weather['advice'] === 'GENERALLY SUITABLE') {
                $adviceClass = 'suitable';
            } elseif ($weather['advice'] === 'NOT RECOMMENDED') {
                $adviceClass = 'unsafe';
            }
            ?>

            <div class="weather-advice <?= $adviceClass ?>">
                <h3>
                    🏗️ Construction Recommendation:
                    <?= clean($weather['advice']) ?>
                </h3>

                <p><?= clean($weather['advice_text']) ?></p>
            </div>

            <small class="weather-updated">
                Live weather provided by OpenWeather.
                Last checked: <?= date('Y-m-d H:i:s') ?>
            </small>

        </div>

    <?php endif; ?>

</section>

<?php endif; ?>


<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

<script>
document.addEventListener('DOMContentLoaded', function () {

    const summaryLabels = <?= json_encode(
        $summaryLabels,
        JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP
    ) ?>;

    const summaryValues = <?= json_encode($summaryValues) ?>;

    const availabilityLabels = <?= json_encode(
        $availabilityLabels,
        JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP
    ) ?>;

    const availabilityValues = <?= json_encode($availabilityValues) ?>;

    const rentalLabels = <?= json_encode(
        $rentalLabels,
        JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP
    ) ?>;

    const rentalValues = <?= json_encode($rentalValues) ?>;

    if (typeof Chart === 'undefined') {
        return;
    }

    Chart.defaults.font.family = 'Arial, Helvetica, sans-serif';
    Chart.defaults.color = '#667487';

    const palette = [
        '#f28b30',
        '#234e70',
        '#36a477',
        '#8b78c6',
        '#e05d5d',
        '#59a9c5',
        '#a3aab5'
    ];

    // Management overview
    new Chart(document.getElementById('summaryChart'), {
        type: 'bar',
        data: {
            labels: summaryLabels,
            datasets: [{
                label: 'Total',
                data: summaryValues,
                backgroundColor: ['#234e70', '#f28b30', '#36a477'],
                borderRadius: 8,
                maxBarThickness: 52
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: { display: false }
            },
            scales: {
                y: {
                    beginAtZero: true,
                    ticks: { precision: 0 }
                },
                x: {
                    grid: { display: false }
                }
            }
        }
    });

    // Equipment availability
    if (availabilityLabels.length > 0) {
        new Chart(document.getElementById('availabilityChart'), {
            type: 'doughnut',
            data: {
                labels: availabilityLabels,
                datasets: [{
                    data: availabilityValues,
                    backgroundColor: availabilityLabels.map(function (label, i) {
                        const value = String(label).toLowerCase();

                        if (value === 'available') return '#36a477';
                        if (value === 'unavailable') return '#f28b30';

                        return palette[i % palette.length];
                    }),
                    borderColor: '#ffffff',
                    borderWidth: 4,
                    hoverOffset: 8
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                cutout: '65%',
                plugins: {
                    legend: {
                        position: 'bottom',
                        labels: {
                            usePointStyle: true,
                            padding: 18
                        }
                    }
                }
            }
        });
    } else {
        document.getElementById('availabilityChart').hidden = true;
        document.getElementById('availabilityEmpty').hidden = false;
    }

    // Rental status
    if (rentalLabels.length > 0) {
        new Chart(document.getElementById('rentalChart'), {
            type: 'doughnut',
            data: {
                labels: rentalLabels,
                datasets: [{
                    data: rentalValues,
                    backgroundColor: rentalLabels.map(function (_, i) {
                        return palette[i % palette.length];
                    }),
                    borderColor: '#ffffff',
                    borderWidth: 4,
                    hoverOffset: 8
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                cutout: '65%',
                plugins: {
                    legend: {
                        position: 'bottom',
                        labels: {
                            usePointStyle: true,
                            padding: 18
                        }
                    }
                }
            }
        });
    } else {
        document.getElementById('rentalChart').hidden = true;
        document.getElementById('rentalEmpty').hidden = false;
    }
});
</script>
<?php include 'approval_notification.php'; ?>
<?php require_once 'includes/footer.php'; ?>
