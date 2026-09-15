<?php

require_once "../includes/db.php";
require_once "../includes/functions.php";
require_once "../includes/auth.php";

require_admin();


// ======================================================
// COUNTRY STATISTICS
// ======================================================

$countries = [];


// ======================================================
// GET USERS BY COUNTRY
// ======================================================

$result = $conn->query(
    "SELECT
        country,
        COUNT(*) AS total
     FROM users
     WHERE country IS NOT NULL
       AND TRIM(country) != ''
     GROUP BY country
     ORDER BY total DESC, country ASC"
);

if ($result) {

    while ($row = $result->fetch_assoc()) {

        $countries[] = [
            'country' => trim($row['country']),
            'total'   => (int) $row['total']
        ];
    }
}


// ======================================================
// TOTAL USERS
// ======================================================

$total_users = 0;

$result = $conn->query(
    "SELECT COUNT(*) AS total
     FROM users"
);

if ($result) {

    $row = $result->fetch_assoc();

    $total_users = (int) $row['total'];
}


// ======================================================
// USERS WITH COUNTRY
// ======================================================

$users_with_country = 0;

$result = $conn->query(
    "SELECT COUNT(*) AS total
     FROM users
     WHERE country IS NOT NULL
       AND TRIM(country) != ''"
);

if ($result) {

    $row = $result->fetch_assoc();

    $users_with_country = (int) $row['total'];
}


// ======================================================
// USERS WITHOUT COUNTRY
// ======================================================

$users_without_country = max(
    0,
    $total_users - $users_with_country
);


// ======================================================
// COUNTRY COUNT
// ======================================================

$total_countries = count($countries);


// ======================================================
// TOP COUNTRY
// ======================================================

$top_country = "None";
$top_country_users = 0;

if (!empty($countries)) {

    $top_country = $countries[0]['country'];

    $top_country_users = $countries[0]['total'];
}


// ======================================================
// PERCENTAGE
// ======================================================

function country_percentage($count, $total)
{
    if ($total <= 0) {
        return 0;
    }

    return round(
        ($count / $total) * 100,
        1
    );
}


// ======================================================
// CHART DATA
// ======================================================

$chart_labels = [];
$chart_values = [];

foreach ($countries as $country) {

    $chart_labels[] = $country['country'];

    $chart_values[] = (int) $country['total'];
}

?>

<!DOCTYPE html>
<html lang="en">

<head>

    <meta charset="UTF-8">

    <meta
        name="viewport"
        content="width=device-width, initial-scale=1.0"
    >

    <title>
        Users by Country - Admin Panel
    </title>

    <link
        rel="stylesheet"
        href="css/admin.css"
    >

    <style>

        /* ==================================================
           COUNTRY PAGE
        ================================================== */

        .country-page {

            width: 100%;

        }


        /* ==================================================
           HEADER ACTIONS
        ================================================== */

        .country-page-header {

            display: flex;

            align-items: center;

            justify-content: space-between;

            gap: 20px;

            margin-bottom: 24px;

        }


        .country-back-btn {

            display: inline-flex;

            align-items: center;

            gap: 8px;

            padding: 10px 15px;

            border-radius: 11px;

            background: rgba(255,255,255,.85);

            border: 1px solid rgba(226,232,240,.9);

            color: #374151;

            text-decoration: none;

            font-size: 12px;

            font-weight: 800;

            box-shadow:
                0 5px 18px rgba(15,23,42,.05);

            transition: .2s ease;

        }


        .country-back-btn:hover {

            background: #ffffff;

            transform: translateX(-2px);

            box-shadow:
                0 8px 22px rgba(15,23,42,.08);

        }


        /* ==================================================
           SUMMARY
        ================================================== */

        .country-summary-grid {

            display: grid;

            grid-template-columns:
                repeat(4, minmax(0, 1fr));

            gap: 17px;

            margin-bottom: 25px;

        }


        .country-summary-card {

            position: relative;

            overflow: hidden;

            min-height: 150px;

            padding: 21px;

            border-radius: 19px;

            background:
                linear-gradient(
                    135deg,
                    rgba(255,255,255,.97),
                    rgba(248,250,252,.90)
                );

            border: 1px solid rgba(255,255,255,.85);

            box-shadow:
                0 10px 30px rgba(15,23,42,.07);

            backdrop-filter: blur(14px);

            transition:
                transform .2s ease,
                box-shadow .2s ease;

        }


        .country-summary-card::after {

            content: "";

            position: absolute;

            width: 85px;

            height: 85px;

            right: -30px;

            bottom: -35px;

            border-radius: 50%;

            background:
                rgba(0,107,63,.06);

        }


        .country-summary-card:hover {

            transform: translateY(-3px);

            box-shadow:
                0 15px 35px rgba(15,23,42,.10);

        }


        .country-summary-icon {

            width: 43px;

            height: 43px;

            display: flex;

            align-items: center;

            justify-content: center;

            border-radius: 13px;

            background: #ecfdf5;

            font-size: 20px;

            margin-bottom: 13px;

        }


        .country-summary-title {

            color: #6b7280;

            font-size: 10px;

            font-weight: 800;

            letter-spacing: .75px;

            text-transform: uppercase;

            margin-bottom: 6px;

        }


        .country-summary-number {

            color: #111827;

            font-size: 27px;

            line-height: 1.1;

            font-weight: 850;

        }


        .country-summary-link {

            display: block;

            margin-top: 7px;

            color: #9ca3af;

            font-size: 11px;

            text-decoration: none;

        }


        .country-summary-card a {

            text-decoration: none;

            color: inherit;

        }


        /* ==================================================
           TOP COUNTRY
        ================================================== */

        .country-top-card {

            margin-bottom: 25px;

            padding: 21px 24px;

            border-radius: 18px;

            background:
                linear-gradient(
                    135deg,
                    #006b3f,
                    #075b3c
                );

            color: #ffffff;

            box-shadow:
                0 12px 30px rgba(0,107,63,.18);

            position: relative;

            overflow: hidden;

        }


        .country-top-card::after {

            content: "";

            position: absolute;

            width: 180px;

            height: 180px;

            right: -60px;

            top: -90px;

            border-radius: 50%;

            background:
                rgba(252,209,22,.13);

        }


        .country-top-label {

            font-size: 10px;

            font-weight: 800;

            letter-spacing: 1px;

            opacity: .75;

            text-transform: uppercase;

        }


        .country-top-content {

            display: flex;

            align-items: center;

            justify-content: space-between;

            gap: 20px;

            margin-top: 8px;

        }


        .country-top-name {

            font-size: 22px;

            font-weight: 850;

        }


        .country-top-users {

            font-size: 13px;

            font-weight: 700;

            opacity: .85;

        }


        .country-top-number {

            font-size: 29px;

            font-weight: 850;

        }


        /* ==================================================
           MAIN PANEL
        ================================================== */

        .country-panel {

            background:
                rgba(255,255,255,.93);

            border:
                1px solid rgba(226,232,240,.9);

            border-radius: 20px;

            padding: 25px;

            box-shadow:
                0 12px 35px rgba(15,23,42,.06);

            backdrop-filter: blur(15px);

            margin-bottom: 25px;

        }


        .country-panel-header {

            display: flex;

            align-items: center;

            justify-content: space-between;

            gap: 20px;

            margin-bottom: 22px;

        }


        .country-panel-header h2 {

            margin: 0;

            color: #111827;

            font-size: 21px;

        }


        .country-panel-header p {

            margin: 6px 0 0;

            color: #6b7280;

            font-size: 13px;

        }


        .country-count-badge {

            padding: 7px 11px;

            border-radius: 20px;

            background: #f0fdf4;

            color: #047857;

            font-size: 11px;

            font-weight: 800;

            white-space: nowrap;

        }


        /* ==================================================
           TABLE
        ================================================== */

        .country-table-wrapper {

            width: 100%;

            overflow-x: auto;

            border:
                1px solid #e5e7eb;

            border-radius: 15px;

        }


        .country-table {

            width: 100%;

            min-width: 750px;

            border-collapse: collapse;

            background: #ffffff;

        }


        .country-table th {

            padding: 14px 16px;

            text-align: left;

            background: #f8fafc;

            border-bottom:
                1px solid #e5e7eb;

            color: #6b7280;

            font-size: 10px;

            font-weight: 800;

            letter-spacing: .7px;

            text-transform: uppercase;

        }


        .country-table td {

            padding: 15px 16px;

            border-bottom:
                1px solid #f0f1f3;

            color: #374151;

            font-size: 13px;

            vertical-align: middle;

        }


        .country-table tbody tr {

            transition: background .15s ease;

        }


        .country-table tbody tr:hover {

            background: #fbfdfc;

        }


        .country-table tbody tr:last-child td {

            border-bottom: none;

        }


        /* ==================================================
           RANK
        ================================================== */

        .country-rank {

            width: 34px;

            height: 34px;

            display: inline-flex;

            align-items: center;

            justify-content: center;

            border-radius: 10px;

            background: #f3f4f6;

            color: #4b5563;

            font-size: 11px;

            font-weight: 850;

        }


        .country-rank.first {

            background: #fef9c3;

            color: #854d0e;

        }


        .country-rank.second {

            background: #f1f5f9;

            color: #475569;

        }


        .country-rank.third {

            background: #fff7ed;

            color: #9a3412;

        }


        /* ==================================================
           COUNTRY NAME
        ================================================== */

        .country-name-cell {

            display: flex;

            align-items: center;

            gap: 10px;

        }


        .country-globe {

            width: 34px;

            height: 34px;

            display: flex;

            align-items: center;

            justify-content: center;

            border-radius: 10px;

            background: #ecfdf5;

            font-size: 16px;

        }


        .country-name {

            color: #111827;

            font-size: 13px;

            font-weight: 800;

        }


        /* ==================================================
           USERS
        ================================================== */

        .country-user-count {

            color: #006b3f;

            font-size: 13px;

            font-weight: 850;

        }


        .country-percentage {

            color: #6b7280;

            font-size: 12px;

            font-weight: 700;

        }


        /* ==================================================
           DISTRIBUTION
        ================================================== */

        .country-distribution {

            display: flex;

            align-items: center;

            gap: 11px;

            min-width: 220px;

        }


        .country-progress {

            width: 160px;

            height: 7px;

            overflow: hidden;

            border-radius: 20px;

            background: #e5e7eb;

        }


        .country-progress-bar {

            height: 100%;

            border-radius: inherit;

            background:
                linear-gradient(
                    90deg,
                    #006b3f,
                    #fcd116
                );

        }


        .country-progress-value {

            color: #6b7280;

            font-size: 11px;

            font-weight: 700;

        }


        /* ==================================================
           CHART
        ================================================== */

        .country-chart-panel {

            background:
                rgba(255,255,255,.93);

            border:
                1px solid rgba(226,232,240,.9);

            border-radius: 20px;

            padding: 25px;

            box-shadow:
                0 12px 35px rgba(15,23,42,.06);

            backdrop-filter: blur(15px);

        }


        .country-chart-panel h2 {

            margin: 0;

            color: #111827;

            font-size: 19px;

        }


        .country-chart-panel p {

            margin: 6px 0 20px;

            color: #6b7280;

            font-size: 12px;

        }


        .country-chart-wrapper {

            position: relative;

            width: 100%;

            height: 430px;

        }


        /* ==================================================
           EMPTY STATE
        ================================================== */

        .country-empty {

            text-align: center;

            padding: 65px 20px;

        }


        .country-empty-icon {

            width: 60px;

            height: 60px;

            display: flex;

            align-items: center;

            justify-content: center;

            margin: 0 auto 13px;

            border-radius: 17px;

            background: #f3f4f6;

            font-size: 25px;

        }


        .country-empty strong {

            display: block;

            color: #374151;

            font-size: 15px;

            margin-bottom: 5px;

        }


        .country-empty p {

            margin: 0;

            color: #9ca3af;

            font-size: 12px;

        }


        /* ==================================================
           MOBILE
        ================================================== */

        @media (max-width: 1050px) {

            .country-summary-grid {

                grid-template-columns:
                    repeat(2, minmax(0, 1fr));

            }

        }


        @media (max-width: 700px) {

            .country-page-header {

                align-items: flex-start;

                flex-direction: column;

            }


            .country-summary-grid {

                grid-template-columns: 1fr;

            }


            .country-panel,
            .country-chart-panel {

                padding: 18px;

                border-radius: 16px;

            }


            .country-panel-header {

                align-items: flex-start;

                flex-direction: column;

            }


            .country-top-content {

                align-items: flex-start;

                flex-direction: column;

            }


            .country-chart-wrapper {

                height: 350px;

            }

        }

    </style>

</head>


<body>


<div class="admin-layout">


    <!-- ==================================================
         SIDEBAR
    ================================================== -->

    <aside class="admin-sidebar">

        <div class="admin-brand">

            Petition Platform

            <span>
                Administration Panel
            </span>

        </div>


        <div class="admin-menu-title">
            Main
        </div>


        <a href="admin_dashboard.php">
            Dashboard
        </a>


        <div class="admin-menu-title">
            Management
        </div>


        <a href="admin_petitions.php">
            Petitions
        </a>


        <a href="admin_users.php">
            Users
        </a>


        <a href="admin_messages.php">
            Messages
        </a>


        <a href="admin_signatures.php">
            Signatures
        </a>


        <div class="admin-menu-title">
            Analytics
        </div>


        <a
            href="admin_statistics.php"
            class="active"
        >
            Statistics
        </a>


        <div class="admin-menu-title">
            System
        </div>


        <a href="admin_settings.php">
            Settings
        </a>


        <a
            href="admin_logout.php"
            class="admin-logout"
        >
            Logout
        </a>

    </aside>


    <!-- ==================================================
         MAIN
    ================================================== -->

    <main class="admin-main">


        <!-- TOP BAR -->

        <header class="admin-topbar">

            <h2>
                Users by Country
            </h2>

            <div class="admin-user">
                Administrator
            </div>

        </header>


        <!-- ==================================================
             CONTENT
        ================================================== -->

        <div class="admin-content country-page">


            <!-- PAGE HEADER -->

            <div class="country-page-header">

                <div class="admin-welcome">

                    <h1>
                        Users by Country
                    </h1>

                    <p>
                        Detailed breakdown of registered users
                        by country.
                    </p>

                </div>


                <a
                    href="admin_statistics.php"
                    class="country-back-btn"
                >
                    ← Back to Statistics
                </a>

            </div>


            <!-- ==================================================
                 TOP COUNTRY
            ================================================== -->

            <?php if (!empty($countries)): ?>

                <div class="country-top-card">

                    <div class="country-top-label">
                        Largest User Population
                    </div>

                    <div class="country-top-content">

                        <div>

                            <div class="country-top-name">
                                🌍 <?= e($top_country) ?>
                            </div>

                            <div class="country-top-users">
                                Registered users from this country
                            </div>

                        </div>

                        <div class="country-top-number">
                            <?= number_format($top_country_users) ?>
                        </div>

                    </div>

                </div>

            <?php endif; ?>


            <!-- ==================================================
                 SUMMARY CARDS
            ================================================== -->

            <div class="country-summary-grid">


                <!-- COUNTRIES -->

                <div class="country-summary-card">

                    <div class="country-summary-icon">
                        🌍
                    </div>

                    <div class="country-summary-title">
                        Countries Represented
                    </div>

                    <div class="country-summary-number">
                        <?= number_format($total_countries) ?>
                    </div>

                    <span class="country-summary-link">
                        Unique countries
                    </span>

                </div>


                <!-- TOTAL USERS -->

                <a
                    href="admin_users.php"
                    class="country-summary-card"
                >

                    <div class="country-summary-icon">
                        👥
                    </div>

                    <div class="country-summary-title">
                        Total Users
                    </div>

                    <div class="country-summary-number">
                        <?= number_format($total_users) ?>
                    </div>

                    <span class="country-summary-link">
                        Manage Users →
                    </span>

                </a>


                <!-- WITH COUNTRY -->

                <div class="country-summary-card">

                    <div class="country-summary-icon">
                        📍
                    </div>

                    <div class="country-summary-title">
                        Country Provided
                    </div>

                    <div class="country-summary-number">
                        <?= number_format($users_with_country) ?>
                    </div>

                    <span class="country-summary-link">
                        <?= country_percentage(
                            $users_with_country,
                            $total_users
                        ) ?>% of users
                    </span>

                </div>


                <!-- WITHOUT COUNTRY -->

                <div class="country-summary-card">

                    <div class="country-summary-icon">
                        ❓
                    </div>

                    <div class="country-summary-title">
                        Country Not Provided
                    </div>

                    <div class="country-summary-number">
                        <?= number_format($users_without_country) ?>
                    </div>

                    <span class="country-summary-link">
                        <?= country_percentage(
                            $users_without_country,
                            $total_users
                        ) ?>% of users
                    </span>

                </div>


            </div>


            <!-- ==================================================
                 COUNTRY BREAKDOWN
            ================================================== -->

            <div class="country-panel">


                <div class="country-panel-header">

                    <div>

                        <h2>
                            Country Breakdown
                        </h2>

                        <p>
                            All countries represented by
                            registered users.
                        </p>

                    </div>


                    <div class="country-count-badge">

                        <?= number_format($total_countries) ?>
                        countries

                    </div>

                </div>


                <?php if (!empty($countries)): ?>

                    <div class="country-table-wrapper">

                        <table class="country-table">

                            <thead>

                                <tr>

                                    <th>
                                        Rank
                                    </th>

                                    <th>
                                        Country
                                    </th>

                                    <th>
                                        Users
                                    </th>

                                    <th>
                                        Percentage
                                    </th>

                                    <th>
                                        Distribution
                                    </th>

                                </tr>

                            </thead>


                            <tbody>


                            <?php

                            $rank = 1;

                            foreach ($countries as $country):

                                $percentage =
                                    country_percentage(
                                        $country['total'],
                                        $total_users
                                    );


                                $rank_class = '';

                                if ($rank === 1) {
                                    $rank_class = 'first';
                                } elseif ($rank === 2) {
                                    $rank_class = 'second';
                                } elseif ($rank === 3) {
                                    $rank_class = 'third';
                                }

                            ?>

                                <tr>


                                    <!-- RANK -->

                                    <td>

                                        <span
                                            class="country-rank <?= $rank_class ?>"
                                        >
                                            <?= $rank ?>
                                        </span>

                                    </td>


                                    <!-- COUNTRY -->

                                    <td>

                                        <div
                                            class="country-name-cell"
                                        >

                                            <div
                                                class="country-globe"
                                            >
                                                🌍
                                            </div>

                                            <div
                                                class="country-name"
                                            >
                                                <?= e(
                                                    $country['country']
                                                ) ?>
                                            </div>

                                        </div>

                                    </td>


                                    <!-- USERS -->

                                    <td>

                                        <span
                                            class="country-user-count"
                                        >
                                            <?= number_format(
                                                $country['total']
                                            ) ?>
                                        </span>

                                    </td>


                                    <!-- PERCENTAGE -->

                                    <td>

                                        <span
                                            class="country-percentage"
                                        >
                                            <?= number_format(
                                                $percentage,
                                                1
                                            ) ?>%
                                        </span>

                                    </td>


                                   <!-- DISTRIBUTION -->

                                <td>

                                    <div class="country-distribution">

                                        <div class="country-progress">

                                <div
                                        class="country-progress-bar"
                                        style="width: <?= min(100, max(0, (float) $percentage)) ?>%;"
                                 ></div>

                                    </div>

                                 </div>

                                            </td>

                                            <span
                                                class="country-progress-value"
                                            >
                                                <?= number_format(
                                                    $percentage,
                                                    1
                                                ) ?>%
                                            </span>

                                        </div>

                                    </td>


                                </tr>


                            <?php

                                $rank++;

                            endforeach;

                            ?>


                            </tbody>

                        </table>

                    </div>


                <?php else: ?>


                    <div class="country-empty">

                        <div class="country-empty-icon">
                            🌍
                        </div>

                        <strong>
                            No country information available
                        </strong>

                        <p>
                            Country statistics will appear here
                            once users provide country information.
                        </p>

                    </div>


                <?php endif; ?>


            </div>


            <!-- ==================================================
                 COUNTRY CHART
            ================================================== -->

            <?php if (!empty($countries)): ?>

                <div class="country-chart-panel">

                    <h2>
                        User Distribution by Country
                    </h2>

                    <p>
                        Visual comparison of registered users
                        across all represented countries.
                    </p>


                    <div class="country-chart-wrapper">

                        <canvas
                            id="countryChart"
                        ></canvas>

                    </div>

                </div>

            <?php endif; ?>


        </div>


    </main>


</div>


<!-- ==================================================
     CHART.JS
================================================== -->

<?php if (!empty($countries)): ?>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

<script>

const countryLabels =
    <?= json_encode(
        $chart_labels,
        JSON_UNESCAPED_UNICODE
    ) ?>;

const countryValues =
    <?= json_encode(
        $chart_values
    ) ?>;


const chartElement =
    document.getElementById(
        "countryChart"
    );


if (chartElement) {

    const chartContext =
        chartElement.getContext("2d");


    new Chart(
        chartContext,
        {

            type: "bar",

            data: {

                labels: countryLabels,

                datasets: [

                    {

                        label: "Registered Users",

                        data: countryValues,

                        borderWidth: 0,

                        borderRadius: 6,

                        barThickness: 18

                    }

                ]

            },


            options: {

                indexAxis: "y",

                responsive: true,

                maintainAspectRatio: false,


                plugins: {

                    legend: {

                        display: false

                    },


                    tooltip: {

                        callbacks: {

                            label: function(context) {

                                return (
                                    " " +
                                    Number(
                                        context.raw
                                    ).toLocaleString() +
                                    " users"
                                );

                            }

                        }

                    }

                },


                scales: {

                    x: {

                        beginAtZero: true,

                        ticks: {

                            precision: 0

                        },

                        grid: {

                            drawBorder: false

                        }

                    },


                    y: {

                        ticks: {

                            autoSkip: false

                        },

                        grid: {

                            display: false

                        }

                    }

                }

            }

        }
    );

}

</script>

<?php endif; ?>


</body>

</html>