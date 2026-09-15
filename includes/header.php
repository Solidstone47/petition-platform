<?php

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$user_logged_in = isset($_SESSION['user_id']);

?>

<header class="site-header">

    <div class="site-header-inner">

        <!-- LOGO -->
        <div class="site-logo">
            <a href="index.php">
                Petition Platform
            </a>
        </div>


        <!-- CENTER NAVIGATION -->
        <nav class="site-nav">

            <a
                href="index.php"
                class="<?= basename($_SERVER['PHP_SELF']) === 'index.php' ? 'active' : '' ?>"
            >
                Home
            </a>

            <a
                href="petitions.php"
                class="<?= basename($_SERVER['PHP_SELF']) === 'petitions.php' ? 'active' : '' ?>"
            >
                Petitions
            </a>

            <a
                href="contact.php"
                class="<?= basename($_SERVER['PHP_SELF']) === 'contact.php' ? 'active' : '' ?>"
            >
                Contact
            </a>

            <?php if ($user_logged_in): ?>

                <a
                    href="dashboard.php"
                    class="<?= basename($_SERVER['PHP_SELF']) === 'dashboard.php' ? 'active' : '' ?>"
                >
                    Dashboard
                </a>

                <a href="logout.php">
                    Logout
                </a>

            <?php else: ?>

                <a
                    href="login.php"
                    class="<?= basename($_SERVER['PHP_SELF']) === 'login.php' ? 'active' : '' ?>"
                >
                    Login
                </a>

                <a
                    href="register.php"
                    class="site-register-btn"
                >
                    Register
                </a>

            <?php endif; ?>

        </nav>


        <!-- TANZANIA WAVING FLAG -->
        <div class="site-flag">

            <div
                id="tanzaniaFlag"
                class="tanzania-flag-animation"
            ></div>

        </div>

    </div>

</header>


<style>

    /* =====================================================
       HEADER
    ===================================================== */

    .site-header {

        width: 100%;

        background: #ffffff;

        border-bottom: 1px solid #e5e7eb;

        position: relative;

        z-index: 1000;

    }


    .site-header-inner {

        width: 100%;

        max-width: 1200px;

        min-height: 72px;

        margin: 0 auto;

        padding: 0 25px;

        box-sizing: border-box;

        display: flex;

        align-items: center;

        justify-content: space-between;

    }


    /* =====================================================
       LOGO
    ===================================================== */

    .site-logo {

        flex: 1;

        display: flex;

        align-items: center;

    }


    .site-logo a {

        text-decoration: none;

        color: var(--primary);

        font-size: 21px;

        font-weight: 800;

        white-space: nowrap;

    }


    .site-logo a:hover {

        text-decoration: none;

        color: var(--primary);

    }


    /* =====================================================
       CENTER NAVIGATION
    ===================================================== */

    .site-nav {

        flex: 2;

        display: flex;

        align-items: center;

        justify-content: center;

        gap: 24px;

    }


    .site-nav a {

        color: #334155;

        text-decoration: none;

        font-size: 15px;

        font-weight: 600;

        white-space: nowrap;

        transition:
            color 0.2s ease,
            background 0.2s ease;

    }


    .site-nav a:hover {

        color: var(--primary);

    }


    /* =====================================================
       ACTIVE NAVIGATION
    ===================================================== */

    .site-nav a.active {

        color: var(--primary);

        font-weight: 800;

    }


    /* =====================================================
       REGISTER BUTTON
    ===================================================== */

    .site-register-btn {

        background: var(--gold);

        color: #111827 !important;

        padding: 9px 17px;

        border-radius: 7px;

        font-weight: 700 !important;

    }


    .site-register-btn:hover {

        opacity: 0.9;

        color: #111827 !important;

    }


    /* =====================================================
       FLAG AREA
    ===================================================== */

    .site-flag {

        flex: 1;

        display: flex;

        align-items: center;

        justify-content: flex-end;

        min-width: 90px;

    }


    .tanzania-flag-animation {

        width: 65px;

        height: 45px;

        overflow: hidden;

    }


    .tanzania-flag-animation svg {

        width: 100% !important;

        height: 100% !important;

    }


    /* =====================================================
       MOBILE
    ===================================================== */

    @media (max-width: 800px) {

        .site-header-inner {

            min-height: auto;

            padding: 14px 18px;

            flex-direction: column;

            gap: 13px;

        }


        .site-logo {

            justify-content: center;

        }


        .site-nav {

            width: 100%;

            flex-wrap: wrap;

            gap: 12px 18px;

        }


        .site-flag {

            justify-content: center;

        }


        .tanzania-flag-animation {

            width: 60px;

            height: 40px;

        }

    }


    @media (max-width: 500px) {

        .site-nav {

            gap: 10px 14px;

        }


        .site-nav a {

            font-size: 14px;

        }


        .site-logo a {

            font-size: 19px;

        }

    }

</style>


<!-- =====================================================
     LOTTIE WEB
===================================================== -->

<script src="https://cdnjs.cloudflare.com/ajax/libs/lottie-web/5.12.2/lottie.min.js"></script>


<script>

document.addEventListener("DOMContentLoaded", function () {

    const flagContainer =
        document.getElementById("tanzaniaFlag");


    if (!flagContainer) {
        return;
    }


    lottie.loadAnimation({

        container: flagContainer,

        renderer: "svg",

        loop: true,

        autoplay: true,

        path: "assets/animations/Tanzania flag Lottie JSON animation.json"

    });

});

</script>