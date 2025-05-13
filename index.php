<?php
session_start();
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Hospital Management System</title>
    <link rel="stylesheet" href="css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
    <link rel="stylesheet" href="../css/doctor.css">
    <style>
        :root {
            --primary-blue: #1a73e8;
            --dark-blue: #0d47a1;
            --light-blue: #e8f0fe;
            --white: #ffffff;
        }

        body {
            margin: 0;
            font-family: 'Arial', sans-serif;
            color: #333;
        }

        /* Hero Section */
        .hero {
            background: linear-gradient(rgba(26, 115, 232, 0.9), rgba(13, 71, 161, 0.9)), url('images/hospital-bg.jpg');
            background-size: cover;
            background-position: center;
            height: 100vh;
            display: flex;
            align-items: center;
            color: var(--white);
            text-align: center;
        }

        .hero-content {
            max-width: 800px;
            margin: 0 auto;
            padding: 2rem;
        }

        .hero h1 {
            font-size: 3.5rem;
            margin-bottom: 1rem;
            color: var(--white);
        }

        .hero p {
            font-size: 1.2rem;
            margin-bottom: 2rem;
            color: var(--white);
        }

        .cta-buttons {
            display: flex;
            gap: 1rem;
            justify-content: center;
        }

        .btn-primary {
            background: var(--white);
            color: var(--primary-blue);
            padding: 1rem 2rem;
            border-radius: 5px;
            text-decoration: none;
            transition: all 0.3s;
            font-weight: bold;
        }

        .btn-primary:hover {
            background: var(--light-blue);
            transform: translateY(-2px);
        }

        .btn-secondary {
            background: transparent;
            color: var(--white);
            padding: 1rem 2rem;
            border: 2px solid var(--white);
            border-radius: 5px;
            text-decoration: none;
            transition: all 0.3s;
            font-weight: bold;
        }

        .btn-secondary:hover {
            background: var(--white);
            color: var(--primary-blue);
            transform: translateY(-2px);
        }

        /* Features Section */
        .features {
            padding: 5rem 2rem;
            background: var(--light-blue);
        }

        .features-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 2rem;
            max-width: 1200px;
            margin: 0 auto;
        }

        .feature-card {
            background: var(--white);
            padding: 2rem;
            border-radius: 10px;
            text-align: center;
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.1);
            transition: transform 0.3s;
        }

        .feature-card:hover {
            transform: translateY(-10px);
        }

        .feature-icon {
            font-size: 2.5rem;
            color: var(--primary-blue);
            margin-bottom: 1rem;
        }

        /* Services Section */
        .services {
            padding: 5rem 2rem;
            background: var(--white);
        }

        .services-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 2rem;
            max-width: 1200px;
            margin: 0 auto;
        }

        .service-card {
            background: var(--white);
            border-radius: 10px;
            overflow: hidden;
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.1);
            transition: transform 0.3s;
        }

        .service-card:hover {
            transform: translateY(-5px);
        }

        .service-image {
            height: 200px;
            background-size: cover;
            background-position: center;
        }

        .service-content {
            padding: 1.5rem;
        }

        .service-content h3 {
            color: var(--primary-blue);
        }

        /* Contact Section */
        .contact {
            padding: 5rem 2rem;
            background: var(--light-blue);
        }

        .contact-container {
            max-width: 1200px;
            margin: 0 auto;
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 2rem;
        }

        .contact-info {
            padding: 2rem;
        }

        .contact-info h2 {
            color: var(--primary-blue);
        }

        .contact-form {
            background: var(--white);
            padding: 2rem;
            border-radius: 10px;
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.1);
        }

        .form-group {
            margin-bottom: 1.5rem;
        }

        .form-group label {
            display: block;
            margin-bottom: 0.5rem;
            color: var(--primary-blue);
        }

        .form-group input,
        .form-group textarea {
            width: 100%;
            padding: 0.75rem;
            border: 1px solid #ddd;
            border-radius: 5px;
            transition: border-color 0.3s;
        }

        .form-group input:focus,
        .form-group textarea:focus {
            border-color: var(--primary-blue);
            outline: none;
            box-shadow: 0 0 5px rgba(26, 115, 232, 0.3);
        }

        /* Navigation */
        .navbar {
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            background: var(--primary-blue);
            padding: 1rem 2rem;
            z-index: 1000;
            transition: background 0.3s;
        }

        .nav-content {
            max-width: 1200px;
            margin: 0 auto;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .nav-links {
            display: flex;
            gap: 2rem;
        }

        .nav-links a {
            color: var(--white);
            text-decoration: none;
            transition: color 0.3s;
            font-weight: 500;
        }

        .nav-links a:hover {
            color: var(--light-blue);
        }

        .logo {
            color: var(--white);
            font-size: 1.5rem;
            font-weight: bold;
            text-decoration: none;
        }

        /* Contact Details */
        .contact-details p {
            margin: 1rem 0;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        .contact-details i {
            color: var(--primary-blue);
            font-size: 1.2rem;
        }
    </style>
</head>

<body>
    <!-- Navigation -->
    <nav class="navbar">
        <div class="nav-content">
            <a href="index.php" class="logo">HMS</a>
            <div class="nav-links">
                <a href="#home">Home</a>
                <a href="#about">About</a>
                <a href="#services">Services</a>
                <a href="#contact">Contact</a>
                <?php if (isset($_SESSION['user_id'])): ?>
                    <a href="admin/dashboard.php">Dashboard</a>
                    <a href="logout.php">Logout</a>
                <?php else: ?>
                    <a href="login.php">Login</a>
                <?php endif; ?>
            </div>
        </div>
    </nav>

    <!-- Hero Section -->
    <section class="hero" id="home">
        <div class="hero-content">
            <h1>Welcome to Hospital Management System</h1>
            <p>Providing quality healthcare services with advanced technology and compassionate care.</p>
            <div class="cta-buttons">
                <a href="#services" class="btn-primary">Our Services</a>
                <a href="#contact" class="btn-secondary">Contact Us</a>
            </div>
        </div>
    </section>

    <!-- Features Section -->
    <section class="features" id="about">
        <div class="features-grid">
            <div class="feature-card">
                <div class="feature-icon">
                    <i class="fas fa-user-md"></i>
                </div>
                <h3>Expert Doctors</h3>
                <p>Our team of experienced doctors provides the best medical care.</p>
            </div>
            <div class="feature-card">
                <div class="feature-icon">
                    <i class="fas fa-clock"></i>
                </div>
                <h3>24/7 Service</h3>
                <p>Round-the-clock medical services for emergency care.</p>
            </div>
            <div class="feature-card">
                <div class="feature-icon">
                    <i class="fas fa-hospital"></i>
                </div>
                <h3>Modern Facilities</h3>
                <p>State-of-the-art medical equipment and facilities.</p>
            </div>
        </div>
    </section>

    <!-- Services Section -->
    <section class="services" id="services">
        <div class="services-grid">
            <div class="service-card">
                <div class="service-image" style="background-image: url('images/emergency.jpg');"></div>
                <div class="service-content">
                    <h3>Emergency Care</h3>
                    <p>24/7 emergency medical services with rapid response teams.</p>
                </div>
            </div>
            <div class="service-card">
                <div class="service-image" style="background-image: url('images/surgery.jpg');"></div>
                <div class="service-content">
                    <h3>Surgical Services</h3>
                    <p>Advanced surgical procedures with modern technology.</p>
                </div>
            </div>
            <div class="service-card">
                <div class="service-image" style="background-image: url('images/lab.jpg');"></div>
                <div class="service-content">
                    <h3>Laboratory Services</h3>
                    <p>Comprehensive diagnostic and testing facilities.</p>
                </div>
            </div>
        </div>
    </section>

    <!-- Contact Section -->
    <section class="contact" id="contact">
        <div class="contact-container">
            <div class="contact-info">
                <h2>Contact Us</h2>
                <p>Get in touch with us for any inquiries or appointments.</p>
                <div class="contact-details">
                    <p><i class="fas fa-map-marker-alt"></i> 123 Hospital Street, Medical City</p>
                    <p><i class="fas fa-phone"></i> +1 234 567 8900</p>
                    <p><i class="fas fa-envelope"></i> info@hospital.com</p>
                </div>
            </div>
            <div class="contact-form">
                <form action="process_contact.php" method="POST">
                    <div class="form-group">
                        <label for="name">Name</label>
                        <input type="text" id="name" name="name" required>
                    </div>
                    <div class="form-group">
                        <label for="email">Email</label>
                        <input type="email" id="email" name="email" required>
                    </div>
                    <div class="form-group">
                        <label for="message">Message</label>
                        <textarea id="message" name="message" rows="5" required></textarea>
                    </div>
                    <button type="submit" class="btn-primary">Send Message</button>
                </form>
            </div>
        </div>
    </section>

    <script>
        // Smooth scrolling for navigation links
        document.querySelectorAll('a[href^="#"]').forEach(anchor => {
            anchor.addEventListener('click', function(e) {
                e.preventDefault();
                document.querySelector(this.getAttribute('href')).scrollIntoView({
                    behavior: 'smooth'
                });
            });
        });

        // Navbar background change on scroll
        window.addEventListener('scroll', function() {
            const navbar = document.querySelector('.navbar');
            if (window.scrollY > 50) {
                navbar.style.background = 'rgba(0, 0, 0, 0.9)';
            } else {
                navbar.style.background = 'rgba(0, 0, 0, 0.8)';
            }
        });
    </script>
</body>

</html>