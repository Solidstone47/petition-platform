<?php

require_once "../includes/db.php";
require_once "../includes/functions.php";
require_once "../includes/auth.php";

require_admin();

$errors = [];


/*
|--------------------------------------------------------------------------
| AUTOMATIC PETITION CATEGORY
|--------------------------------------------------------------------------
|
| The system examines the petition title and description and assigns
| the most relevant category automatically.
|
*/

function detect_petition_category($title, $description)
{
    $text = strtolower(
        trim($title . ' ' . $description)
    );


    /*
    |--------------------------------------------------------------------------
    | CATEGORY KEYWORDS
    |--------------------------------------------------------------------------
    */

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


    /*
    |--------------------------------------------------------------------------
    | SCORE EACH CATEGORY
    |--------------------------------------------------------------------------
    */

    $scores = [];

    foreach ($categories as $category => $keywords) {

        $scores[$category] = 0;

        foreach ($keywords as $keyword) {

            /*
            | Count occurrences of each keyword.
            */

            $count = substr_count(
                $text,
                strtolower($keyword)
            );

            if ($count > 0) {

                /*
                | Title matches are given extra importance.
                */

                $title_text = strtolower($title);

                $title_count = substr_count(
                    $title_text,
                    strtolower($keyword)
                );

                $scores[$category] += $count;

                /*
                | Give title keywords additional weight.
                */

                if ($title_count > 0) {
                    $scores[$category] += ($title_count * 3);
                }
            }
        }
    }


    /*
    |--------------------------------------------------------------------------
    | FIND HIGHEST SCORE
    |--------------------------------------------------------------------------
    */

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
| CREATE PETITION
|--------------------------------------------------------------------------
*/

if (is_post()) {

    verify_csrf();

    $title = trim($_POST['title'] ?? '');
    $description = trim($_POST['description'] ?? '');
    $goal = (int) ($_POST['goal'] ?? 0);


    /*
    |--------------------------------------------------------------------------
    | VALIDATE TITLE
    |--------------------------------------------------------------------------
    */

    if ($title === '') {

        $errors[] = "Petition title is required.";

    } elseif (strlen($title) < 5) {

        $errors[] = "Petition title must contain at least 5 characters.";

    } elseif (strlen($title) > 255) {

        $errors[] = "Petition title cannot exceed 255 characters.";
    }


    /*
    |--------------------------------------------------------------------------
    | VALIDATE DESCRIPTION
    |--------------------------------------------------------------------------
    */

    if ($description === '') {

        $errors[] = "Petition description is required.";

    } elseif (strlen($description) < 20) {

        $errors[] = "Petition description must contain at least 20 characters.";
    }


    /*
    |--------------------------------------------------------------------------
    | VALIDATE GOAL
    |--------------------------------------------------------------------------
    */

    if ($goal < 1) {

        $errors[] = "Signature goal must be at least 1.";

    } elseif ($goal > 1000000000) {

        $errors[] = "Signature goal is too large.";
    }


    /*
    |--------------------------------------------------------------------------
    | INSERT PETITION
    |--------------------------------------------------------------------------
    */

    if (empty($errors)) {

        $admin_id = $_SESSION['admin_id'];

        $status = "draft";


        /*
        |--------------------------------------------------------------------------
        | AUTOMATIC CATEGORY
        |--------------------------------------------------------------------------
        */

        $category = detect_petition_category(
            $title,
            $description
        );


        /*
        |--------------------------------------------------------------------------
        | SAVE PETITION
        |--------------------------------------------------------------------------
        */

        $stmt = $conn->prepare(
            "INSERT INTO petitions
            (
                title,
                description,
                category,
                goal,
                status,
                created_by
            )
            VALUES (?, ?, ?, ?, ?, ?)"
        );


        if (!$stmt) {

            $errors[] = "Unable to prepare the petition.";

        } else {

            $stmt->bind_param(
                "sssisi",
                $title,
                $description,
                $category,
                $goal,
                $status,
                $admin_id
            );


            if ($stmt->execute()) {

                set_flash(
                    "success",
                    "Petition created successfully as a draft and categorized as " .
                    $category . "."
                );

                $stmt->close();

                redirect("admin_dashboard.php");

            } else {

                $errors[] = "Unable to create the petition.";

                $stmt->close();
            }
        }
    }
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

    <title>Create Petition</title>

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
                Create Petition
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
                        Create Petition
                    </h1>

                    <p>
                        Create a new petition for the platform.
                        The system will automatically determine its category.
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

                    <?php foreach ($errors as $error): ?>

                        <div>
                            <?= e($error) ?>
                        </div>

                    <?php endforeach; ?>

                </div>

            <?php endif; ?>


            <!-- ==================================================
                 CREATE FORM
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
                            value="<?= e($_POST['title'] ?? '') ?>"
                            maxlength="255"
                            placeholder="Enter petition title"
                            required
                        >

                    </div>


                    <!-- DESCRIPTION -->

                    <div class="admin-form-group">

                        <label for="description">
                            Description
                        </label>

                        <textarea
                            id="description"
                            name="description"
                            class="admin-textarea"
                            rows="10"
                            placeholder="Enter the petition description"
                            required
                        ><?= e($_POST['description'] ?? '') ?></textarea>

                    </div>


                    <!-- AUTOMATIC CATEGORY INFORMATION -->

                    <div
                        style="
                            padding:16px 18px;
                            margin-bottom:20px;
                            border-radius:12px;
                            background:rgba(30,180,212,0.08);
                            border:1px solid rgba(30,180,212,0.20);
                        "
                    >

                        <strong>
                            Automatic Categorization
                        </strong>

                        <p
                            style="
                                margin:7px 0 0;
                                opacity:0.8;
                                line-height:1.6;
                            "
                        >
                            This petition will automatically be categorized
                            using its title and description when you create it.
                            If no relevant category is detected, it will be
                            assigned to <strong>Other</strong>.
                        </p>

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
                            value="<?= e($_POST['goal'] ?? '1000') ?>"
                            placeholder="Example: 1000"
                            required
                        >

                    </div>


                    <!-- BUTTONS -->

                    <div>

                        <button
                            type="submit"
                            class="admin-btn admin-btn-primary"
                        >
                            Create Petition
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