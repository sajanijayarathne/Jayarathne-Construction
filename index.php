<?php require_once 'includes/header.php'; ?>
<link rel="stylesheet" href="style.css">


<!-- FULL WIDTH HERO SLIDER SECTION -->
<section class="hero-slider-section">
    <div class="slider-container">
        <!-- Slide 1 -->
        <div class="slide active" style="background-image: url('images/slide1.jpg'), url('https://images.unsplash.com/photo-1541888946425-d81bb19240f5?auto=format&fit=crop&w=1600&q=80');">
            <div class="slide-overlay"></div>
        </div>
        <!-- Slide 2 -->
        <div class="slide" style="background-image: url('images/slide2.jpg'), url('https://images.unsplash.com/photo-1504307651254-35680f356dfd?auto=format&fit=crop&w=1600&q=80');">
            <div class="slide-overlay"></div>
        </div>
        <!-- Slide 3 -->
        <div class="slide" style="background-image: url('images/slide3.jpg'), url('https://images.unsplash.com/photo-1486406146926-c627a92ad1ab?auto=format&fit=crop&w=1600&q=80');">
            <div class="slide-overlay"></div>
        </div>
    </div>

    <div class="hero-slider-content">
        <div class="hero-badge-award">
            <span>🏆</span> SMART CONSTRUCTION PLATFORM 2026
        </div>

        <h1 class="hero-slider-title">
            We're Shaping A <br><span>Brighter Future.</span>
        </h1>

        <p class="hero-slider-desc">
            Your construction projects, equipment rentals, and client requests — all managed in one powerful platform designed to keep your work moving forward.
        </p>

        <div style="display: flex; gap: 15px; align-items: center;">
            <a class="button hero-primary" href="<?= !empty($_SESSION['user']) ? 'dashboard.php' : 'register.php' ?>" style="background: #d97706; color: #fff; padding: 14px 28px; border-radius: 8px; font-weight: bold; text-decoration: none;">
                Get Started ↗
            </a>
            <a href="#features" style="color: #ffffff; font-weight: bold; text-decoration: none; padding: 14px 20px;">
                Explore Features ↓
            </a>
        </div>

        <div class="hero-stats-bar">
            <div class="stat-item">
                <h3>15+</h3>
                <p>Years of Experience</p>
            </div>
            <div class="stat-item">
                <h3>+250</h3>
                <p>Mega Projects</p>
            </div>
            <div class="stat-item">
                <h3>100%</h3>
                <p>Quality Guaranteed</p>
            </div>
        </div>
    </div>

    <!-- Dots navigation -->
    <div class="slider-dots">
        <span class="dot active" onclick="setSlide(0)"></span>
        <span class="dot" onclick="setSlide(1)"></span>
        <span class="dot" onclick="setSlide(2)"></span>
    </div>
</section>

<!-- AUTO SLIDER SCRIPT -->
<script>
let currentSlide = 0;
const slides = document.querySelectorAll('.slide');
const dots = document.querySelectorAll('.dot');

function showSlide(index) {
    slides.forEach((slide, i) => {
        slide.classList.remove('active');
        dots[i].classList.remove('active');
    });
    slides[index].classList.add('active');
    dots[index].classList.add('active');
}

function nextSlide() {
    currentSlide = (currentSlide + 1) % slides.length;
    showSlide(currentSlide);
}

function setSlide(index) {
    currentSlide = index;
    showSlide(currentSlide);
}

// Auto change slide every 4 seconds
setInterval(nextSlide, 4000);
</script>

<!-- FEATURES SECTION -->
<section class="home-features" id="features" style="background-image: linear-gradient(rgba(255, 255, 255, 0.92), rgba(255, 255, 255, 0.92)), url('images/123.jpg'); background-size: cover; background-position: center; background-repeat: no-repeat; padding: 60px 20px;">
    <div class="section-heading" style="text-align: center; margin-bottom: 40px;">
        <p class="eyebrow" style="color: #d97706; font-weight: bold; letter-spacing: 1px;">BUILT FOR BETTER WORKFLOWS</p>
        <h2 style="font-size: 2.5rem; color: #1e293b; margin: 10px 0;">Everything your project needs.</h2>
        <p style="color: #64748b;">
            Simplify daily construction operations with tools designed to help you work smarter.
        </p>
    </div>

    <div class="container">
        <div class="feature-grid" style="display: grid; grid-template-columns: repeat(auto-fit, minmax(300px, 1fr)); gap: 30px;">
            
            <!-- Card 1: Project Management -->
            <article class="feature-card" style="overflow: hidden; padding: 0; background: #ffffff; border-radius: 12px; box-shadow: 0 10px 25px rgba(0,0,0,0.06);">
                <div style="height: 180px; overflow: hidden;">
                    <img src="images/project.jpg" alt="Project Management" style="width: 100%; height: 100%; object-fit: cover;" onerror="this.src='https://images.unsplash.com/photo-1541888946425-d81bb19240f5?auto=format&fit=crop&w=600&q=80'">
                </div>
                <div style="padding: 24px;">
                    <h3 style="margin-bottom: 10px; color: #1e293b; font-size: 1.25rem;">Project Management</h3>
                    <p style="color: #64748b; font-size: 0.95rem; margin-bottom: 20px; line-height: 1.5;">
                        Organize project information and monitor progress from one convenient dashboard.
                    </p>
                    <a href="<?= !empty($_SESSION['user']) ? 'dashboard.php' : 'login.php' ?>" style="color: #d97706; font-weight: bold; text-decoration: none;">
                        Explore dashboard <span>→</span>
                    </a>
                </div>
            </article>

            <!-- Card 2: Equipment Rental -->
            <article class="feature-card" style="overflow: hidden; padding: 0; background: #ffffff; border-radius: 12px; box-shadow: 0 10px 25px rgba(0,0,0,0.06);">
                <div style="height: 180px; overflow: hidden;">
                    <img src="images/equipment.jpg" alt="Equipment Rental" style="width: 100%; height: 100%; object-fit: cover;" onerror="this.src='https://images.unsplash.com/photo-1579621970563-ebec7560ff3e?auto=format&fit=crop&w=600&q=80'">
                </div>
                <div style="padding: 24px;">
                    <h3 style="margin-bottom: 10px; color: #1e293b; font-size: 1.25rem;">Equipment Rental</h3>
                    <p style="color: #64748b; font-size: 0.95rem; margin-bottom: 20px; line-height: 1.5;">
                        Manage construction equipment availability and make rental requests more easily.
                    </p>
                    <a href="<?= !empty($_SESSION['user']) ? 'dashboard.php' : 'login.php' ?>" style="color: #d97706; font-weight: bold; text-decoration: none;">
                        Manage equipment <span>→</span>
                    </a>
                </div>
            </article>

            <!-- Card 3: Client Requests -->
            <article class="feature-card" style="overflow: hidden; padding: 0; background: #ffffff; border-radius: 12px; box-shadow: 0 10px 25px rgba(0,0,0,0.06);">
                <div style="height: 180px; overflow: hidden;">
                    <img src="images/clients.jpg" alt="Client Requests" style="width: 100%; height: 100%; object-fit: cover;" onerror="this.src='https://images.unsplash.com/photo-1600880292203-757bb62b4baf?auto=format&fit=crop&w=600&q=80'">
                </div>
                <div style="padding: 24px;">
                    <h3 style="margin-bottom: 10px; color: #1e293b; font-size: 1.25rem;">Client Requests</h3>
                    <p style="color: #64748b; font-size: 0.95rem; margin-bottom: 20px; line-height: 1.5;">
                        Keep client requests organized and make communication across your project workflow simpler.
                    </p>
                    <a href="<?= !empty($_SESSION['user']) ? 'dashboard.php' : 'login.php' ?>" style="color: #d97706; font-weight: bold; text-decoration: none;">
                        Manage requests <span>→</span>
                    </a>
                </div>
            </article>

        </div>
    </div>
</section>
 <!-- CUSTOMER REVIEWS SECTION -->
<section class="customer-reviews-section">
    <div class="container">
        <div class="section-heading">
            <span class="eyebrow">TESTIMONIALS</span>
            <h2>What Our Clients Say</h2>
            <p>Trusted by leading contractors and property developers across Sri Lanka.</p>
        </div>
        
        <div class="reviews-grid">
            <div class="review-card">
                <div class="stars">⭐⭐⭐⭐⭐</div>
                <p>"Jayarathne Construction is a great choice for construction projects. Their work is well organized, and they pay attention to proper planning, quality, and timely completion. From the initial project plan to the final construction work, they provide a reliable service. Highly recommended for anyone looking for professional construction services!"</p>
                <div class="client-info">
                    <strong>Richel</strong>
                    <span>MD.Lakewood Hotel Kandy</span>
                </div>
            </div>

            <div class="review-card">
                <div class="stars">⭐⭐⭐⭐⭐</div>
                <p>"Excellent service for construction work and equipment requirements! Jayarathne Construction offers a convenient solution for people looking for construction services, project planning, and equipment rental. Their practical approach makes construction work easier to organize and manage. A reliable choice for your construction needs! "</p>
                <div class="client-info">
                    <strong>Disasekara</strong>
                    <span>CEO.Kandy Cabana, Kandy</span>
                </div>
            </div>

            <div class="review-card">
                <div class="stars">⭐⭐⭐⭐⭐</div>
                <p>"I had a great experience with Jayarathne Construction. The construction equipment rental service was convenient and reliable, making it easier to find the equipment needed for my project. The equipment helped us carry out our work smoothly and efficiently. Highly recommended for anyone looking for construction equipment rental services!."</p>
                <div class="client-info">
                    <strong>Chathura Bandara</strong>
                    <span>Client, Gampaha</span>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- ABOUT US SECTION -->
<section class="about-us" id="about">
    <div class="about-container">

        <div class="about-image">
            <img
                src="https://images.unsplash.com/photo-1541888946425-d81bb19240f5?auto=format&fit=crop&w=1200&q=85"
                alt="Modern construction project"
                loading="lazy">

            <div class="about-image-badge">
                <span class="about-badge-icon">🏗️</span>
                <div>
                    <strong>Built for Better Results</strong>
                    <p>Smarter Construction Management</p>
                </div>
            </div>
        </div>

        <div class="about-content">

            <p class="about-eyebrow">
                <span></span> ABOUT OUR PLATFORM
            </p>

            <h2>
                Making Construction
                <span>Management Smarter.</span>
            </h2>

            <p class="about-description">
                Our platform brings essential construction activities
                together in one place. From tracking project progress
                and managing equipment rentals to handling client
                requests, we help users organize their work and keep
                important information within reach.
            </p>

            <div class="about-points">
                <div class="about-point">
                    <span class="about-check">✓</span>
                    <div>
                        <strong>Organized Project Tracking</strong>
                        <p>Keep project details and progress organized.</p>
                    </div>
                </div>

                <div class="about-point">
                    <span class="about-check">✓</span>
                    <div>
                        <strong>Equipment Management</strong>
                        <p>Manage equipment information and rental requests.</p>
                    </div>
                </div>

                <div class="about-point">
                    <span class="about-check">✓</span>
                    <div>
                        <strong>Simplified Client Requests</strong>
                        <p>Keep client requests organized in one platform.</p>
                    </div>
                </div>
            </div>

            <div class="about-contact">
                <h3>Get in Touch</h3>

                <div class="contact-item">
                    <span class="contact-icon">✉</span>
                    <div>
                        <strong>Email Us</strong>
                        <a href="mailto:jayarathneconstruction@gmail.com">
                            jayarathneconstruction@gmail.com
                        </a>
                    </div>
                </div>

                <div class="contact-item">
                    <span class="contact-icon">☎</span>
                    <div>
                        <strong>Call Us</strong>
                        <a href="tel:+94777764997">
                            +94 77 7764997
                        </a>
                    </div>
                </div>

                <div class="contact-item">
                    <span class="contact-icon">⌖</span>
                    <div>
                        <strong>Our Location</strong>
                        <p>Pilimathalawa, Sri Lanka</p>
                    </div>
                </div>
            </div>

            <a class="about-button"
               href="<?= !empty($_SESSION['user']) ? 'dashboard.php' : 'register.php' ?>">
                Discover Our Platform
                <span>→</span>
            </a>
     
    

        </div>
    </div>
</section>
<footer class="jc-footer">

    <div class="jc-footer-accent"></div>

    <div class="jc-footer-container">

        <!-- Company Information -->
        <div class="jc-footer-column jc-footer-about">

            <a href="#home" class="jc-footer-brand">
                <span class="jc-footer-logo">JC</span>

                <span class="jc-footer-brand-name">
                    <strong>JAYARATHNE</strong>
                    <span>CONSTRUCTION</span>
                </span>
            </a>

            <p class="jc-footer-description">
                Building with quality, integrity, and commitment.
                Jayarathne Construction is dedicated to delivering
                reliable construction solutions and professional service.
            </p>

            <a href="#contact" class="jc-footer-quote">
                Discuss Your Project
                <span>↗</span>
            </a>

        </div>

        <!-- Quick Links -->
        <div class="jc-footer-column">

            <h3 class="jc-footer-title">QUICK LINKS</h3>

            <ul class="jc-footer-links">
                <li><a href="#home">Home</a></li>
                <li><a href="#about">About Us</a></li>
                <li><a href="#services">Our Services</a></li>
                <li><a href="#projects">Our Projects</a></li>
                <li><a href="#contact">Contact Us</a></li>
            </ul>

        </div>

        <!-- Construction Services -->
        <div class="jc-footer-column">

            <h3 class="jc-footer-title">OUR SERVICES</h3>

            <ul class="jc-footer-links">
                <li><a href="#services">Building Construction</a></li>
                <li><a href="#services">House Construction</a></li>
                <li><a href="#services">Renovation & Remodeling</a></li>
                <li><a href="#services">Commercial Construction</a></li>
                <li><a href="#services">Construction Consultation</a></li>
            </ul>

        </div>

        <!-- Contact Details -->
        <div class="jc-footer-column jc-footer-contact">

            <h3 class="jc-footer-title">GET IN TOUCH</h3>

            <p class="jc-footer-contact-intro">
                Have a project in mind? Get in touch with our team.
            </p>

            <a href="mailto:jayarathneconstruction@gmail.com"
               class="jc-footer-contact-item">

                <span class="jc-footer-icon" aria-hidden="true">
                    <svg viewBox="0 0 24 24" fill="none">
                        <rect x="3" y="5" width="18" height="14"
                              rx="2" stroke="currentColor"
                              stroke-width="1.7"/>
                        <path d="M4 7L12 13L20 7"
                              stroke="currentColor"
                              stroke-width="1.7"
                              stroke-linecap="round"
                              stroke-linejoin="round"/>
                    </svg>
                </span>

                <span>
                    <small>EMAIL ADDRESS</small>
                    <strong>jayarathneconstruction@gmail.com</strong>
                </span>
            </a>

            <a href="tel:+94777764997"
               class="jc-footer-contact-item">

                <span class="jc-footer-icon" aria-hidden="true">
                    <svg viewBox="0 0 24 24" fill="none">
                        <path d="M7 3H4.5C3.67 3 3 3.67 3 4.5
                                 C3 13.61 10.39 21 19.5 21
                                 C20.33 21 21 20.33 21 19.5
                                 V17L16.5 15L14 17.5
                                 C11.2 16.3 7.7 12.8 6.5 10
                                 L9 7.5L7 3Z"
                              stroke="currentColor"
                              stroke-width="1.7"
                              stroke-linecap="round"
                              stroke-linejoin="round"/>
                    </svg>
                </span>

                <span>
                    <small>PHONE NUMBER</small>
                    <strong>+94 77 776 4997</strong>
                </span>
            </a>

            <a href="https://www.google.com/maps/search/?api=1&query=Pilimathalawa%2C+Sri+Lanka"
               target="_blank"
               rel="noopener noreferrer"
               class="jc-footer-contact-item">

                <span class="jc-footer-icon" aria-hidden="true">
                    <svg viewBox="0 0 24 24" fill="none">
                        <path d="M20 10C20 15 12 22 12 22
                                 S4 15 4 10
                                 A8 8 0 1 1 20 10Z"
                              stroke="currentColor"
                              stroke-width="1.7"/>
                        <circle cx="12" cy="10" r="2.5"
                                stroke="currentColor"
                                stroke-width="1.7"/>
                    </svg>
                </span>

                <span>
                    <small>OFFICE LOCATION</small>
                    <strong>Pilimathalawa, Sri Lanka</strong>
                </span>
            </a>

            <div class="jc-footer-hours">
                <span class="jc-footer-hours-dot"></span>
                <span>
                    <small>PROJECT ENQUIRIES</small>
                    <strong>Contact us for availability</strong>
                </span>
            </div>

        </div>

    </div>

    <!-- Footer Bottom -->
    <div class="jc-footer-bottom">

        <div class="jc-footer-bottom-inner">

            <p>
                &copy; <?php echo date('Y'); ?>
                <strong>Jayarathne Construction.</strong>
                All rights reserved.
            </p>

            <p class="jc-footer-tagline">
                Built on Quality. Backed by Trust.
            </p>

        </div>

    </div>

</footer>

