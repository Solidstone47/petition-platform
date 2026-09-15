<?php

require_once "includes/db.php";
require_once "includes/functions.php";
require_once "includes/auth.php";

// ======================================================
// LOGIN STATE
// ======================================================

$user_logged_in = is_user_logged_in();

// ======================================================
// AUTOMATIC CATEGORY DETECTION
// ======================================================

function detect_petition_category($title, $description, $database_category = '')
{
    $title = trim((string) $title);
    $description = trim((string) $description);
    $database_category = trim((string) $database_category);

    $text = strtolower(
        $title . ' ' . $description
    );

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
            'water pollution',
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
            'maternal',
            'healthcare system'
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
            'mp ',
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
                strpos(
                    $text,
                    strtolower($keyword)
                ) !== false
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

    return $icons[$category] ?? 'fa-bullhorn';
}

// ======================================================
// GET SEARCH
// ======================================================

$search =
    isset($_GET['search'])
        ? trim($_GET['search'])
        : '';

// ======================================================
// GET ACTIVE PETITIONS
// ======================================================

$petitions = [];

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

    WHERE p.status = 'active'
";

$params = [];
$types = '';

// ======================================================
// SEARCH FILTER
// ======================================================

if ($search !== '') {

    $sql .= "
        AND (
            p.title LIKE ?
            OR p.description LIKE ?
            OR p.category LIKE ?
        )
    ";

    $search_value =
        '%' . $search . '%';

    $params[] = $search_value;
    $params[] = $search_value;
    $params[] = $search_value;

    $types .= 'sss';
}

$sql .= "
    ORDER BY p.created_at DESC
";

$stmt =
    $conn->prepare($sql);

if ($stmt) {

    if (!empty($params)) {

        $stmt->bind_param(
            $types,
            ...$params
        );

    }

    $stmt->execute();

    $result =
        $stmt->get_result();

    while (
        $row =
            $result->fetch_assoc()
    ) {

        $row['display_category'] =
            detect_petition_category(
                $row['title'],
                $row['description'],
                $row['category']
            );

        $row['category_icon'] =
            petition_category_icon(
                $row['display_category']
            );

        $petitions[] = $row;
    }

    $stmt->close();
}

// ======================================================
// TOTAL ACTIVE PETITIONS
// ======================================================

$total_active_petitions =
    count($petitions);

// ======================================================
// TOTAL SIGNATURES
// ======================================================

$total_signatures = 0;

$result =
    $conn->query(
        "SELECT COUNT(*) AS total
         FROM signatures s
         INNER JOIN petitions p
             ON p.id = s.petition_id
         WHERE p.status = 'active'"
    );

if ($result) {

    $row =
        $result->fetch_assoc();

    $total_signatures =
        (int) ($row['total'] ?? 0);
}

// ======================================================
// TOTAL GOALS
// ======================================================

$total_goal = 0;

foreach ($petitions as $petition) {

    $total_goal +=
        max(
            0,
            (int) $petition['goal']
        );
}

// ======================================================
// CATEGORY SUMMARY
// ======================================================

$category_counts = [];

foreach ($petitions as $petition) {

    $category =
        $petition['display_category'];

    if (!isset($category_counts[$category])) {
        $category_counts[$category] = 0;
    }

    $category_counts[$category]++;
}

arsort($category_counts);

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
    content="Browse active petitions and causes from communities across Tanzania."
>

<title>
    Petitions | Petition Platform
</title>

<!-- ======================================================
     MAIN CSS
====================================================== -->

<link
    rel="stylesheet"
    href="assets/css/style.css"
>

<!-- ======================================================
     FONT AWESOME
====================================================== -->

<link
    rel="stylesheet"
    href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css"
>

<!-- ======================================================
     LOTTIE
====================================================== -->

<script
    src="https://cdnjs.cloudflare.com/ajax/libs/bodymovin/5.12.2/lottie.min.js">
</script>

<style>

/* ======================================================
   PAGE
====================================================== */

body {

    background:

        linear-gradient(
            180deg,
            #f4f7fb 0%,
            #eef3f8 45%,
            #f8fafc 100%
        );

}

/* ======================================================
   HOMEPAGE-STYLE NAVIGATION
====================================================== */

.homepage-navigation {

    position: relative;

    width: 100%;

    min-height: 72px;

    background: #ffffff;

    border-bottom:
        1px solid var(--border);

    display: flex;

    align-items: center;

    padding:
        0 20px;

    box-sizing:
        border-box;

}

.homepage-brand {

    position: absolute;

    left: 25px;

    top: 50%;

    transform:
        translateY(-50%);

    z-index: 5;

}

.homepage-brand a {

    color:
        var(--primary);

    text-decoration:
        none;

    font-size:
        20px;

    font-weight:
        800;

    white-space:
        nowrap;

}

.homepage-brand a:hover {

    color:
        var(--primary-dark);

}

.homepage-tabs {

    position: absolute;

    left: 50%;

    top: 50%;

    transform:
        translate(-50%, -50%);

    display: flex;

    align-items: center;

    justify-content: center;

    gap: 26px;

    white-space: nowrap;

}

.homepage-tabs a {

    display: inline-flex;

    align-items: center;

    justify-content: center;

    color:
        var(--text);

    text-decoration:
        none;

    font-size:
        15px;

    font-weight:
        600;

    padding:
        8px 2px;

    transition:
        color 0.2s ease;

}

.homepage-tabs a:hover {

    color:
        var(--primary);

}

.homepage-tabs a[aria-current="page"] {

    color:
        var(--primary);

    font-weight:
        700;

}

.homepage-tabs .homepage-register {

    background:
        var(--gold);

    color:
        #111827;

    padding:
        9px 16px;

    border-radius:
        7px;

    font-weight:
        700;

}

.homepage-tabs .homepage-register:hover {

    color:
        #111827;

    opacity:
        0.9;

}

/* ======================================================
   SUPPORT TAB
====================================================== */

.homepage-tabs .homepage-support {

    position:
        relative;

    display:
        inline-flex;

    align-items:
        center;

    gap:
        6px;

    padding:
        9px 13px;

    border-radius:
        999px;

    background:
        rgba(0,106,78,0.055);

    border:
        1px solid rgba(0,106,78,0.12);

    color:
        var(--primary);

    backdrop-filter:
        blur(8px);

    -webkit-backdrop-filter:
        blur(8px);

    box-shadow:
        0 4px 15px rgba(
            15,
            23,
            42,
            0.045
        );

    transition:
        all 0.22s ease;

}

.homepage-tabs .homepage-support i {

    font-size:
        12px;

}

.homepage-tabs .homepage-support:hover {

    background:
        rgba(0,106,78,0.10);

    color:
        var(--primary-dark);

    transform:
        translateY(-1px);

    box-shadow:
        0 7px 20px rgba(
            15,
            23,
            42,
            0.09
        );

}

.homepage-flag {

    position: absolute;

    right: 18px;

    top: 50%;

    transform:
        translateY(-50%);

    width: 62px;

    height: 62px;

    display: flex;

    align-items: center;

    justify-content: center;

    overflow: hidden;

    z-index: 10;

}

.homepage-flag svg {

    width: 100%;

    height: 100%;

    display: block;

}

/* ======================================================
   HERO
====================================================== */

.petitions-hero {

    position:
        relative;

    overflow:
        hidden;

    background:

        linear-gradient(
            135deg,
            var(--primary-dark),
            var(--primary)
        );

    color:
        #ffffff;

    padding:
        78px 25px 105px;

    border-bottom:
        4px solid var(--gold);

}

.petitions-hero::before {

    content:
        "";

    position:
        absolute;

    width:
        280px;

    height:
        280px;

    border-radius:
        50%;

    background:
        rgba(
            255,
            255,
            255,
            0.05
        );

    top:
        -130px;

    right:
        8%;

}

.petitions-hero::after {

    content:
        "";

    position:
        absolute;

    width:
        180px;

    height:
        180px;

    border-radius:
        50%;

    background:
        rgba(
            255,
            255,
            255,
            0.04
        );

    bottom:
        -100px;

    left:
        8%;

}

.petitions-hero-inner {

    position:
        relative;

    z-index:
        2;

    max-width:
        850px;

    margin:
        0 auto;

    text-align:
        center;

}

.petitions-hero-badge {

    display:
        inline-flex;

    align-items:
        center;

    gap:
        8px;

    padding:
        7px 13px;

    border:
        1px solid rgba(
            255,
            255,
            255,
            0.18
        );

    background:
        rgba(
            255,
            255,
            255,
            0.08
        );

    backdrop-filter:
        blur(10px);

    -webkit-backdrop-filter:
        blur(10px);

    border-radius:
        999px;

    font-size:
        12px;

    font-weight:
        700;

    margin-bottom:
        18px;

}

.petitions-hero-badge i {

    color:
        var(--gold);

}

.petitions-hero h1 {

    color:
        #ffffff;

    font-size:
        clamp(
            40px,
            5vw,
            60px
        );

    line-height:
        1.08;

    margin:
        0 0 17px;

    letter-spacing:
        -1px;

}

.petitions-hero p {

    max-width:
        720px;

    margin:
        0 auto;

    color:
        rgba(
            255,
            255,
            255,
            0.88
        );

    font-size:
        18px;

    line-height:
        1.7;

}

/* ======================================================
   MAIN CONTAINER
====================================================== */

.petitions-container {

    max-width:
        1140px;

    margin:
        0 auto;

    padding:
        0 25px 85px;

}

/* ======================================================
   MODERN FLOATING SEARCH
====================================================== */

.petitions-search {
    max-width: 860px;
    margin: -42px auto 32px;
    position: relative;
    z-index: 20;
}

.petitions-search-form {
    position: relative;
    display: flex;
    align-items: center;
    gap: 10px;
    width: 100%;
    min-height: 68px;
    padding: 7px;
    background: rgba(255, 255, 255, 0.88);
    border: 1px solid rgba(255, 255, 255, 0.95);
    border-radius: 20px;
    box-shadow:
        0 20px 50px rgba(15, 23, 42, 0.13),
        0 4px 14px rgba(15, 23, 42, 0.05);
    backdrop-filter: blur(20px);
    -webkit-backdrop-filter: blur(20px);
    transition:
        border-color 0.25s ease,
        box-shadow 0.25s ease,
        transform 0.25s ease;
}

.petitions-search-form:focus-within {
    border-color: rgba(0, 106, 78, 0.28);
    box-shadow:
        0 22px 55px rgba(15, 23, 42, 0.15),
        0 0 0 4px rgba(0, 106, 78, 0.06);
    transform: translateY(-1px);
}

/* SEARCH ICON */

.petitions-search-icon {
    width: 48px;
    min-width: 48px;
    height: 48px;
    display: flex;
    align-items: center;
    justify-content: center;
    margin-left: 2px;
    border-radius: 14px;
    background: var(--primary-light);
    color: var(--primary);
    font-size: 16px;
    transition:
        background 0.25s ease,
        color 0.25s ease,
        transform 0.25s ease;
}

.petitions-search-form:focus-within
.petitions-search-icon {
    background: rgba(0, 106, 78, 0.12);
    color: var(--primary-dark);
    transform: scale(1.03);
}

/* INPUT */

.petitions-search-form input {
    flex: 1;
    min-width: 0;
    height: 52px;
    border: none;
    outline: none;
    padding: 0 8px;
    background: transparent;
    color: var(--text);
    font-family: inherit;
    font-size: 15px;
    font-weight: 500;
}

.petitions-search-form input::placeholder {
    color: #94a3b8;
    font-weight: 400;
}

.petitions-search-form input::-webkit-search-cancel-button {
    appearance: none;
    width: 18px;
    height: 18px;
    margin-right: 4px;
    cursor: pointer;
    background:
        linear-gradient(
            45deg,
            transparent 42%,
            #94a3b8 42%,
            #94a3b8 58%,
            transparent 58%
        ),
        linear-gradient(
            -45deg,
            transparent 42%,
            #94a3b8 42%,
            #94a3b8 58%,
            transparent 58%
        );
}

/* SEARCH BUTTON */

.petitions-search-form button {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    gap: 8px;
    height: 52px;
    padding: 0 23px;
    border: none;
    border-radius: 14px;
    background: var(--primary);
    color: #ffffff;
    font-family: inherit;
    font-size: 14px;
    font-weight: 700;
    letter-spacing: 0.1px;
    cursor: pointer;
    box-shadow:
        0 7px 18px rgba(0, 106, 78, 0.20);
    transition:
        background 0.2s ease,
        transform 0.2s ease,
        box-shadow 0.2s ease;
}

.petitions-search-form button:hover {
    background: var(--primary-dark);
    transform: translateY(-1px);
    box-shadow:
        0 10px 23px rgba(0, 106, 78, 0.26);
}

.petitions-search-form button:active {
    transform: translateY(0);
    box-shadow:
        0 5px 13px rgba(0, 106, 78, 0.18);
}

.petitions-search-form button i {
    font-size: 13px;
}

/* ======================================================
   SEARCH RESPONSIVE
====================================================== */

@media (max-width: 700px) {

    .petitions-search {
        margin: -34px auto 28px;
    }

    .petitions-search-form {
        min-height: 62px;
        padding: 6px;
        border-radius: 17px;
        gap: 7px;
    }

    .petitions-search-icon {
        width: 44px;
        min-width: 44px;
        height: 44px;
        border-radius: 12px;
    }

    .petitions-search-form input {
        height: 46px;
        padding: 0 5px;
        font-size: 14px;
    }

    .petitions-search-form button {
        height: 46px;
        padding: 0 17px;
        border-radius: 12px;
    }
}

@media (max-width: 480px) {

    .petitions-search {
        margin: -30px auto 25px;
    }

    .petitions-search-form {
        padding: 6px;
        border-radius: 16px;
    }

    .petitions-search-icon {
        width: 40px;
        min-width: 40px;
        height: 40px;
        border-radius: 11px;
        font-size: 14px;
    }

    .petitions-search-form input {
        height: 42px;
        font-size: 13px;
    }

    .petitions-search-form input::placeholder {
        font-size: 12px;
    }

    .petitions-search-form button {
        width: 43px;
        min-width: 43px;
        height: 42px;
        padding: 0;
        border-radius: 11px;
    }

    .petitions-search-form button span {
        display: none;
    }

    .petitions-search-form button i {
        margin: 0;
        font-size: 14px;
    }
}

/* ======================================================
   OVERVIEW
====================================================== */

.petitions-overview {

    display:
        grid;

    grid-template-columns:
        repeat(3, 1fr);

    gap:
        14px;

    margin-bottom:
        45px;

}

.petitions-overview-card {

    position:
        relative;

    overflow:
        hidden;

    background:
        rgba(
            255,
            255,
            255,
            0.66
        );

    border:
        1px solid rgba(
            255,
            255,
            255,
            0.9
        );

    border-radius:
        16px;

    padding:
        18px;

    display:
        flex;

    align-items:
        center;

    gap:
        14px;

    box-shadow:
        0 8px 25px rgba(
            15,
            23,
            42,
            0.05
        );

    backdrop-filter:
        blur(12px);

    -webkit-backdrop-filter:
        blur(12px);

}

.petitions-overview-icon {

    width:
        42px;

    height:
        42px;

    min-width:
        42px;

    display:
        flex;

    align-items:
        center;

    justify-content:
        center;

    border-radius:
        13px;

    background:
        var(--primary-light);

    color:
        var(--primary);

}

.petitions-overview-card:nth-child(2)
.petitions-overview-icon {

    background:
        var(--gold-light);

    color:
        var(--gold-dark);

}

.petitions-overview-card:nth-child(3)
.petitions-overview-icon {

    background:
        var(--blue-light);

    color:
        var(--blue-dark);

}

.petitions-overview-number {

    font-size:
        20px;

    font-weight:
        800;

    color:
        var(--text);

    line-height:
        1.1;

}

.petitions-overview-label {

    color:
        var(--text-muted);

    font-size:
        12px;

    margin-top:
        3px;

}

/* ======================================================
   CATEGORY BAR
====================================================== */

.petition-category-section {

    margin-bottom:
        38px;

}

.petition-category-heading {

    display:
        flex;

    align-items:
        center;

    justify-content:
        space-between;

    gap:
        15px;

    margin-bottom:
        14px;

}

.petition-category-heading h2 {

    font-size:
        18px;

    margin:
        0;

}

.petition-category-heading span {

    font-size:
        12px;

    color:
        var(--text-muted);

}

.petition-category-list {

    display:
        flex;

    gap:
        9px;

    overflow-x:
        auto;

    padding:
        3px 2px 8px;

    scrollbar-width:
        thin;

}

.petition-category-chip {

    display:
        inline-flex;

    align-items:
        center;

    gap:
        7px;

    white-space:
        nowrap;

    padding:
        9px 13px;

    border-radius:
        999px;

    background:
        rgba(
            255,
            255,
            255,
            0.72
        );

    border:
        1px solid #e2e8f0;

    color:
        var(--text);

    font-size:
        12px;

    font-weight:
        700;

    box-shadow:
        0 4px 12px rgba(
            15,
            23,
            42,
            0.04
        );

}

.petition-category-chip i {

    color:
        var(--primary);

}

/* ======================================================
   LIST HEADER
====================================================== */

.petitions-list-header {

    display:
        flex;

    align-items:
        flex-end;

    justify-content:
        space-between;

    gap:
        20px;

    margin-bottom:
        24px;

}

.petitions-list-header h2 {

    margin:
        0 0 5px;

    font-size:
        29px;

    letter-spacing:
        -0.5px;

}

.petitions-list-header p {

    margin:
        0;

    color:
        var(--text-muted);

    font-size:
        14px;

}

.petition-count-summary {

    display:
        inline-flex;

    align-items:
        center;

    gap:
        6px;

    color:
        var(--primary);

    font-size:
        13px;

    font-weight:
        700;

    white-space:
        nowrap;

    background:
        var(--primary-light);

    padding:
        9px 13px;

    border-radius:
        999px;

}

/* ======================================================
   PETITION GRID
====================================================== */

.petitions-grid {

    display:
        grid;

    grid-template-columns:
        repeat(2, minmax(0, 1fr));

    gap:
        20px;

}

/* ======================================================
   PETITION CARD
====================================================== */

.petition-card {

    position:
        relative;

    overflow:
        hidden;

    background:
        rgba(
            255,
            255,
            255,
            0.74
        );

    border:
        1px solid rgba(
            255,
            255,
            255,
            0.92
        );

    border-radius:
        19px;

    padding:
        22px;

    display:
        flex;

    flex-direction:
        column;

    min-width:
        0;

    box-shadow:
        0 10px 30px rgba(
            15,
            23,
            42,
            0.06
        );

    backdrop-filter:
        blur(15px);

    -webkit-backdrop-filter:
        blur(15px);

    transition:
        transform 0.22s ease,
        box-shadow 0.22s ease,
        border-color 0.22s ease;

}

.petition-card::before {

    content:
        "";

    position:
        absolute;

    width:
        100px;

    height:
        100px;

    border-radius:
        50%;

    background:
        rgba(
            255,
            255,
            255,
            0.55
        );

    top:
        -55px;

    right:
        -35px;

    pointer-events:
        none;

}

.petition-card:hover {

    transform:
        translateY(-5px);

    box-shadow:
        0 18px 38px rgba(
            15,
            23,
            42,
            0.11
        );

    border-color:
        rgba(
            203,
            213,
            225,
            0.9
        );

}

/* ======================================================
   CARD TOP
====================================================== */

.petition-card-top {

    position:
        relative;

    z-index:
        2;

    display:
        flex;

    align-items:
        center;

    gap:
        12px;

    margin-bottom:
        17px;

}

.petition-category-icon {

    width:
        43px;

    height:
        43px;

    min-width:
        43px;

    border-radius:
        13px;

    display:
        flex;

    align-items:
        center;

    justify-content:
        center;

    background:
        var(--primary-light);

    color:
        var(--primary);

    font-size:
        17px;

}

.petition-category-info {

    min-width:
        0;

    flex:
        1;

}

.petition-category-label {

    display:
        block;

    color:
        #94a3b8;

    font-size:
        10px;

    font-weight:
        800;

    text-transform:
        uppercase;

    letter-spacing:
        0.8px;

    margin-bottom:
        3px;

}

.petition-category-name {

    color:
        var(--text);

    font-size:
        13px;

    font-weight:
        700;

}

/* ======================================================
   STATUS
====================================================== */

.petition-status {

    display:
        inline-flex;

    align-items:
        center;

    gap:
        5px;

    padding:
        5px 9px;

    border-radius:
        999px;

    background:
        rgba(
            220,
            252,
            231,
            0.9
        );

    color:
        #166534;

    font-size:
        10px;

    font-weight:
        800;

    white-space:
        nowrap;

    border:
        1px solid rgba(
            134,
            239,
            172,
            0.35
        );

}

.petition-status-dot {

    width:
        6px;

    height:
        6px;

    border-radius:
        50%;

    background:
        #16a34a;

    box-shadow:
        0 0 0 3px rgba(
            34,
            197,
            94,
            0.12
        );

}

/* ======================================================
   TITLE
====================================================== */

.petition-card h3 {

    position:
        relative;

    z-index:
        2;

    margin:
        0 0 10px;

    font-size:
        21px;

    line-height:
        1.3;

}

.petition-card h3 a {

    color:
        var(--text);

    text-decoration:
        none;

}

.petition-card h3 a:hover {

    color:
        var(--primary);

}

/* ======================================================
   DESCRIPTION
====================================================== */

.petition-description {

    color:
        var(--text-muted);

    font-size:
        14px;

    line-height:
        1.6;

    margin:
        0 0 20px;

}

/* ======================================================
   PROGRESS AREA
====================================================== */

.petition-progress-area {

    margin-top:
        auto;

}

.petition-progress-header {

    display:
        flex;

    align-items:
        center;

    justify-content:
        space-between;

    margin-bottom:
        8px;

}

.petition-progress-label {

    font-size:
        11px;

    color:
        #94a3b8;

    font-weight:
        600;

}

.petition-progress-percent {

    font-size:
        12px;

    color:
        var(--primary);

    font-weight:
        800;

}

.petition-progress-bar {

    width:
        100%;

    height:
        9px;

    background:
        rgba(
            226,
            232,
            240,
            0.85
        );

    border-radius:
        999px;

    overflow:
        hidden;

    margin-bottom:
        11px;

}

.petition-progress-fill {

    height:
        100%;

    background:
        linear-gradient(
            90deg,
            var(--primary),
            #22c55e
        );

    border-radius:
        999px;

    min-width:
        0;

    position:
        relative;

}

.petition-progress-fill::after {

    content:
        "";

    position:
        absolute;

    top:
        0;

    right:
        0;

    width:
        35px;

    height:
        100%;

    background:
        rgba(
            255,
            255,
            255,
            0.25
        );

    border-radius:
        inherit;

}

/* ======================================================
   PETITION STATS
====================================================== */

.petition-stats {

    display:
        flex;

    align-items:
        center;

    justify-content:
        space-between;

    gap:
        15px;

    font-size:
        12px;

    color:
        var(--text-muted);

}

.petition-stat-left {

    min-width:
        0;

}

.petition-stat-left strong {

    color:
        var(--text);

    font-size:
        14px;

}

.petition-stat-right {

    color:
        var(--primary);

    font-weight:
        800;

    white-space:
        nowrap;

}

/* ======================================================
   CARD FOOTER
====================================================== */

.petition-card-footer {

    display:
        flex;

    align-items:
        center;

    justify-content:
        space-between;

    gap:
        15px;

    margin-top:
        18px;

    padding-top:
        15px;

    border-top:
        1px solid rgba(
            226,
            232,
            240,
            0.75
        );

}

.petition-created {

    color:
        #94a3b8;

    font-size:
        11px;

}

.petition-view {

    display:
        inline-flex;

    align-items:
        center;

    gap:
        7px;

    color:
        var(--primary);

    text-decoration:
        none;

    font-size:
        12px;

    font-weight:
        800;

    padding:
        7px 10px;

    border-radius:
        9px;

    transition:
        background 0.2s ease;

}

.petition-view:hover {

    background:
        var(--primary-light);

}

.petition-view i {

    transition:
        transform 0.2s ease;

}

.petition-view:hover i {

    transform:
        translateX(3px);

}

/* ======================================================
   EMPTY STATE
====================================================== */

.petitions-empty {

    background:
        rgba(
            255,
            255,
            255,
            0.72
        );

    border:
        1px solid rgba(
            255,
            255,
            255,
            0.9
        );

    border-radius:
        20px;

    text-align:
        center;

    padding:
        70px 25px;

    box-shadow:
        0 12px 30px rgba(
            15,
            23,
            42,
            0.06
        );

    backdrop-filter:
        blur(14px);

    -webkit-backdrop-filter:
        blur(14px);

}

.petitions-empty-icon {

    width:
        62px;

    height:
        62px;

    margin:
        0 auto 17px;

    border-radius:
        18px;

    display:
        flex;

    align-items:
        center;

    justify-content:
        center;

    background:
        var(--primary-light);

    color:
        var(--primary);

    font-size:
        22px;

}

.petitions-empty h3 {

    margin:
        0 0 8px;

}

.petitions-empty p {

    color:
        var(--text-muted);

    margin:
        0 0 22px;

}

/* ======================================================
   SUPPORT / CTA
====================================================== */

.petitions-cta {

    position:
        relative;

    overflow:
        hidden;

    margin-top:
        65px;

    padding:
        55px 30px;

    border-radius:
        22px;

    text-align:
        center;

    background:
        linear-gradient(
            135deg,
            var(--primary),
            var(--primary-dark)
        );

    color:
        #ffffff;

    border-bottom:
        4px solid var(--gold);

    box-shadow:
        0 18px 40px rgba(
            15,
            23,
            42,
            0.12
        );

}

.petitions-cta::before {

    content:
        "";

    position:
        absolute;

    width:
        230px;

    height:
        230px;

    border-radius:
        50%;

    background:
        rgba(
            255,
            255,
            255,
            0.05
        );

    right:
        -70px;

    top:
        -120px;

}

.petitions-cta::after {

    content:
        "";

    position:
        absolute;

    width:
        160px;

    height:
        160px;

    border-radius:
        50%;

    background:
        rgba(
            255,
            255,
            255,
            0.04
        );

    left:
        -70px;

    bottom:
        -100px;

}

.petitions-cta h2,
.petitions-cta p,
.petitions-cta a {

    position:
        relative;

    z-index:
        2;

}

.petitions-cta h2 {

    color:
        #ffffff;

    margin:
        0 0 10px;

}

.petitions-cta p {

    max-width:
        650px;

    margin:
        0 auto 24px;

    color:
        rgba(
            255,
            255,
            255,
            0.86
        );

}

/* ======================================================
   FOOTER
====================================================== */

.petitions-footer {

    background:
        #111827;

    color:
        #ffffff;

    padding:
        45px 25px;

    text-align:
        center;

    border-top:
        4px solid var(--gold);

}

.petitions-footer h3 {

    color:
        #ffffff;

    margin-bottom:
        8px;

}

.petitions-footer p {

    color:
        #cbd5e1;

}

.petitions-footer .copyright {

    color:
        #94a3b8;

    font-size:
        14px;

    margin-top:
        20px;

}

/* ======================================================
   RESPONSIVE
====================================================== */

@media (max-width: 900px) {

    .petitions-grid {

        grid-template-columns:
            1fr;

    }

    .homepage-tabs {

        gap:
            15px;

    }

}

@media (max-width: 700px) {

    .homepage-navigation {

        min-height:
            125px;

        padding:
            10px 15px;

        flex-direction:
            column;

        justify-content:
            center;

    }

    .homepage-brand {

        position:
            static;

        transform:
            none;

        width:
            100%;

        text-align:
            center;

        padding-top:
            5px;

    }

    .homepage-tabs {

        position:
            static;

        transform:
            none;

        width:
            100%;

        flex-wrap:
            wrap;

        gap:
            10px;

        padding:
            5px 45px 5px 0;

        box-sizing:
            border-box;

    }

    .homepage-flag {

        right:
            8px;

        width:
            48px;

        height:
            48px;

    }

    .petitions-hero {

        padding:
            58px 20px 85px;

    }

    .petitions-hero h1 {

        font-size:
            40px;

    }

    .petitions-container {

        padding:
            0 16px 60px;

    }

    .petitions-search {

        margin:
            -30px auto 25px;

    }

    .petitions-overview {

        grid-template-columns:
            1fr;

        gap:
            10px;

        margin-bottom:
            35px;

    }

    .petitions-overview-card {

        padding:
            15px;

    }

    .petitions-list-header {

        align-items:
            flex-start;

        flex-direction:
            column;

    }

    .petition-count-summary {

        white-space:
            normal;

    }

    .petition-card {

        padding:
            19px;

    }

}

@media (max-width: 480px) {

    .petitions-search-form {

        padding:
            7px;

    }

    .petitions-search-icon {

        width:
            38px;

        min-width:
            38px;

    }

    .petitions-search-form button {

        padding:
            11px 15px;

    }

    .petitions-search-form button i {

        margin-right:
            0;

    }

    .petitions-search-form button {

        font-size:
            0;

    }

    .petitions-search-form button i {

        font-size:
            15px;

    }

    .petitions-hero h1 {

        font-size:
            36px;

    }

    .petitions-hero p {

        font-size:
            16px;

    }

    .petition-card-top {

        align-items:
            flex-start;

    }

    .petition-category-icon {

        width:
            40px;

        height:
            40px;

        min-width:
            40px;

    }

    .petition-card h3 {

        font-size:
            19px;

    }

    .petition-card-footer {

        align-items:
            flex-start;

        flex-direction:
            column;

        gap:
            8px;

    }

    .petition-view {

        padding-left:
            0;

    }

    .petitions-cta {

        padding:
            42px 22px;

    }

    .homepage-tabs a {

        font-size:
            14px;

    }

}

</style>

</head>

<body>

<!-- ======================================================
     NAVIGATION
====================================================== -->

<nav class="homepage-navigation">

    <div class="homepage-brand">

        <a href="index.php">
            Petition Platform
        </a>

    </div>

    <div class="homepage-tabs">

        <a
            href="index.php"
        >
            Home
        </a>

        <a
            href="./petitions.php"
            aria-current="page"
        >
            Petitions
        </a>

       
        <a
            href="#support"
            class="homepage-support"
        >
            <i class="fa-solid fa-circle-info"></i>
            Support
        </a>


        <a
            href="contact.php"
        >
            Contact
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
                class="homepage-register"
            >
                Register
            </a>

        <?php endif; ?>

    </div>

    <div
        id="tanzania-flag"
        class="homepage-flag"
        aria-label="Tanzania Flag"
        title="Tanzania"
    ></div>

</nav>

<!-- ======================================================
     HERO
====================================================== -->

<section class="petitions-hero">

    <div class="petitions-hero-inner">

        <div class="petitions-hero-badge">

            <i class="fa-solid fa-bullhorn"></i>

            <span>
                COMMUNITY VOICES
            </span>

        </div>

        <h1>
            Petitions That Matter
        </h1>

        <p>
            Discover causes, support your community,
            and add your voice to petitions that can
            help create meaningful change across Tanzania.
        </p>

    </div>

</section>

<!-- ======================================================
     MAIN CONTENT
====================================================== -->

<main class="petitions-container">

    <!-- ==================================================
         SEARCH
    ================================================== -->

    <div class="petitions-search">

        <form
            action="petitions.php"
            method="GET"
            class="petitions-search-form"
        >

            <div class="petitions-search-icon">

                <i class="fa-solid fa-magnifying-glass"></i>

            </div>

            <input
                type="search"
                name="search"
                value="<?= e($search) ?>"
                placeholder="Search petitions, causes or categories..."
                aria-label="Search petitions"
            >

            <button type="submit">

                <i class="fa-solid fa-magnifying-glass"></i>

                <span>
                    Search
                </span>

            </button>

        </form>

    </div>

    <!-- ==================================================
         OVERVIEW
    ================================================== -->

    <div class="petitions-overview">

        <div class="petitions-overview-card">

            <div class="petitions-overview-icon">

                <i class="fa-solid fa-file-signature"></i>

            </div>

            <div>

                <div class="petitions-overview-number">

                    <?= number_format(
                        $total_active_petitions
                    ) ?>

                </div>

                <div class="petitions-overview-label">

                    Active Petitions

                </div>

            </div>

        </div>

        <div class="petitions-overview-card">

            <div class="petitions-overview-icon">

                <i class="fa-solid fa-users"></i>

            </div>

            <div>

                <div class="petitions-overview-number">

                    <?= number_format(
                        $total_signatures
                    ) ?>

                </div>

                <div class="petitions-overview-label">

                    Community Signatures

                </div>

            </div>

        </div>

        <div class="petitions-overview-card">

            <div class="petitions-overview-icon">

                <i class="fa-solid fa-bullseye"></i>

            </div>

            <div>

                <div class="petitions-overview-number">

                    <?= number_format(
                        $total_goal
                    ) ?>

                </div>

                <div class="petitions-overview-label">

                    Combined Signature Goals

                </div>

            </div>

        </div>

    </div>

    <!-- ==================================================
         CATEGORY BAR
    ================================================== -->

    <?php if (!empty($category_counts)): ?>

        <section
            class="petition-category-section"
        >

            <div
                class="petition-category-heading"
            >

                <h2>
                    Explore by Category
                </h2>

                <span>

                    <?= number_format(
                        count($category_counts)
                    ) ?>

                    categories

                </span>

            </div>

            <div
                class="petition-category-list"
            >

                <?php foreach (
                    $category_counts
                    as $category => $count
                ): ?>

                    <div
                        class="petition-category-chip"
                    >

                        <i
                            class="fa-solid <?= e(
                                petition_category_icon($category)
                            ) ?>"
                        ></i>

                        <?= e($category) ?>

                        <span>
                            <?= number_format($count) ?>
                        </span>

                    </div>

                <?php endforeach; ?>

            </div>

        </section>

    <?php endif; ?>

    <!-- ==================================================
         LIST HEADER
    ================================================== -->

    <div class="petitions-list-header">

        <div>

            <h2>
                Active Petitions
            </h2>

            <p>
                Browse causes and see how close
                each petition is to its goal.
            </p>

        </div>

        <div class="petition-count-summary">

            <i class="fa-solid fa-layer-group"></i>

            <?= number_format(
                $total_active_petitions
            ) ?>

            active

            &nbsp;•&nbsp;

            <?= number_format(
                $total_signatures
            ) ?>

            signatures

        </div>

    </div>

    <!-- ==================================================
         PETITIONS
    ================================================== -->

    <?php if (empty($petitions)): ?>

        <div class="petitions-empty">

            <div class="petitions-empty-icon">

                <i class="fa-solid fa-magnifying-glass"></i>

            </div>

            <?php if ($search !== ''): ?>

                <h3>
                    No petitions found
                </h3>

                <p>

                    We couldn't find an active petition
                    matching

                    <strong>
                        <?= e($search) ?>
                    </strong>.

                </p>

                <a
                    href="petitions.php"
                    class="btn"
                >

                    <i class="fa-solid fa-arrow-rotate-left"></i>

                    View All Petitions

                </a>

            <?php else: ?>

                <h3>
                    No Active Petitions
                </h3>

                <p>
                    There are currently no active petitions.
                    Be the first to start one.
                </p>

                <?php if ($user_logged_in): ?>

                    <a
                        href="start_petition.php"
                        class="btn"
                    >

                        <i class="fa-solid fa-plus"></i>

                        Start a Petition

                    </a>

                <?php else: ?>

                    <a
                        href="register.php"
                        class="btn"
                    >

                        <i class="fa-solid fa-user-plus"></i>

                        Create an Account

                    </a>

                <?php endif; ?>

            <?php endif; ?>

        </div>

    <?php else: ?>

        <div class="petitions-grid">

            <?php foreach (
                $petitions
                as $petition
            ): ?>

                <?php

                $signatures =
                    (int)
                    $petition['signature_count'];

                $goal =
                    (int)
                    $petition['goal'];

                $percentage = 0;

                if ($goal > 0) {

                    $percentage =
                        (
                            $signatures /
                            $goal
                        ) * 100;

                    $percentage =
                        min(
                            100,
                            $percentage
                        );

                }

                $description =
                    trim(
                        $petition['description'] ?? ''
                    );

                if ($description === '') {

                    $description =
                        'Support this petition and help bring attention to this important cause.';

                }

                if (
                    strlen($description)
                    > 155
                ) {

                    $description =
                        substr(
                            $description,
                            0,
                            155
                        ) . '...';

                }

                $category =
                    $petition[
                        'display_category'
                    ];

                $category_icon =
                    $petition[
                        'category_icon'
                    ];

                $created_date =
                    !empty(
                        $petition['created_at']
                    )
                        ? date(
                            'M j, Y',
                            strtotime(
                                $petition['created_at']
                            )
                        )
                        : '';

                ?>

                <!-- ======================================
                     PETITION CARD
                ======================================= -->

                <article
                    class="petition-card"
                >

                    <!-- ==================================
                         CARD TOP
                    ================================== -->

                    <div
                        class="petition-card-top"
                    >

                        <div
                            class="petition-category-icon"
                            aria-hidden="true"
                        >

                            <i
                                class="fa-solid <?= e(
                                    $category_icon
                                ) ?>"
                            ></i>

                        </div>

                        <div
                            class="petition-category-info"
                        >

                            <span
                                class="petition-category-label"
                            >
                                Category
                            </span>

                            <span
                                class="petition-category-name"
                            >

                                <?= e(
                                    $category
                                ) ?>

                            </span>

                        </div>

                        <span
                            class="petition-status"
                        >

                            <span
                                class="petition-status-dot"
                            ></span>

                            Active

                        </span>

                    </div>

                    <!-- ==================================
                         TITLE
                    ================================== -->

                    <h3>

                        <a
                            href="petition.php?id=<?= (int) $petition['id'] ?>"
                        >

                            <?= e(
                                $petition['title']
                            ) ?>

                        </a>

                    </h3>

                    <!-- ==================================
                         DESCRIPTION
                    ================================== -->

                    <p
                        class="petition-description"
                    >

                        <?= e(
                            $description
                        ) ?>

                    </p>

                    <!-- ==================================
                         PROGRESS
                    ================================== -->

                    <div
                        class="petition-progress-area"
                    >

                        <div
                            class="petition-progress-header"
                        >

                            <span
                                class="petition-progress-label"
                            >

                                Signature progress

                            </span>

                            <span
                                class="petition-progress-percent"
                            >

                                <?= number_format(
                                    $percentage,
                                    0
                                ) ?>%

                            </span>

                        </div>

                        <div
                            class="petition-progress-bar"
                            role="progressbar"
                            aria-valuenow="<?= number_format($percentage, 1, '.', '') ?>"
                            aria-valuemin="0"
                            aria-valuemax="100"
                        >

                            <div
                                class="petition-progress-fill"
                                style="width: <?= number_format($percentage, 2, '.', '') ?>%;"
                            ></div>

                        </div>

                        <div
                            class="petition-stats"
                        >

                            <span
                                class="petition-stat-left"
                            >

                                <strong>

                                    <?= number_format(
                                        $signatures
                                    ) ?>

                                </strong>

                                signatures

                                <?php if ($goal > 0): ?>

                                    of

                                    <strong>

                                        <?= number_format(
                                            $goal
                                        ) ?>

                                    </strong>

                                <?php endif; ?>

                            </span>

                            <span
                                class="petition-stat-right"
                            >

                                <?php if ($goal > 0): ?>

                                    <?= number_format(
                                        max(
                                            0,
                                            $goal - $signatures
                                        )
                                    ) ?>

                                    to go

                                <?php else: ?>

                                    No fixed goal

                                <?php endif; ?>

                            </span>

                        </div>

                    </div>

                    <!-- ==================================
                         CARD FOOTER
                    ================================== -->

                    <div
                        class="petition-card-footer"
                    >

                        <span
                            class="petition-created"
                        >

                            <?php if (
                                $created_date !== ''
                            ): ?>

                                <i
                                    class="fa-regular fa-calendar"
                                ></i>

                                Started

                                <?= e(
                                    $created_date
                                ) ?>

                            <?php endif; ?>

                        </span>

                        <a
                            href="petition.php?id=<?= (int) $petition['id'] ?>"
                            class="petition-view"
                        >

                            View Petition

                            <i
                                class="fa-solid fa-arrow-right"
                            ></i>

                        </a>

                    </div>

                </article>

            <?php endforeach; ?>

        </div>

    <?php endif; ?>

    <!-- ==================================================
         SUPPORT SECTION
    ================================================== -->

    <section
        id="support"
        class="petitions-cta"
    >

        <h2>
            Have a Cause You Care About?
        </h2>

        <p>
            Start a petition, explain the issue,
            and give your community an opportunity
            to stand with you.
        </p>

        <?php if ($user_logged_in): ?>

            <a
                href="start_petition.php"
                class="btn btn-gold"
            >

                <i class="fa-solid fa-pen-to-square"></i>

                Start Your Petition

            </a>

        <?php else: ?>

            <a
                href="register.php"
                class="btn btn-gold"
            >

                <i class="fa-solid fa-user-plus"></i>

                Start a Petition

            </a>

        <?php endif; ?>

    </section>

</main>

<!-- ======================================================
     FOOTER
====================================================== -->

<footer class="petitions-footer">

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

        if (!flag) {

            console.error(
                "Tanzania flag container not found."
            );

            return;

        }

        if (
            typeof lottie === "undefined"
        ) {

            console.error(
                "Lottie library failed to load."
            );

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

</script>

</body>

</html>