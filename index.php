<?php
$year = date('Y');
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Saraswati Abhyasika | Modern Study Library</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <main class="site-shell">
        <nav class="topbar" aria-label="Primary navigation">
            <a class="brand" href="index.php">
                <img src="IMAGES/SHREE SARASWATI ABHYASIKA LOGO.png" alt="Saraswati Abhyasika logo">
                <span>सरस्वती अभ्यासिका</span>
            </a>
            <div class="nav-actions">
                <a href="USER/index.php">Student Login</a>
                <a class="admin-link" href="pratik2002/index.php">Admin</a>
            </div>
        </nav>

        <section class="hero">
            <div class="hero-copy">
                <p class="eyebrow">Focused seats • Simple booking • Secure login</p>
                <h1>A calm, organized study space for every learner.</h1>
                <p class="hero-text">Manage library seats, subscriptions, attendance and payments from one responsive portal built for Saraswati Abhyasika students and administrators.</p>
                <div class="hero-buttons">
                    <a class="btn primary" href="USER/register.php">Create Student Account</a>
                    <a class="btn secondary" href="USER/index.php">Login with Email</a>
                </div>
            </div>
            <div class="hero-card" aria-label="Library highlights">
                <img src="IMAGES/WhatsApp Image 2026-06-22 at 5.33.20 PM.jpeg" alt="Saraswati Abhyasika study area">
                <div class="stats-grid">
                    <div><strong>39</strong><span>Study Tables</span></div>
                    <div><strong>24/7</strong><span>Digital Access</span></div>
                    <div><strong>UPI</strong><span>Payments</span></div>
                </div>
            </div>
        </section>

        <section class="features" aria-label="Portal features">
            <article>
                <span>01</span>
                <h2>Email Login</h2>
                <p>Students sign in with their registered email and password, with account lockout protection and audit logs.</p>
            </article>
            <article>
                <span>02</span>
                <h2>Seat Management</h2>
                <p>See available, booked and maintenance tables clearly from responsive student and admin dashboards.</p>
            </article>
            <article>
                <span>03</span>
                <h2>Subscriptions</h2>
                <p>Track plans, renewal dates, payments and notifications without manual paper records.</p>
            </article>
        </section>

        <footer class="footer">© <?= htmlspecialchars($year) ?> Saraswati Abhyasika. All rights reserved.</footer>
    </main>
</body>
</html>
