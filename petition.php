<?php

require_once "includes/db.php";
require_once "includes/functions.php";
require_once "includes/auth.php";


// ======================================================
// LOGIN STATE
// ======================================================

$user_logged_in = is_user_logged_in();


// ======================================================
// GET PETITION ID
// ======================================================

$petition_id = isset($_GET['id'])
    ? (int) $_GET['id']
    : 0;


if ($petition_id < 1) {

    http_response_code(404);

    exit("Petition not found.");

}


// ======================================================
// CATEGORY DETECTION
// ======================================================

function detect_petition_category($title, $description, $database_category = '')
{
    $title = trim((string) $title);
    $description = trim((string) $description);
    $database_category = trim((string) $database_category);

    $text = strtolower($title . ' ' . $description);


    if (
        $database_category !== ''
        &&
        strtolower($database_category) !== 'other'
    ) {

        return $database_category;

    }


    $categories = [

        'Education' => [
            'education',
            'school',
            'schools',
            'student',
            'students',
            'teacher',
            'teachers',
            'university',
            'college',
            'classroom',
            'learning',
            'scholarship',
            'exam',
            'exams',
            'teaching',
            'academic'
        ],

        'Environment' => [
            'environment',
            'environmental',
            'climate',
            'pollution',
            'forest',
            'forests',
            'tree',
            'trees',
            'deforestation',
            'wildlife',
            'conservation',
            'ocean',
            'marine',
            'plastic',
            'waste',
            'garbage',
            'recycling',
            'green',
            'nature'
        ],

        'Healthcare' => [
            'health',
            'healthcare',
            'hospital',
            'hospitals',
            'doctor',
            'doctors',
            'nurse',
            'nurses',
            'medical',
            'medicine',
            'medication',
            'clinic',
            'clinics',
            'patient',
            'patients',
            'disease',
            'treatment',
            'maternal'
        ],

        'Human Rights' => [
            'human rights',
            'rights',
            'equality',
            'equal rights',
            'discrimination',
            'freedom',
            'justice',
            'fairness',
            'minority',
            'minorities',
            'women rights',
            'child rights',
            'civil rights',
            'dignity',
            'oppression'
        ],

        'Government & Public Services' => [
            'government',
            'president',
            'parliament',
            'minister',
            'ministry',
            'politician',
            'politicians',
            'public service',
            'public services',
            'accountability',
            'corruption',
            'transparency',
            'election',
            'elections',
            'local government',
            'council',
            'municipality',
            'district',
            'policy',
            'law',
            'laws',
            'legislation',
            'tax',
            'taxes',
            'public money',
            'citizen'
        ],

        'Community' => [
            'community',
            'neighborhood',
            'neighbourhood',
            'village',
            'villages',
            'street',
            'streets',
            'residents',
            'residential',
            'local',
            'society',
            'community center',
            'community centre',
            'public space'
        ],

        'Employment & Workers' => [
            'job',
            'jobs',
            'employment',
            'worker',
            'workers',
            'employee',
            'employees',
            'employer',
            'salary',
            'salaries',
            'wage',
            'wages',
            'workplace',
            'labour',
            'labor',
            'unemployment',
            'working conditions',
            'workers rights'
        ],

        'Transport & Roads' => [
            'transport',
            'road',
            'roads',
            'highway',
            'highways',
            'traffic',
            'bus',
            'buses',
            'daladala',
            'train',
            'trains',
            'railway',
            'railways',
            'airport',
            'airports',
            'driving',
            'vehicle',
            'vehicles',
            'parking',
            'bridge',
            'bridges',
            'public transport'
        ],

        'Housing & Infrastructure' => [
            'housing',
            'house',
            'houses',
            'home',
            'homes',
            'rent',
            'rental',
            'land',
            'building',
            'buildings',
            'construction',
            'infrastructure',
            'electricity',
            'power',
            'water supply',
            'sewer',
            'sewage',
            'drainage'
        ],

        'Animal Welfare' => [
            'animal',
            'animals',
            'dog',
            'dogs',
            'cat',
            'cats',
            'livestock',
            'pets',
            'pet',
            'animal welfare',
            'animal rights',
            'wildlife'
        ],

        'Business & Economy' => [
            'business',
            'businesses',
            'economy',
            'economic',
            'market',
            'markets',
            'traders',
            'trade',
            'investment',
            'investors',
            'entrepreneur',
            'entrepreneurs',
            'small business',
            'msme',
            'industry',
            'industries'
        ],

        'Technology' => [
            'technology',
            'tech',
            'internet',
            'online',
            'digital',
            'software',
            'computer',
            'computers',
            'phone',
            'phones',
            'mobile',
            'data',
            'privacy',
            'artificial intelligence',
            'ai',
            'website',
            'web'
        ],

        'Safety & Security' => [
            'security',
            'crime',
            'criminal',
            'police',
            'safety',
            'violence',
            'abuse',
            'assault',
            'fraud',
            'scam',
            'scams',
            'emergency',
            'fire',
            'disaster'
        ],

        'Culture & Heritage' => [
            'culture',
            'cultural',
            'heritage',
            'tradition',
            'traditional',
            'history',
            'historical',
            'museum',
            'language',
            'languages',
            'art',
            'arts',
            'music',
            'festival',
            'festivals'
        ],

        'Sports & Recreation' => [
            'sport',
            'sports',
            'football',
            'soccer',
            'basketball',
            'athletics',
            'stadium',
            'team',
            'teams',
            'player',
            'players',
            'recreation',
            'gym',
            'fitness'
        ]

    ];


    $scores = [];


    foreach ($categories as $category => $keywords) {

        $scores[$category] = 0;


        foreach ($keywords as $keyword) {

            $weight =
                strlen($keyword) > 8
                    ? 3
                    : 1;


            if (
                strpos($text, strtolower($keyword))
                !== false
            ) {

                $scores[$category] += $weight;

            }

        }

    }


    $best_category = '';
    $best_score = 0;


    foreach ($scores as $category => $score) {

        if ($score > $best_score) {

            $best_score = $score;
            $best_category = $category;

        }

    }


    if ($best_category === '') {

        $best_category = 'Community';

    }


    return $best_category;
}


// ======================================================
// CATEGORY ICON
// ======================================================

function petition_category_icon($category)
{
    $category = strtolower(trim($category));


    $icons = [

        'education'
            => 'fa-graduation-cap',

        'environment'
            => 'fa-leaf',

        'healthcare'
            => 'fa-heart-pulse',

        'human rights'
            => 'fa-scale-balanced',

        'government & public services'
            => 'fa-landmark',

        'community'
            => 'fa-people-group',

        'employment & workers'
            => 'fa-briefcase',

        'transport & roads'
            => 'fa-road',

        'housing & infrastructure'
            => 'fa-house',

        'animal welfare'
            => 'fa-paw',

        'business & economy'
            => 'fa-chart-line',

        'technology'
            => 'fa-microchip',

        'safety & security'
            => 'fa-shield-halved',

        'culture & heritage'
            => 'fa-landmark-dome',

        'sports & recreation'
            => 'fa-futbol'

    ];


    return isset($icons[$category])
        ? $icons[$category]
        : 'fa-bullhorn';
}


// ======================================================
// GET PETITION
// ======================================================

$sql = "
    SELECT
        p.id,
        p.title,
        p.description,
        p.category,
        p.goal,
        p.status,
        p.created_at,

        (
            SELECT COUNT(*)
            FROM signatures s
            WHERE s.petition_id = p.id
        ) AS signature_count

    FROM petitions p

    WHERE p.id = ?

    LIMIT 1
";


$stmt = $conn->prepare($sql);


if (!$stmt) {

    http_response_code(500);

    exit("Unable to load petition.");

}


$stmt->bind_param(
    "i",
    $petition_id
);


$stmt->execute();


$result = $stmt->get_result();


if ($result->num_rows < 1) {

    $stmt->close();

    http_response_code(404);

    exit("Petition not found.");

}


$petition = $result->fetch_assoc();


$stmt->close();


// ======================================================
// CATEGORY
// ======================================================

$category = detect_petition_category(
    $petition['title'],
    $petition['description'],
    $petition['category']
);


$category_icon = petition_category_icon(
    $category
);


// ======================================================
// SIGNATURES
// ======================================================

$signatures = (int) $petition['signature_count'];


// ======================================================
// GOAL
// ======================================================

$goal = (int) $petition['goal'];


// ======================================================
// PROGRESS
// ======================================================

$percentage = 0;


if ($goal > 0) {

    $percentage =
        ($signatures / $goal) * 100;

    $percentage =
        min(100, $percentage);

}


// ======================================================
// DATE
// ======================================================

$created_date = '';


if (!empty($petition['created_at'])) {

    $created_date = date(
        'F j, Y',
        strtotime($petition['created_at'])
    );

}


// ======================================================
// DESCRIPTION
// ======================================================

$description =
    trim(
        $petition['description'] ?? ''
    );


if ($description === '') {

    $description =
        'Support this petition and help bring attention to this important cause.';

}


// ======================================================
// STATUS
// ======================================================

$petition_status =
    strtolower(
        trim(
            $petition['status'] ?? 'active'
        )
    );


$status_label =
    ucfirst(
        str_replace(
            '_',
            ' ',
            $petition_status
        )
    );


if ($status_label === '') {

    $status_label = 'Active';

}


// ======================================================
// SHARE URL
// ======================================================

$share_url =
    'petition.php?id=' .
    (int) $petition['id'];


// ======================================================
// SIGNATURE REMAINING
// ======================================================

$remaining = 0;


if ($goal > $signatures) {

    $remaining = $goal - $signatures;

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

    <meta
        name="description"
        content="<?= e($petition['title']) ?>"
    >

    <meta
        property="og:title"
        content="<?= e($petition['title']) ?>"
    >

    <meta
        property="og:description"
        content="<?= e($description) ?>"
    >

    <title>
        <?= e($petition['title']) ?> | Petition Platform
    </title>


    <!-- ==================================================
         MAIN CSS
    ================================================== -->

    <link
        rel="stylesheet"
        href="assets/css/style.css"
    >


    <!-- ==================================================
         FONT AWESOME
    ================================================== -->

    <link
        rel="stylesheet"
        href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css"
    >


    <!-- ==================================================
         LOTTIE
    ================================================== -->

    <script
        src="https://cdnjs.cloudflare.com/ajax/libs/bodymovin/5.12.2/lottie.min.js">
    </script>


    <style>

        /* ==================================================
           GLOBAL
        ================================================== */

        * {
            box-sizing: border-box;
        }


        body {

            margin: 0;

            background:
                #f4f7fb;

            color:
                #172033;

        }


        a {
            -webkit-tap-highlight-color:
                transparent;
        }


        /* ==================================================
           NAVIGATION
        ================================================== */

        .petition-navigation {

            position:
                relative;

            width:
                100%;

            min-height:
                72px;

            background:
                rgba(255,255,255,0.96);

            backdrop-filter:
                blur(16px);

            -webkit-backdrop-filter:
                blur(16px);

            border-bottom:
                1px solid rgba(15,23,42,0.08);

            display:
                flex;

            align-items:
                center;

            padding:
                0 24px;

            z-index:
                100;

        }


        .petition-brand {

            position:
                absolute;

            left:
                25px;

            top:
                50%;

            transform:
                translateY(-50%);

            z-index:
                5;

        }


        .petition-brand a {

            color:
                var(--primary);

            text-decoration:
                none;

            font-size:
                20px;

            font-weight:
                900;

            letter-spacing:
                -0.4px;

            white-space:
                nowrap;

        }


        .petition-tabs {

            position:
                absolute;

            left:
                50%;

            top:
                50%;

            transform:
                translate(-50%, -50%);

            display:
                flex;

            align-items:
                center;

            justify-content:
                center;

            gap:
                24px;

            white-space:
                nowrap;

        }


        .petition-tabs a {

            color:
                #475569;

            text-decoration:
                none;

            font-size:
                14px;

            font-weight:
                700;

            padding:
                9px 3px;

            transition:
                0.2s ease;

        }


        .petition-tabs a:hover {

            color:
                var(--primary);

        }


        .petition-tabs .active {

            color:
                var(--primary);

        }


        .petition-register {

            background:
                var(--gold);

            color:
                #111827 !important;

            padding:
                9px 15px !important;

            border-radius:
                8px;

        }


        /* ==================================================
           TANZANIA FLAG
        ================================================== */

        .petition-flag {

            position:
                absolute;

            right:
                18px;

            top:
                50%;

            transform:
                translateY(-50%);

            width:
                58px;

            height:
                58px;

            display:
                flex;

            align-items:
                center;

            justify-content:
                center;

            overflow:
                hidden;

            z-index:
                10;

        }


        .petition-flag svg {

            width:
                100%;

            height:
                100%;

            display:
                block;

        }


        /* ==================================================
           HERO
        ================================================== */

        .petition-hero {

            position:
                relative;

            overflow:
                hidden;

            color:
                #ffffff;

            background:
                linear-gradient(
                    135deg,
                    var(--primary-dark),
                    var(--primary)
                );

            padding:
                70px 25px 90px;

            border-bottom:
                4px solid var(--gold);

        }


        .petition-hero::before {

            content:
                "";

            position:
                absolute;

            width:
                420px;

            height:
                420px;

            border-radius:
                50%;

            background:
                rgba(255,255,255,0.06);

            top:
                -220px;

            right:
                -100px;

        }


        .petition-hero::after {

            content:
                "";

            position:
                absolute;

            width:
                300px;

            height:
                300px;

            border-radius:
                50%;

            background:
                rgba(255,255,255,0.04);

            bottom:
                -210px;

            left:
                -100px;

        }


        .petition-hero-inner {

            position:
                relative;

            z-index:
                2;

            max-width:
                1120px;

            margin:
                0 auto;

        }


        .petition-back {

            display:
                inline-flex;

            align-items:
                center;

            gap:
                8px;

            color:
                rgba(255,255,255,0.82);

            text-decoration:
                none;

            font-size:
                14px;

            font-weight:
                800;

            margin-bottom:
                28px;

            transition:
                0.2s ease;

        }


        .petition-back:hover {

            color:
                #ffffff;

            transform:
                translateX(-3px);

        }


        .petition-category-badge {

            display:
                inline-flex;

            align-items:
                center;

            gap:
                8px;

            background:
                rgba(255,255,255,0.12);

            border:
                1px solid rgba(255,255,255,0.20);

            box-shadow:
                0 10px 25px rgba(0,0,0,0.10);

            border-radius:
                999px;

            padding:
                8px 14px;

            font-size:
                12px;

            font-weight:
                800;

            margin-bottom:
                18px;

        }


        .petition-hero h1 {

            color:
                #ffffff;

            font-size:
                clamp(34px, 5vw, 58px);

            line-height:
                1.08;

            letter-spacing:
                -1.5px;

            margin:
                0 0 22px;

            max-width:
                980px;

        }


        .petition-hero-meta {

            display:
                flex;

            align-items:
                center;

            flex-wrap:
                wrap;

            gap:
                10px;

        }


        .petition-meta-item {

            display:
                inline-flex;

            align-items:
                center;

            gap:
                8px;

            padding:
                9px 13px;

            background:
                rgba(255,255,255,0.09);

            border:
                1px solid rgba(255,255,255,0.13);

            border-radius:
                8px;

            color:
                rgba(255,255,255,0.84);

            font-size:
                13px;

        }


        .petition-meta-item i {

            color:
                var(--gold);

        }


        /* ==================================================
           MAIN CONTAINER
        ================================================== */

        .petition-container {

            max-width:
                1120px;

            margin:
                0 auto;

            padding:
                45px 25px 90px;

        }


        .petition-layout {

            display:
                grid;

            grid-template-columns:
                minmax(0, 1fr)
                365px;

            gap:
                28px;

            align-items:
                start;

        }


        /* ==================================================
           GLASS CARD
        ================================================== */

        .petition-glass-card {

            background:
                rgba(255,255,255,0.86);

            border:
                1px solid rgba(255,255,255,0.85);

            box-shadow:
                0 18px 50px rgba(15,23,42,0.07);

            backdrop-filter:
                blur(18px);

            -webkit-backdrop-filter:
                blur(18px);

            border-radius:
                18px;

        }


        /* ==================================================
           DESCRIPTION
        ================================================== */

        .petition-content-card {

            padding:
                32px;

        }


        .petition-status {

            display:
                inline-flex;

            align-items:
                center;

            gap:
                7px;

            padding:
                7px 11px;

            border-radius:
                999px;

            font-size:
                11px;

            font-weight:
                900;

            text-transform:
                uppercase;

            letter-spacing:
                0.4px;

            margin-bottom:
                22px;

        }


        .petition-status-active {

            background:
                #dcfce7;

            color:
                #166534;

        }


        .petition-status-closed {

            background:
                #fee2e2;

            color:
                #991b1b;

        }


        .petition-status-dot {

            width:
                7px;

            height:
                7px;

            border-radius:
                50%;

            background:
                #16a34a;

        }


        .petition-content-heading {

            display:
                flex;

            align-items:
                flex-start;

            justify-content:
                space-between;

            gap:
                20px;

            margin-bottom:
                20px;

        }


        .petition-content-card h2 {

            margin:
                0;

            font-size:
                27px;

            line-height:
                1.2;

            letter-spacing:
                -0.5px;

        }


        .petition-description-full {

            color:
                #526176;

            font-size:
                16px;

            line-height:
                1.85;

            white-space:
                pre-line;

        }


        /* ==================================================
           INFO STRIP
        ================================================== */

        .petition-info-grid {

            display:
                grid;

            grid-template-columns:
                repeat(3, 1fr);

            gap:
                12px;

            margin-top:
                30px;

        }


        .petition-info-box {

            padding:
                17px;

            background:
                #f8fafc;

            border:
                1px solid #e8edf3;

            border-radius:
                13px;

        }


        .petition-info-box i {

            display:
                block;

            color:
                var(--primary);

            font-size:
                17px;

            margin-bottom:
                9px;

        }


        .petition-info-box span {

            display:
                block;

            color:
                #94a3b8;

            font-size:
                11px;

            font-weight:
                700;

            text-transform:
                uppercase;

            letter-spacing:
                0.5px;

            margin-bottom:
                3px;

        }


        .petition-info-box strong {

            color:
                #172033;

            font-size:
                14px;

        }


        /* ==================================================
           ACTION CARD
        ================================================== */

        .petition-action-card {

            padding:
                27px;

            position:
                sticky;

            top:
                20px;

        }


        .petition-action-card::before {

            content:
                "";

            display:
                block;

            width:
                55px;

            height:
                4px;

            background:
                var(--gold);

            border-radius:
                999px;

            margin-bottom:
                20px;

        }


        .petition-action-title {

            margin:
                0 0 7px;

            font-size:
                21px;

            letter-spacing:
                -0.3px;

        }


        .petition-action-subtitle {

            color:
                #64748b;

            font-size:
                13px;

            line-height:
                1.6;

            margin:
                0 0 25px;

        }


        /* ==================================================
           SIGNATURE NUMBERS
        ================================================== */

        .petition-number-row {

            display:
                flex;

            align-items:
                flex-end;

            justify-content:
                space-between;

            gap:
                15px;

        }


        .petition-big-number {

            font-size:
                36px;

            line-height:
                1;

            font-weight:
                900;

            letter-spacing:
                -1px;

            color:
                var(--primary);

        }


        .petition-number-label {

            color:
                #64748b;

            font-size:
                12px;

            margin-top:
                6px;

        }


        .petition-target {

            text-align:
                right;

            color:
                #64748b;

            font-size:
                12px;

        }


        .petition-target strong {

            display:
                block;

            color:
                #172033;

            font-size:
                17px;

            margin-top:
                3px;

        }


        /* ==================================================
           PROGRESS
        ================================================== */

        .petition-progress-bar {

            width:
                100%;

            height:
                12px;

            background:
                #e8edf3;

            border-radius:
                999px;

            overflow:
                hidden;

            margin:
                20px 0 11px;

        }


        .petition-progress-fill {

            height:
                100%;

            background:
                linear-gradient(
                    90deg,
                    var(--primary),
                    #22a06b
                );

            border-radius:
                999px;

            min-width:
                4px;

            transition:
                width 0.5s ease;

        }


        .petition-progress-stats {

            display:
                flex;

            align-items:
                center;

            justify-content:
                space-between;

            margin-bottom:
                22px;

        }


        .petition-progress-left {

            color:
                #64748b;

            font-size:
                12px;

        }


        .petition-progress-left strong {

            color:
                #172033;

        }


        .petition-percentage {

            color:
                var(--primary);

            font-weight:
                900;

            font-size:
                13px;

        }


        /* ==================================================
           SIGN BUTTON
        ================================================== */

        .petition-sign-button {

            width:
                100%;

            min-height:
                50px;

            display:
                flex;

            align-items:
                center;

            justify-content:
                center;

            gap:
                9px;

            background:
                var(--primary);

            color:
                #ffffff;

            text-decoration:
                none;

            border:
                none;

            border-radius:
                10px;

            padding:
                14px 18px;

            font-size:
                15px;

            font-weight:
                900;

            box-shadow:
                0 10px 25px rgba(0,0,0,0.12);

            transition:
                0.2s ease;

        }


        .petition-sign-button:hover {

            background:
                var(--primary-dark);

            color:
                #ffffff;

            transform:
                translateY(-2px);

            box-shadow:
                0 14px 30px rgba(0,0,0,0.16);

        }


        .petition-sign-button.disabled {

            background:
                #94a3b8;

            cursor:
                not-allowed;

            box-shadow:
                none;

        }


        .petition-login-note {

            margin:
                11px 0 0;

            text-align:
                center;

            color:
                #94a3b8;

            font-size:
                11px;

            line-height:
                1.5;

        }


        .petition-login-note a {

            color:
                var(--primary);

            font-weight:
                800;

            text-decoration:
                none;

        }


        /* ==================================================
           REMAINING
        ================================================== */

        .petition-remaining {

            margin:
                17px 0 0;

            padding:
                12px 13px;

            border-radius:
                10px;

            background:
                #f8fafc;

            border:
                1px solid #e8edf3;

            color:
                #64748b;

            text-align:
                center;

            font-size:
                12px;

        }


        .petition-remaining strong {

            color:
                #172033;

        }


        /* ==================================================
           CARD DIVIDER
        ================================================== */

        .petition-card-divider {

            height:
                1px;

            background:
                #edf1f5;

            margin:
                23px 0;

        }


        /* ==================================================
           DETAILS
        ================================================== */

        .petition-details-title {

            margin:
                0 0 15px;

            font-size:
                14px;

            font-weight:
                900;

        }


        .petition-detail-row {

            display:
                flex;

            align-items:
                center;

            gap:
                12px;

            padding:
                11px 0;

            color:
                #64748b;

            font-size:
                12px;

        }


        .petition-detail-row i {

            width:
                22px;

            color:
                var(--primary);

            text-align:
                center;

        }


        .petition-detail-row strong {

            color:
                #334155;

        }


        /* ==================================================
           SHARE
        ================================================== */

        .petition-share {

            margin-top:
                16px;

        }


        .petition-share-title {

            color:
                #64748b;

            font-size:
                11px;

            font-weight:
                800;

            text-transform:
                uppercase;

            letter-spacing:
                0.6px;

            margin-bottom:
                10px;

        }


        .petition-share-buttons {

            display:
                grid;

            grid-template-columns:
                repeat(3, 1fr);

            gap:
                8px;

        }


        .petition-share-button {

            height:
                39px;

            display:
                flex;

            align-items:
                center;

            justify-content:
                center;

            border-radius:
                9px;

            border:
                1px solid #e2e8f0;

            background:
                #ffffff;

            color:
                #475569;

            text-decoration:
                none;

            font-size:
                13px;

            transition:
                0.2s ease;

            cursor:
                pointer;

        }


        .petition-share-button:hover {

            border-color:
                var(--primary);

            color:
                var(--primary);

            transform:
                translateY(-1px);

        }


        /* ==================================================
           LOWER CONTENT
        ================================================== */

        .petition-lower-grid {

            display:
                grid;

            grid-template-columns:
                1fr 1fr;

            gap:
                25px;

            margin-top:
                25px;

        }


        .petition-lower-card {

            padding:
                27px;

        }


        .petition-lower-card h3 {

            margin:
                0 0 8px;

            font-size:
                18px;

        }


        .petition-lower-card p {

            margin:
                0;

            color:
                #64748b;

            font-size:
                13px;

            line-height:
                1.7;

        }


        .petition-creator {

            display:
                flex;

            align-items:
                center;

            gap:
                13px;

            margin-top:
                20px;

        }


        .petition-creator-avatar {

            width:
                48px;

            height:
                48px;

            border-radius:
                50%;

            display:
                flex;

            align-items:
                center;

            justify-content:
                center;

            background:
                linear-gradient(
                    135deg,
                    var(--primary),
                    var(--primary-dark)
                );

            color:
                #ffffff;

            font-size:
                18px;

        }


        .petition-creator-info span {

            display:
                block;

            color:
                #94a3b8;

            font-size:
                11px;

            margin-bottom:
                3px;

        }


        .petition-creator-info strong {

            color:
                #334155;

            font-size:
                14px;

        }


        .petition-support-box {

            display:
                flex;

            align-items:
                center;

            gap:
                14px;

            margin-top:
                18px;

            padding:
                15px;

            border-radius:
                12px;

            background:
                #f8fafc;

            border:
                1px solid #e8edf3;

        }


        .petition-support-box i {

            width:
                38px;

            height:
                38px;

            display:
                flex;

            align-items:
                center;

            justify-content:
                center;

            border-radius:
                10px;

            background:
                rgba(0,0,0,0.04);

            color:
                var(--primary);

        }


        .petition-support-box strong {

            display:
                block;

            color:
                #334155;

            font-size:
                13px;

            margin-bottom:
                3px;

        }


        .petition-support-box span {

            color:
                #94a3b8;

            font-size:
                11px;

        }


        /* ==================================================
           CTA
        ================================================== */

        .petition-bottom-cta {

            position:
                relative;

            overflow:
                hidden;

            margin-top:
                28px;

            background:
                linear-gradient(
                    135deg,
                    var(--primary),
                    var(--primary-dark)
                );

            border-radius:
                18px;

            border-bottom:
                4px solid var(--gold);

            padding:
                45px 25px;

            text-align:
                center;

            color:
                #ffffff;

            box-shadow:
                0 18px 45px rgba(15,23,42,0.13);

        }


        .petition-bottom-cta h2 {

            position:
                relative;

            z-index:
                2;

            color:
                #ffffff;

            margin:
                0 0 9px;

            font-size:
                29px;

        }


        .petition-bottom-cta p {

            position:
                relative;

            z-index:
                2;

            color:
                rgba(255,255,255,0.82);

            max-width:
                650px;

            margin:
                0 auto 23px;

            line-height:
                1.7;

            font-size:
                14px;

        }


        .petition-gold-button {

            position:
                relative;

            z-index:
                2;

            display:
                inline-flex;

            align-items:
                center;

            justify-content:
                center;

            gap:
                8px;

            background:
                var(--gold);

            color:
                #111827;

            text-decoration:
                none;

            padding:
                12px 20px;

            border-radius:
                9px;

            font-weight:
                900;

            font-size:
                14px;

            transition:
                0.2s ease;

        }


        .petition-gold-button:hover {

            color:
                #111827;

            transform:
                translateY(-2px);

            opacity:
                0.94;

        }


        /* ==================================================
           FOOTER
        ================================================== */

        .petition-footer {

            background:
                #101827;

            color:
                #ffffff;

            padding:
                45px 25px;

            text-align:
                center;

            border-top:
                4px solid var(--gold);

        }


        .petition-footer h3 {

            color:
                #ffffff;

            margin:
                0 0 8px;

            font-size:
                18px;

        }


        .petition-footer p {

            color:
                #cbd5e1;

            margin:
                6px 0;

            font-size:
                13px;

        }


        .petition-footer .copyright {

            color:
                #94a3b8;

            font-size:
                12px;

            margin-top:
                20px;

        }


        /* ==================================================
           RESPONSIVE
        ================================================== */

        @media (max-width: 950px) {

            .petition-layout {

                grid-template-columns:
                    1fr;

            }


            .petition-action-card {

                position:
                    static;

            }


            .petition-action-card {

                max-width:
                    none;

            }

        }


        @media (max-width: 700px) {

            .petition-navigation {

                min-height:
                    126px;

                padding:
                    10px 15px;

                flex-direction:
                    column;

                justify-content:
                    center;

            }


            .petition-brand {

                position:
                    static;

                transform:
                    none;

                width:
                    100%;

                text-align:
                    center;

                padding-top:
                    3px;

                margin-bottom:
                    3px;

            }


            .petition-tabs {

                position:
                    static;

                transform:
                    none;

                width:
                    100%;

                flex-wrap:
                    wrap;

                gap:
                    8px 15px;

                padding:
                    5px 48px 5px 0;

            }


            .petition-tabs a {

                font-size:
                    13px;

            }


            .petition-flag {

                right:
                    8px;

                width:
                    45px;

                height:
                    45px;

            }


            .petition-hero {

                padding:
                    45px 20px 60px;

            }


            .petition-hero h1 {

                font-size:
                    36px;

                letter-spacing:
                    -0.8px;

            }


            .petition-container {

                padding:
                    28px 16px 65px;

            }


            .petition-content-card,
            .petition-action-card,
            .petition-lower-card {

                padding:
                    22px;

            }


            .petition-info-grid {

                grid-template-columns:
                    1fr;

            }


            .petition-lower-grid {

                grid-template-columns:
                    1fr;

            }

        }


        @media (max-width: 420px) {

            .petition-tabs {

                gap:
                    7px 11px;

            }


            .petition-tabs a {

                font-size:
                    12px;

            }


            .petition-hero h1 {

                font-size:
                    31px;

            }


            .petition-hero-meta {

                flex-direction:
                    column;

                align-items:
                    flex-start;

            }


            .petition-meta-item {

                width:
                    100%;

            }


            .petition-number-row {

                align-items:
                    flex-start;

            }


            .petition-big-number {

                font-size:
                    31px;

            }

        }

    </style>

</head>


<body>


<!-- ======================================================
     NAVIGATION
====================================================== -->

<nav class="petition-navigation">


    <div class="petition-brand">

        <a href="index.php">
            Petition Platform
        </a>

    </div>


    <div class="petition-tabs">


        <a href="index.php">
            Home
        </a>


        <a
            href="petitions.php"
            class="active"
            aria-current="page"
        >
            Petitions
        </a>


        <?php if ($user_logged_in): ?>


            <a href="dashboard.php">
                Dashboard
            </a>


            <a href="logout.php">
                Logout
            </a>


        <?php else: ?>


            <a href="login.php">
                Login
            </a>


            <a
                href="register.php"
                class="petition-register"
            >
                Register
            </a>


        <?php endif; ?>


    </div>


    <!-- TANZANIA FLAG -->

    <div
        id="tanzania-flag"
        class="petition-flag"
        aria-label="Tanzania Flag"
        title="Tanzania"
    ></div>


</nav>


<!-- ======================================================
     HERO
====================================================== -->

<section class="petition-hero">


    <div class="petition-hero-inner">


        <a
            href="petitions.php"
            class="petition-back"
        >

            <i class="fa-solid fa-arrow-left"></i>

            Back to Petitions

        </a>


        <div class="petition-category-badge">

            <i
                class="fa-solid <?= e($category_icon) ?>"
            ></i>

            <?= e($category) ?>

        </div>


        <h1>
            <?= e($petition['title']) ?>
        </h1>


        <div class="petition-hero-meta">


            <?php if ($created_date !== ''): ?>

                <div class="petition-meta-item">

                    <i class="fa-regular fa-calendar"></i>

                    Started <?= e($created_date) ?>

                </div>

            <?php endif; ?>


            <div class="petition-meta-item">

                <i class="fa-solid fa-pen-nib"></i>

                <?= number_format($signatures) ?>

                signatures

            </div>


            <div class="petition-meta-item">

                <i class="fa-solid fa-bullseye"></i>

                Goal:
                <?= number_format($goal) ?>

            </div>


            <div class="petition-meta-item">

                <i class="fa-solid fa-circle-check"></i>

                <?= e($status_label) ?>

            </div>


        </div>


    </div>

</section>


<!-- ======================================================
     MAIN
====================================================== -->

<main class="petition-container">


    <div class="petition-layout">


        <!-- ==================================================
             LEFT CONTENT
        ================================================== -->

        <div>


            <article
                class="petition-glass-card petition-content-card"
            >


                <div
                    class="petition-status <?= $petition_status === 'active'
                        ? 'petition-status-active'
                        : 'petition-status-closed' ?>"
                >

                    <span
                        class="petition-status-dot"
                    ></span>

                    <?= e($status_label) ?>

                </div>


                <div
                    class="petition-content-heading"
                >

                    <h2>
                        About This Petition
                    </h2>

                </div>


                <div
                    class="petition-description-full"
                >

                    <?= e($description) ?>

                </div>


                <div
                    class="petition-info-grid"
                >


                    <div
                        class="petition-info-box"
                    >

                        <i
                            class="fa-solid <?= e($category_icon) ?>"
                        ></i>

                        <span>
                            Category
                        </span>

                        <strong>
                            <?= e($category) ?>
                        </strong>

                    </div>


                    <div
                        class="petition-info-box"
                    >

                        <i
                            class="fa-solid fa-users"
                        ></i>

                        <span>
                            Supporters
                        </span>

                        <strong>
                            <?= number_format($signatures) ?>
                            people
                        </strong>

                    </div>


                    <div
                        class="petition-info-box"
                    >

                        <i
                            class="fa-solid fa-bullseye"
                        ></i>

                        <span>
                            Target
                        </span>

                        <strong>
                            <?= number_format($goal) ?>
                            signatures
                        </strong>

                    </div>


                </div>


            </article>


            <!-- ==================================================
                 LOWER INFORMATION
            ================================================== -->

            <div
                class="petition-lower-grid"
            >


                <section
                    class="petition-glass-card petition-lower-card"
                >

                    <h3>
                        Why Your Signature Matters
                    </h3>


                    <p>

                        Every signature adds public support
                        to this cause and helps demonstrate
                        that people across Tanzania care
                        about this issue.

                    </p>


                    <div
                        class="petition-support-box"
                    >

                        <i
                            class="fa-solid fa-bullhorn"
                        ></i>

                        <div>

                            <strong>
                                Make your voice count
                            </strong>

                            <span>
                                One signature can help amplify
                                an important public issue.
                            </span>

                        </div>

                    </div>


                </section>


                <section
                    class="petition-glass-card petition-lower-card"
                >

                    <h3>
                        Petition Information
                    </h3>


                    <p>

                        This petition is part of the
                        Tanzania Petition Platform, where
                        citizens can raise issues and show
                        public support for important causes.

                    </p>


                    <div
                        class="petition-creator"
                    >

                        <div
                            class="petition-creator-avatar"
                        >

                            <i
                                class="fa-solid fa-user"
                            ></i>

                        </div>


                        <div
                            class="petition-creator-info"
                        >

                            <span>
                                Petition status
                            </span>

                            <strong>
                                <?= e($status_label) ?>
                            </strong>

                        </div>

                    </div>


                </section>


            </div>


        </div>


        <!-- ==================================================
             RIGHT ACTION CARD
        ================================================== -->

        <aside
            class="petition-glass-card petition-action-card"
        >


            <h2
                class="petition-action-title"
            >
                Support This Petition
            </h2>


            <p
                class="petition-action-subtitle"
            >

                Add your voice and help this cause
                reach its goal.

            </p>


            <div
                class="petition-number-row"
            >


                <div>

                    <div
                        class="petition-big-number"
                    >

                        <?= number_format($signatures) ?>

                    </div>


                    <div
                        class="petition-number-label"
                    >
                        signatures
                    </div>

                </div>


                <div
                    class="petition-target"
                >

                    Goal

                    <strong>
                        <?= number_format($goal) ?>
                    </strong>

                </div>


            </div>


            <!-- PROGRESS -->

            <div
                class="petition-progress-bar"
                role="progressbar"
                aria-valuenow="<?= number_format($percentage, 1, '.', '') ?>"
                aria-valuemin="0"
                aria-valuemax="100"
                aria-label="Petition signature progress"
            >

                <div
                    class="petition-progress-fill"
                    style="width: <?= number_format($percentage, 2, '.', '') ?>%;"
                ></div>

            </div>


            <div
                class="petition-progress-stats"
            >

                <span
                    class="petition-progress-left"
                >

                    <strong>
                        <?= number_format($signatures) ?>
                    </strong>

                    of

                    <?= number_format($goal) ?>

                </span>


                <span
                    class="petition-percentage"
                >

                    <?= number_format($percentage, 0) ?>%

                </span>

            </div>


            <!-- SIGN -->

            <?php if ($petition_status === 'active'): ?>


                <?php if ($user_logged_in): ?>


                    <a
                        href="sign.php?id=<?= (int) $petition['id'] ?>"
                        class="petition-sign-button"
                    >

                        <i
                            class="fa-solid fa-signature"
                        ></i>

                        Sign This Petition

                    </a>


                <?php else: ?>


                    <a
                        href="login.php?redirect=petition.php%3Fid%3D<?= (int) $petition['id'] ?>"
                        class="petition-sign-button"
                    >

                        <i
                            class="fa-solid fa-signature"
                        ></i>

                        Sign This Petition

                    </a>


                    <p
                        class="petition-login-note"
                    >

                        You need an account to sign this petition.

                        <a href="register.php">
                            Create one
                        </a>

                        or

                        <a href="login.php">
                            log in
                        </a>.

                    </p>


                <?php endif; ?>


            <?php else: ?>


                <div
                    class="petition-sign-button disabled"
                >

                    <i
                        class="fa-solid fa-lock"
                    ></i>

                    Petition Closed

                </div>


            <?php endif; ?>


            <?php if ($remaining > 0): ?>


                <div
                    class="petition-remaining"
                >

                    <strong>
                        <?= number_format($remaining) ?>
                    </strong>

                    more signatures needed
                    to reach the goal.

                </div>


            <?php elseif ($goal > 0): ?>


                <div
                    class="petition-remaining"
                >

                    <strong>
                        Goal reached!
                    </strong>

                    Thank you to everyone
                    who supported this petition.

                </div>


            <?php endif; ?>


            <div
                class="petition-card-divider"
            ></div>


            <!-- DETAILS -->

            <h3
                class="petition-details-title"
            >
                Petition Details
            </h3>


            <?php if ($created_date !== ''): ?>

                <div
                    class="petition-detail-row"
                >

                    <i
                        class="fa-regular fa-calendar"
                    ></i>

                    <span>

                        Started
                        <strong>
                            <?= e($created_date) ?>
                        </strong>

                    </span>

                </div>

            <?php endif; ?>


            <div
                class="petition-detail-row"
            >

                <i
                    class="fa-solid <?= e($category_icon) ?>"
                ></i>

                <span>

                    Category
                    <strong>
                        <?= e($category) ?>
                    </strong>

                </span>

            </div>


            <div
                class="petition-detail-row"
            >

                <i
                    class="fa-solid fa-users"
                ></i>

                <span>

                    <strong>
                        <?= number_format($signatures) ?>
                    </strong>

                    people have signed

                </span>

            </div>


            <div
                class="petition-detail-row"
            >

                <i
                    class="fa-solid fa-bullseye"
                ></i>

                <span>

                    Target
                    <strong>
                        <?= number_format($goal) ?>
                    </strong>

                    signatures

                </span>

            </div>


            <!-- SHARE -->

            <div
                class="petition-share"
            >

                <div
                    class="petition-share-title"
                >
                    Share this petition
                </div>


                <div
                    class="petition-share-buttons"
                >

                    <a
                        href="https://www.facebook.com/sharer/sharer.php?u=<?= urlencode($share_url) ?>"
                        target="_blank"
                        rel="noopener noreferrer"
                        class="petition-share-button"
                        aria-label="Share on Facebook"
                        title="Share on Facebook"
                    >

                        <i
                            class="fa-brands fa-facebook-f"
                        ></i>

                    </a>


                    <a
                        href="https://twitter.com/intent/tweet?url=<?= urlencode($share_url) ?>&text=<?= urlencode($petition['title']) ?>"
                        target="_blank"
                        rel="noopener noreferrer"
                        class="petition-share-button"
                        aria-label="Share on X"
                        title="Share on X"
                    >

                        <i
                            class="fa-brands fa-x-twitter"
                        ></i>

                    </a>


                    <button
                        type="button"
                        class="petition-share-button"
                        onclick="copyPetitionLink()"
                        aria-label="Copy petition link"
                        title="Copy petition link"
                    >

                        <i
                            class="fa-solid fa-link"
                        ></i>

                    </button>

                </div>


            </div>


        </aside>


    </div>


    <!-- ======================================================
         BOTTOM CTA
    ====================================================== -->

    <section
        class="petition-bottom-cta"
    >


        <h2>
            Your Voice Matters
        </h2>


        <p>

            Every signature helps bring attention
            to important issues affecting communities
            across Tanzania.

        </p>


        <?php if ($petition_status === 'active'): ?>


            <?php if ($user_logged_in): ?>


                <a
                    href="sign.php?id=<?= (int) $petition['id'] ?>"
                    class="petition-gold-button"
                >

                    <i
                        class="fa-solid fa-signature"
                    ></i>

                    Sign This Petition

                </a>


            <?php else: ?>


                <a
                    href="register.php"
                    class="petition-gold-button"
                >

                    <i
                        class="fa-solid fa-user-plus"
                    ></i>

                    Create an Account

                </a>


            <?php endif; ?>


        <?php else: ?>


            <span
                class="petition-gold-button"
            >

                <i
                    class="fa-solid fa-lock"
                ></i>

                Petition Closed

            </span>


        <?php endif; ?>


    </section>


</main>


<!-- ======================================================
     FOOTER
====================================================== -->

<footer class="petition-footer">


    <h3>
        Petition Platform
    </h3>


    <p>

        Empowering Tanzanian communities
        to make their voices heard.

    </p>


    <p class="copyright">

        &copy;

        <?= date('Y') ?>

        Petition Platform.

        All rights reserved.

    </p>


</footer>


<!-- ======================================================
     TANZANIA FLAG LOTTIE
====================================================== -->

<script>

document.addEventListener(
    "DOMContentLoaded",
    function () {


        const flag =
            document.getElementById(
                "tanzania-flag"
            );


        if (
            !flag ||
            typeof lottie === "undefined"
        ) {

            return;

        }


        lottie.loadAnimation({

            container:
                flag,

            renderer:
                "svg",

            loop:
                true,

            autoplay:
                true,

            path:
                "assets/animations/Tanzania%20flag%20Lottie%20JSON%20animation.json"

        });


    }

);


// ======================================================
// COPY PETITION LINK
// ======================================================

function copyPetitionLink()
{

    const url =
        window.location.href;


    if (
        navigator.clipboard &&
        window.isSecureContext
    ) {

        navigator.clipboard
            .writeText(url)
            .then(function () {

                showCopyMessage();

            })
            .catch(function () {

                fallbackCopy(url);

            });

    } else {

        fallbackCopy(url);

    }

}


function fallbackCopy(text)
{

    const textarea =
        document.createElement("textarea");


    textarea.value =
        text;


    textarea.style.position =
        "fixed";

    textarea.style.left =
        "-999999px";


    document.body.appendChild(
        textarea
    );


    textarea.focus();

    textarea.select();


    try {

        document.execCommand(
            "copy"
        );

        showCopyMessage();

    } catch (error) {

        alert(
            "Unable to copy the petition link."
        );

    }


    document.body.removeChild(
        textarea
    );

}


function showCopyMessage()
{

    const button =
        document.querySelector(
            '[onclick="copyPetitionLink()"]'
        );


    if (!button) {

        return;

    }


    const original =
        button.innerHTML;


    button.innerHTML =
        '<i class="fa-solid fa-check"></i>';


    button.style.color =
        "var(--primary)";


    setTimeout(
        function () {

            button.innerHTML =
                original;

        },
        1800
    );

}

</script>


</body>

</html>