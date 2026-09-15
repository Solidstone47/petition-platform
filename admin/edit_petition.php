<?php

error_reporting(E_ALL);
ini_set('display_errors', '1');
ini_set('display_startup_errors', '1');

require_once "../includes/db.php";
require_once "../includes/functions.php";
require_once "../includes/auth.php";

require_admin();


/*
|--------------------------------------------------------------------------
| PETITION CATEGORIES
|--------------------------------------------------------------------------
*/

$petition_categories = [

    "Education",

    "Health",

    "Environment",

    "Infrastructure & Transport",

    "Governance & Accountability",

    "Justice & Security",

    "Employment & Labour",

    "Agriculture",

    "Business & Economy",

    "Water & Utilities",

    "Social Affairs",

    "Other"

];


/*
|--------------------------------------------------------------------------
| AUTOMATIC CATEGORY DETECTION
|--------------------------------------------------------------------------
*/

function detect_petition_category($title, $description)
{
    $text = strtolower(
        trim($title . ' ' . $description)
    );

    $categories = [

        "Education" => [
            "education",
            "school",
            "schools",
            "student",
            "students",
            "teacher",
            "teachers",
            "university",
            "universities",
            "college",
            "colleges",
            "classroom",
            "classrooms",
            "learning",
            "exam",
            "exams",
            "curriculum",
            "textbook",
            "textbooks",
            "scholarship",
            "scholarships",
            "tuition",
            "academic"
        ],

        "Health" => [
            "health",
            "hospital",
            "hospitals",
            "clinic",
            "clinics",
            "doctor",
            "doctors",
            "nurse",
            "nurses",
            "medicine",
            "medicines",
            "medical",
            "patient",
            "patients",
            "healthcare",
            "health care",
            "disease",
            "diseases",
            "maternal",
            "maternity",
            "ambulance",
            "pharmacy",
            "pharmacies"
        ],

        "Environment" => [
            "environment",
            "environmental",
            "pollution",
            "climate",
            "climate change",
            "forest",
            "forests",
            "deforestation",
            "wildlife",
            "conservation",
            "nature",
            "ocean",
            "oceans",
            "river",
            "rivers",
            "lake",
            "lakes",
            "plastic",
            "waste",
            "recycling",
            "carbon",
            "emissions"
        ],

        "Infrastructure & Transport" => [
            "road",
            "roads",
            "highway",
            "highways",
            "bridge",
            "bridges",
            "railway",
            "railways",
            "train",
            "trains",
            "transport",
            "transportation",
            "bus",
            "buses",
            "traffic",
            "airport",
            "airports",
            "port",
            "ports",
            "infrastructure",
            "construction",
            "street",
            "streets",
            "pothole",
            "potholes"
        ],

        "Governance & Accountability" => [
            "government",
            "governance",
            "accountability",
            "corruption",
            "transparency",
            "public officials",
            "officials",
            "ministry",
            "ministries",
            "parliament",
            "parliamentary",
            "council",
            "councils",
            "public funds",
            "taxpayer",
            "taxpayers",
            "election",
            "elections",
            "leadership"
        ],

        "Justice & Security" => [
            "justice",
            "police",
            "crime",
            "criminal",
            "security",
            "court",
            "courts",
            "law",
            "laws",
            "legal",
            "prison",
            "prisons",
            "rights",
            "human rights",
            "violence",
            "abuse",
            "safety",
            "victim",
            "victims"
        ],

        "Employment & Labour" => [
            "employment",
            "employ",
            "employee",
            "employees",
            "employer",
            "employers",
            "job",
            "jobs",
            "work",
            "worker",
            "workers",
            "labour",
            "labor",
            "salary",
            "salaries",
            "wages",
            "workplace",
            "unemployment",
            "pension",
            "pensions"
        ],

        "Agriculture" => [
            "agriculture",
            "farmer",
            "farmers",
            "farming",
            "farm",
            "farms",
            "crop",
            "crops",
            "livestock",
            "cattle",
            "goats",
            "chicken",
            "poultry",
            "fishing",
            "fisheries",
            "irrigation",
            "fertilizer",
            "fertilizers",
            "seeds",
            "harvest"
        ],

        "Business & Economy" => [
            "business",
            "businesses",
            "economy",
            "economic",
            "trade",
            "trading",
            "company",
            "companies",
            "entrepreneur",
            "entrepreneurs",
            "investment",
            "investments",
            "tax",
            "taxes",
            "taxation",
            "market",
            "markets",
            "finance",
            "financial",
            "bank",
            "banks",
            "small business",
            "startup",
            "startups"
        ],

        "Water & Utilities" => [
            "water",
            "drinking water",
            "electricity",
            "power",
            "energy",
            "utility",
            "utilities",
            "sewage",
            "sewer",
            "sanitation",
            "toilet",
            "toilets",
            "wastewater",
            "water supply",
            "electric"
        ],

        "Social Affairs" => [
            "social",
            "children",
            "child",
            "women",
            "woman",
            "youth",
            "elderly",
            "disabled",
            "disability",
            "disabilities",
            "poverty",
            "homeless",
            "homelessness",
            "welfare",
            "community",
            "communities",
            "families",
            "family",
            "orphan",
            "orphans",
            "inclusion"
        ]

    ];


    $scores = [];


    foreach ($categories as $category => $keywords) {

        $scores[$category] = 0;

        foreach ($keywords as $keyword) {

            $keyword = strtolower($keyword);

            $count = substr_count(
                $text,
                $keyword
            );

            if ($count > 0) {

                $scores[$category] += $count;


                /*
                |--------------------------------------------------------------------------
                | TITLE MATCH GETS EXTRA WEIGHT
                |--------------------------------------------------------------------------
                */

                $title_text = strtolower($title);

                $title_count = substr_count(
                    $title_text,
                    $keyword
                );

                if ($title_count > 0) {

                    $scores[$category] +=
                        ($title_count * 3);
                }
            }
        }
    }


    $best_category = "Other";
    $highest_score = 0;


    foreach ($scores as $category => $score) {

        if ($score > $highest_score) {

            $highest_score = $score;
            $best_category = $category;
        }
    }


    return $best_category;
}


/*
|--------------------------------------------------------------------------
| GET PETITION ID
|--------------------------------------------------------------------------
*/

$petition_id = isset($_GET['id'])
    ? (int) $_GET['id']
    : 0;


if ($petition_id < 1) {

    set_flash(
        "error",
        "Invalid petition ID."
    );

    redirect("admin_petitions.php");
}


/*
|--------------------------------------------------------------------------
| GET PETITION
|--------------------------------------------------------------------------
*/

$stmt = $conn->prepare(
    "SELECT
        id,
        title,
        description,
        category,
        goal,
        status
     FROM petitions
     WHERE id = ?
     LIMIT 1"
);


if (!$stmt) {

    die(
        "Database error: " .
        $conn->error
    );
}


$stmt->bind_param(
    "i",
    $petition_id
);


if (!$stmt->execute()) {

    die(
        "Database error: " .
        $stmt->error
    );
}


$result = $stmt->get_result();

$petition = $result->fetch_assoc();

$stmt->close();


if (!$petition) {

    set_flash(
        "error",
        "Petition not found."
    );

    redirect("admin_petitions.php");
}


$errors = [];


/*
|--------------------------------------------------------------------------
| UPDATE PETITION
|--------------------------------------------------------------------------
*/

if (is_post()) {

    verify_csrf();

    $title = trim($_POST['title'] ?? '');
    $description = trim($_POST['description'] ?? '');
    $goal = (int) ($_POST['goal'] ?? 0);
    $status = $_POST['status'] ?? 'draft';
    $category = trim($_POST['category'] ?? 'auto');


    /*
    |--------------------------------------------------------------------------
    | VALIDATE TITLE
    |--------------------------------------------------------------------------
    */

    if ($title === '') {

        $errors[] = "Petition title is required.";

    } elseif (strlen($title) < 5) {

        $errors[] =
            "Petition title must contain at least 5 characters.";

    } elseif (strlen($title) > 255) {

        $errors[] =
            "Petition title cannot exceed 255 characters.";
    }


    /*
    |--------------------------------------------------------------------------
    | VALIDATE DESCRIPTION
    |--------------------------------------------------------------------------
    */

    if ($description === '') {

        $errors[] =
            "Petition description is required.";

    } elseif (strlen($description) < 20) {

        $errors[] =
            "Petition description must contain at least 20 characters.";
    }


    /*
    |--------------------------------------------------------------------------
    | VALIDATE GOAL
    |--------------------------------------------------------------------------
    */

    if ($goal < 1) {

        $errors[] =
            "Signature goal must be at least 1.";

    } elseif ($goal > 1000000000) {

        $errors[] =
            "Signature goal is too large.";
    }


    /*
    |--------------------------------------------------------------------------
    | VALIDATE STATUS
    |--------------------------------------------------------------------------
    */

    $allowed_statuses = [
        "draft",
        "active",
        "closed"
    ];


    if (!in_array(
        $status,
        $allowed_statuses,
        true
    )) {

        $errors[] =
            "Invalid petition status.";
    }


    /*
    |--------------------------------------------------------------------------
    | VALIDATE CATEGORY
    |--------------------------------------------------------------------------
    */

    if (
        $category !== 'auto' &&
        !in_array(
            $category,
            $petition_categories,
            true
        )
    ) {

        $errors[] =
            "Invalid petition category.";
    }


    /*
    |--------------------------------------------------------------------------
    | AUTOMATIC CATEGORY
    |--------------------------------------------------------------------------
    */

    if (
        empty($errors) &&
        $category === 'auto'
    ) {

        $category = detect_petition_category(
            $title,
            $description
        );
    }


    /*
    |--------------------------------------------------------------------------
    | UPDATE DATABASE
    |--------------------------------------------------------------------------
    */

    if (empty($errors)) {

        $stmt = $conn->prepare(
            "UPDATE petitions
             SET title = ?,
                 description = ?,
                 category = ?,
                 goal = ?,
                 status = ?
             WHERE id = ?"
        );


        if (!$stmt) {

            die(
                "Database error: " .
                $conn->error
            );
        }


        $stmt->bind_param(
            "sssisi",
            $title,
            $description,
            $category,
            $goal,
            $status,
            $petition_id
        );


        if ($stmt->execute()) {

            $stmt->close();


            set_flash(
                "success",
                "Petition updated successfully. Category: " .
                $category
            );


            redirect(
                "admin_petitions.php"
            );

        } else {

            $errors[] =
                "Unable to update petition: " .
                $stmt->error;

            $stmt->close();
        }
    }


    /*
    |--------------------------------------------------------------------------
    | KEEP SUBMITTED VALUES
    |--------------------------------------------------------------------------
    */

    $petition['title'] =
        $title;

    $petition['description'] =
        $description;

    $petition['goal'] =
        $goal;

    $petition['status'] =
        $status;

    $petition['category'] =
        $category === 'auto'
            ? detect_petition_category(
                $title,
                $description
            )
            : $category;
}


/*
|--------------------------------------------------------------------------
| CURRENT CATEGORY
|--------------------------------------------------------------------------
*/

$current_category =
    trim($petition['category'] ?? '');


if ($current_category === '') {

    $current_category = 'Other';
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

    <title>Edit Petition</title>

    <link
        rel="stylesheet"
        href="css/admin.css"
    >

</head>


<body>


<div class="admin-layout">


    <!-- ======================================================
         SIDEBAR
    ======================================================= -->

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


        <a
            href="admin_petitions.php"
            class="active"
        >
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


        <a href="admin_statistics.php">
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


    <!-- ======================================================
         MAIN
    ======================================================= -->

    <main class="admin-main">


        <!-- TOP BAR -->

        <header class="admin-topbar">

            <h2>
                Edit Petition
            </h2>

            <div class="admin-user">
                Administrator
            </div>

        </header>


        <!-- CONTENT -->

        <div class="admin-content">


            <!-- PAGE HEADER -->

            <div class="admin-page-header">

                <div>

                    <h1>
                        Edit Petition
                    </h1>

                    <p>
                        Update petition information, category and status.
                    </p>

                </div>


                <a
                    href="admin_petitions.php"
                    class="admin-btn admin-btn-light"
                >
                    ← Back to Petitions
                </a>

            </div>


            <!-- ==================================================
                 ERRORS
            =================================================== -->

            <?php if (!empty($errors)): ?>

                <div class="admin-alert admin-alert-danger">

                    <strong>
                        Please fix the following:
                    </strong>

                    <br><br>

                    <?php foreach ($errors as $error): ?>

                        <div>
                            <?= e($error) ?>
                        </div>

                    <?php endforeach; ?>

                </div>

            <?php endif; ?>


            <!-- ==================================================
                 EDIT FORM
            =================================================== -->

            <div class="admin-panel">


                <h2>
                    Petition Information
                </h2>


                <form method="POST">


                    <?= csrf_field() ?>


                    <!-- TITLE -->

                    <div class="admin-form-group">

                        <label for="title">
                            Petition Title
                        </label>

                        <input
                            type="text"
                            id="title"
                            name="title"
                            class="admin-input"
                            maxlength="255"
                            value="<?= e($petition['title']) ?>"
                            required
                        >

                    </div>


                    <!-- DESCRIPTION -->

                    <div class="admin-form-group">

                        <label for="description">
                            Petition Description
                        </label>

                        <textarea
                            id="description"
                            name="description"
                            class="admin-textarea"
                            rows="12"
                            required
                        ><?= e($petition['description']) ?></textarea>

                    </div>


                    <!-- ==================================================
                         CATEGORY
                    =================================================== -->

                    <div class="admin-form-group">

                        <label for="category">
                            Petition Category
                        </label>


                        <select
                            id="category"
                            name="category"
                            class="admin-select"
                            required
                        >

                            <option value="auto">
                                🤖 Auto-detect from petition content
                            </option>


                            <?php foreach ($petition_categories as $category_option): ?>

                                <option
                                    value="<?= e($category_option) ?>"
                                    <?= $current_category === $category_option
                                        ? 'selected'
                                        : '' ?>
                                >
                                    <?= e($category_option) ?>
                                </option>

                            <?php endforeach; ?>


                        </select>


                        <p
                            style="
                                margin:8px 0 0;
                                font-size:13px;
                                opacity:0.75;
                                line-height:1.5;
                            "
                        >
                            Choose <strong>Auto-detect</strong> to let the
                            system determine the category from the title and
                            description. You can also manually select a
                            category.
                        </p>


                        <div
                            style="
                                margin-top:12px;
                                padding:12px 15px;
                                border-radius:10px;
                                background:rgba(30,180,212,0.08);
                                border:1px solid rgba(30,180,212,0.18);
                            "
                        >

                            <strong>
                                Current category:
                            </strong>

                            <?= e($current_category) ?>

                        </div>

                    </div>


                    <!-- GOAL -->

                    <div class="admin-form-group">

                        <label for="goal">
                            Signature Goal
                        </label>

                        <input
                            type="number"
                            id="goal"
                            name="goal"
                            class="admin-input"
                            min="1"
                            max="1000000000"
                            value="<?= e($petition['goal']) ?>"
                            required
                        >

                    </div>


                    <!-- STATUS -->

                    <div class="admin-form-group">

                        <label for="status">
                            Petition Status
                        </label>

                        <select
                            id="status"
                            name="status"
                            class="admin-select"
                            required
                        >

                            <option
                                value="draft"
                                <?= $petition['status'] === 'draft'
                                    ? 'selected'
                                    : '' ?>
                            >
                                Draft
                            </option>


                            <option
                                value="active"
                                <?= $petition['status'] === 'active'
                                    ? 'selected'
                                    : '' ?>
                            >
                                Active
                            </option>


                            <option
                                value="closed"
                                <?= $petition['status'] === 'closed'
                                    ? 'selected'
                                    : '' ?>
                            >
                                Closed
                            </option>

                        </select>

                    </div>


                    <!-- BUTTONS -->

                    <div>

                        <button
                            type="submit"
                            class="admin-btn admin-btn-primary"
                        >
                            Save Changes
                        </button>


                        <a
                            href="admin_petitions.php"
                            class="admin-btn admin-btn-light"
                        >
                            Cancel
                        </a>

                    </div>


                </form>


            </div>


        </div>


    </main>


</div>


</body>

</html>