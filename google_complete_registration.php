<?php

require_once "includes/db.php";
require_once "includes/functions.php";
require_once "includes/auth.php";


// ======================================================
// CHECK GOOGLE SIGNUP SESSION
// ======================================================

if (
    !isset($_SESSION['google_signup']) ||
    !is_array($_SESSION['google_signup'])
) {

    set_flash(
        "error",
        "Google registration session has expired. Please try again."
    );

    redirect("register.php");
}


// ======================================================
// GET GOOGLE INFORMATION
// ======================================================

$google_id = trim(
    $_SESSION['google_signup']['google_id'] ?? ''
);

$google_email = trim(
    $_SESSION['google_signup']['email'] ?? ''
);

$google_name = trim(
    $_SESSION['google_signup']['fullname'] ?? ''
);


// ======================================================
// VALIDATE GOOGLE SESSION DATA
// ======================================================

if (
    $google_id === '' ||
    $google_email === '' ||
    $google_name === ''
) {

    unset($_SESSION['google_signup']);

    set_flash(
        "error",
        "Unable to complete Google registration. Please try again."
    );

    redirect("register.php");
}


// ======================================================
// COUNTRIES
// ======================================================

$countries = [

    "Afghanistan",
    "Albania",
    "Algeria",
    "Andorra",
    "Angola",
    "Antigua and Barbuda",
    "Argentina",
    "Armenia",
    "Australia",
    "Austria",
    "Azerbaijan",
    "Bahamas",
    "Bahrain",
    "Bangladesh",
    "Barbados",
    "Belarus",
    "Belgium",
    "Belize",
    "Benin",
    "Bhutan",
    "Bolivia",
    "Bosnia and Herzegovina",
    "Botswana",
    "Brazil",
    "Brunei",
    "Bulgaria",
    "Burkina Faso",
    "Burundi",
    "Cabo Verde",
    "Cambodia",
    "Cameroon",
    "Canada",
    "Central African Republic",
    "Chad",
    "Chile",
    "China",
    "Colombia",
    "Comoros",
    "Congo",
    "Costa Rica",
    "Croatia",
    "Cuba",
    "Cyprus",
    "Czechia",
    "Democratic Republic of the Congo",
    "Denmark",
    "Djibouti",
    "Dominica",
    "Dominican Republic",
    "Ecuador",
    "Egypt",
    "El Salvador",
    "Equatorial Guinea",
    "Eritrea",
    "Estonia",
    "Eswatini",
    "Ethiopia",
    "Fiji",
    "Finland",
    "France",
    "Gabon",
    "Gambia",
    "Georgia",
    "Germany",
    "Ghana",
    "Greece",
    "Grenada",
    "Guatemala",
    "Guinea",
    "Guinea-Bissau",
    "Guyana",
    "Haiti",
    "Honduras",
    "Hungary",
    "Iceland",
    "India",
    "Indonesia",
    "Iran",
    "Iraq",
    "Ireland",
    "Israel",
    "Italy",
    "Ivory Coast",
    "Jamaica",
    "Japan",
    "Jordan",
    "Kazakhstan",
    "Kenya",
    "Kiribati",
    "Kuwait",
    "Laos",
    "Latvia",
    "Lebanon",
    "Lesotho",
    "Liberia",
    "Libya",
    "Liechtenstein",
    "Lithuania",
    "Luxembourg",
    "Madagascar",
    "Malawi",
    "Malaysia",
    "Maldives",
    "Mali",
    "Malta",
    "Marshall Islands",
    "Mauritania",
    "Mauritius",
    "Mexico",
    "Micronesia",
    "Moldova",
    "Monaco",
    "Mongolia",
    "Montenegro",
    "Morocco",
    "Mozambique",
    "Myanmar",
    "Namibia",
    "Nauru",
    "Nepal",
    "Netherlands",
    "New Zealand",
    "Nicaragua",
    "Niger",
    "Nigeria",
    "North Korea",
    "North Macedonia",
    "Norway",
    "Oman",
    "Pakistan",
    "Palau",
    "Palestine",
    "Panama",
    "Papua New Guinea",
    "Paraguay",
    "Peru",
    "Philippines",
    "Poland",
    "Portugal",
    "Qatar",
    "Romania",
    "Russia",
    "Rwanda",
    "Saint Kitts and Nevis",
    "Saint Lucia",
    "Saint Vincent and the Grenadines",
    "Samoa",
    "San Marino",
    "Sao Tome and Principe",
    "Saudi Arabia",
    "Senegal",
    "Serbia",
    "Seychelles",
    "Sierra Leone",
    "Singapore",
    "Slovakia",
    "Slovenia",
    "Solomon Islands",
    "Somalia",
    "South Africa",
    "South Korea",
    "South Sudan",
    "Spain",
    "Sri Lanka",
    "Sudan",
    "Suriname",
    "Sweden",
    "Switzerland",
    "Syria",
    "Tajikistan",
    "Tanzania",
    "Thailand",
    "Timor-Leste",
    "Togo",
    "Tonga",
    "Trinidad and Tobago",
    "Tunisia",
    "Türkiye",
    "Turkmenistan",
    "Tuvalu",
    "Uganda",
    "Ukraine",
    "United Arab Emirates",
    "United Kingdom",
    "United States",
    "Uruguay",
    "Uzbekistan",
    "Vanuatu",
    "Vatican City",
    "Venezuela",
    "Vietnam",
    "Yemen",
    "Zambia",
    "Zimbabwe"

];


// ======================================================
// VARIABLES
// ======================================================

$errors = [];

$selected_country =
    $_POST['country'] ?? '';

$phone =
    trim($_POST['phone'] ?? '');


// ======================================================
// HANDLE REGISTRATION
// ======================================================

if (is_post()) {

    verify_csrf();


    // ==================================================
    // COUNTRY
    // ==================================================

    if ($selected_country === '') {

        $errors[] =
            "Please select your country.";

    } elseif (
        !in_array(
            $selected_country,
            $countries,
            true
        )
    ) {

        $errors[] =
            "Please select a valid country.";
    }


    // ==================================================
    // TANZANIA PHONE
    // ==================================================

    if ($selected_country === "Tanzania") {

        if ($phone === '') {

            $errors[] =
                "Phone number is required for users from Tanzania.";

        } else {

            /*
            --------------------------------------------------
            Accept common Tanzanian formats:

            0712345678
            0612345678
            +255712345678
            255712345678
            --------------------------------------------------
            */

            $clean_phone =
                preg_replace(
                    '/[\s\-\(\)]/',
                    '',
                    $phone
                );


            if (
                !preg_match(
                    '/^(?:0[67]\d{8}|\+255[67]\d{8}|255[67]\d{8})$/',
                    $clean_phone
                )
            ) {

                $errors[] =
                    "Please enter a valid Tanzanian phone number.";

            } else {

                $phone =
                    $clean_phone;
            }
        }

    } else {

        /*
        --------------------------------------------------
        Non-Tanzanian users do not need a phone number.
        --------------------------------------------------
        */

        $phone = '';
    }


    // ==================================================
    // CHECK GOOGLE ACCOUNT AGAIN
    // ==================================================

    if (empty($errors)) {

        $stmt = $conn->prepare("
            SELECT
                id
            FROM users
            WHERE google_id = ?
            LIMIT 1
        ");


        if (!$stmt) {

            $errors[] =
                "Unable to verify your Google account.";

        } else {

            $stmt->bind_param(
                "s",
                $google_id
            );


            $stmt->execute();


            $result =
                $stmt->get_result();


            if ($result->num_rows > 0) {

                $existing =
                    $result->fetch_assoc();


                $existing_id =
                    (int) $existing['id'];


                $stmt->close();


                unset(
                    $_SESSION['google_signup']
                );


                login_user(
                    $existing_id
                );


                set_flash(
                    "success",
                    "Welcome back!"
                );


                redirect("index.php");

            }


            $stmt->close();
        }
    }


    // ==================================================
    // CHECK EMAIL AGAIN
    // ==================================================

    if (empty($errors)) {

        $stmt = $conn->prepare("
            SELECT
                id
            FROM users
            WHERE email = ?
            LIMIT 1
        ");


        if (!$stmt) {

            $errors[] =
                "Unable to verify your email address.";

        } else {

            $stmt->bind_param(
                "s",
                $google_email
            );


            $stmt->execute();


            $result =
                $stmt->get_result();


            if ($result->num_rows > 0) {

                $errors[] =
                    "An account with this email already exists. Please log in using your existing account.";
            }


            $stmt->close();
        }
    }


    // ==================================================
    // CREATE GOOGLE ACCOUNT
    // ==================================================

    if (empty($errors)) {

        /*
        --------------------------------------------------
        Google has already authenticated the user.

        Therefore we do not need to create a normal
        password for this account.
        --------------------------------------------------
        */

        $password = null;


        $stmt = $conn->prepare("
            INSERT INTO users
            (
                fullname,
                email,
                phone,
                country,
                password,
                google_id
            )
            VALUES (?, ?, ?, ?, ?, ?)
        ");


        if (!$stmt) {

            $errors[] =
                "Unable to create your account. Please try again.";

        } else {

            $stmt->bind_param(
                "ssssss",
                $google_name,
                $google_email,
                $phone,
                $selected_country,
                $password,
                $google_id
            );


            if ($stmt->execute()) {

                $user_id =
                    (int) $stmt->insert_id;


                $stmt->close();


                /*
                --------------------------------------------------
                Registration completed.

                Remove temporary Google signup information.
                --------------------------------------------------
                */

                unset(
                    $_SESSION['google_signup']
                );


                /*
                --------------------------------------------------
                Log user in.
                --------------------------------------------------
                */

                login_user(
                    $user_id
                );


                set_flash(
                    "success",
                    "Your account has been created successfully."
                );


                redirect("index.php");


            } else {

                $errors[] =
                    "Unable to create your account. Please try again.";


                $stmt->close();
            }
        }
    }
}


// ======================================================
// DISPLAY SELECTED COUNTRY
// ======================================================

$country_selected =
    htmlspecialchars(
        $selected_country,
        ENT_QUOTES,
        'UTF-8'
    );

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
        Complete Google Registration - Petition Platform
    </title>


    <style>

        * {
            box-sizing: border-box;
        }


        body {

            margin: 0;

            min-height: 100vh;

            display: flex;

            align-items: center;

            justify-content: center;

            padding: 20px;

            background:
                linear-gradient(
                    135deg,
                    #f5f7fb 0%,
                    #eef2ff 100%
                );

            font-family:
                Arial,
                Helvetica,
                sans-serif;

            color: #111827;

        }


        .container {

            width: 100%;

            max-width: 500px;

        }


        .card {

            background: #ffffff;

            border: 1px solid #e5e7eb;

            border-radius: 20px;

            padding: 34px;

            box-shadow:
                0 15px 45px
                rgba(15, 23, 42, 0.08);

        }


        .google-icon {

            width: 62px;

            height: 62px;

            display: flex;

            align-items: center;

            justify-content: center;

            margin-bottom: 20px;

            border-radius: 17px;

            background: #f3f4f6;

            color: #4285f4;

            font-size: 30px;

            font-weight: 800;

        }


        h1 {

            margin: 0 0 8px;

            font-size: 26px;

            font-weight: 800;

            line-height: 1.2;

        }


        .subtitle {

            margin: 0 0 22px;

            color: #6b7280;

            font-size: 13px;

            line-height: 1.6;

        }


        .google-user {

            padding: 15px;

            margin-bottom: 23px;

            border: 1px solid #e5e7eb;

            border-radius: 12px;

            background: #f9fafb;

        }


        .google-user-name {

            margin-bottom: 4px;

            color: #111827;

            font-size: 14px;

            font-weight: 750;

        }


        .google-user-email {

            color: #6b7280;

            font-size: 12px;

            word-break: break-word;

        }


        .error-box {

            margin-bottom: 20px;

            padding: 13px 15px;

            border: 1px solid #fecaca;

            border-radius: 10px;

            background: #fef2f2;

            color: #b91c1c;

            font-size: 13px;

            line-height: 1.5;

        }


        .error-box p {

            margin: 3px 0;

        }


        .form-group {

            margin-bottom: 18px;

        }


        label {

            display: block;

            margin-bottom: 7px;

            color: #374151;

            font-size: 12px;

            font-weight: 750;

        }


        .required {

            color: #dc2626;

        }


        input,
        select {

            width: 100%;

            height: 47px;

            padding: 0 14px;

            border: 1px solid #d1d5db;

            border-radius: 10px;

            outline: none;

            background: #ffffff;

            color: #111827;

            font-family: inherit;

            font-size: 14px;

            transition:
                border-color 0.2s ease,
                box-shadow 0.2s ease;

        }


        input:focus,
        select:focus {

            border-color: #6366f1;

            box-shadow:
                0 0 0 3px
                rgba(99, 102, 241, 0.10);

        }


        select {

            cursor: pointer;

        }


        .phone-box {

            display: none;

        }


        .phone-box.show {

            display: block;

        }


        .phone-help {

            margin-top: 6px;

            color: #9ca3af;

            font-size: 11px;

            line-height: 1.5;

        }


        .submit-button {

            width: 100%;

            height: 47px;

            margin-top: 4px;

            border: none;

            border-radius: 10px;

            background: #111827;

            color: #ffffff;

            font-family: inherit;

            font-size: 14px;

            font-weight: 750;

            cursor: pointer;

            transition:
                background 0.2s ease,
                transform 0.2s ease;

        }


        .submit-button:hover {

            background: #1f2937;

            transform: translateY(-1px);

        }


        .cancel-link {

            display: block;

            margin-top: 18px;

            text-align: center;

            color: #6b7280;

            font-size: 12px;

            text-decoration: none;

        }


        .cancel-link:hover {

            color: #111827;

            text-decoration: underline;

        }


        .security-note {

            margin-top: 20px;

            padding-top: 18px;

            border-top: 1px solid #eef0f3;

            color: #9ca3af;

            font-size: 10px;

            line-height: 1.5;

            text-align: center;

        }


        @media (max-width: 520px) {

            body {

                padding: 15px;

            }


            .card {

                padding: 25px 20px;

                border-radius: 16px;

            }


            h1 {

                font-size: 23px;

            }

        }

    </style>

</head>


<body>


<div class="container">


    <div class="card">


        <div class="google-icon">
            G
        </div>


        <h1>
            Complete Your Registration
        </h1>


        <p class="subtitle">

            Your Google account has been verified.
            Please select your country to finish creating
            your Petition Platform account.

        </p>


        <!-- ==================================================
             GOOGLE ACCOUNT
        ================================================== -->

        <div class="google-user">

            <div class="google-user-name">

                <?= e($google_name) ?>

            </div>


            <div class="google-user-email">

                <?= e($google_email) ?>

            </div>

        </div>


        <!-- ==================================================
             ERRORS
        ================================================== -->

        <?php if (!empty($errors)): ?>

            <div class="error-box">

                <?php foreach ($errors as $error): ?>

                    <p>
                        <?= e($error) ?>
                    </p>

                <?php endforeach; ?>

            </div>

        <?php endif; ?>


        <!-- ==================================================
             FORM
        ================================================== -->

        <form
            method="POST"
            action=""
            id="completeRegistrationForm"
        >

            <?= csrf_field() ?>


            <!-- COUNTRY -->

            <div class="form-group">

                <label for="country">

                    Country

                    <span class="required">
                        *
                    </span>

                </label>


                <select
                    id="country"
                    name="country"
                    required
                >

                    <option value="">

                        -- Select your country --

                    </option>


                    <?php foreach ($countries as $country_option): ?>

                        <option
                            value="<?= e($country_option) ?>"
                            <?= (
                                $selected_country ===
                                $country_option
                            )
                                ? 'selected'
                                : ''
                            ?>
                        >

                            <?= e($country_option) ?>

                        </option>

                    <?php endforeach; ?>

                </select>

            </div>


            <!-- ==================================================
                 TANZANIA PHONE
            ================================================== -->

            <div
                class="form-group phone-box"
                id="phoneBox"
            >

                <label for="phone">

                    Tanzanian Phone Number

                    <span class="required">
                        *
                    </span>

                </label>


                <input
                    type="tel"
                    id="phone"
                    name="phone"
                    value="<?= e($phone) ?>"
                    placeholder="e.g. 0712345678"
                    autocomplete="tel"
                >


                <div class="phone-help">

                    Required for Tanzanian users.
                    Example: 0712345678 or +255712345678.

                </div>

            </div>


            <!-- ==================================================
                 SUBMIT
            ================================================== -->

            <button
                type="submit"
                class="submit-button"
            >

                Complete Registration

            </button>


        </form>


        <a
            href="register.php"
            class="cancel-link"
        >

            Cancel and return to registration

        </a>


        <div class="security-note">

            Your Google account has been securely verified.
            We only use the information necessary to create
            your Petition Platform account.

        </div>


    </div>


</div>


<script>

    const countrySelect =
        document.getElementById("country");

    const phoneBox =
        document.getElementById("phoneBox");

    const phoneInput =
        document.getElementById("phone");


    function updatePhoneField() {

        if (countrySelect.value === "Tanzania") {

            phoneBox.classList.add("show");

            phoneInput.required = true;

        } else {

            phoneBox.classList.remove("show");

            phoneInput.required = false;

            phoneInput.value = "";
        }
    }


    countrySelect.addEventListener(
        "change",
        updatePhoneField
    );


    /*
    ------------------------------------------------------
    Run immediately in case the page reloads after
    a validation error.
    ------------------------------------------------------
    */

    updatePhoneField();

</script>


</body>

</html>