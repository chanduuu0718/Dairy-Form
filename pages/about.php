<?php
/**
 * About Us Page - PM Dairy Farm
 */
$pageTitle = 'About Us';
$pageDescription = 'Learn about PM Dairy Farm - our story, mission, and commitment to delivering pure, farm-fresh dairy products from Haryana since 2009.';

require_once __DIR__ . '/../config/config.php';

$db = Database::getInstance();

// Fetch testimonials for social proof
$testimonials = $db->fetchAll(
    "SELECT * FROM testimonials WHERE is_approved = 1 ORDER BY created_at DESC LIMIT 3"
);

// Stats from settings
$stats = [
    'cattle'     => getSetting('total_cattle') ?: '150+',
    'production' => getSetting('daily_production') ?: '2000+',
    'experience' => getSetting('years_experience') ?: '15+',
    'customers'  => getSetting('happy_customers') ?: '5000+'
];

require_once INCLUDES_PATH . 'header.php';
?>

<!-- ==================== HERO / BREADCRUMB ==================== -->
<section class="page-hero">
    <div class="page-hero-overlay"></div>
    <div class="container">
        <div class="page-hero-content animate-on-scroll fade-up">
            <h1 class="page-hero-title">About Us</h1>
            <p class="page-hero-subtitle">Our journey from a small family farm to Haryana's trusted dairy brand</p>
        </div>
    </div>
</section>

<!-- ==================== OUR STORY ==================== -->
<section class="section" id="our-story">
    <div class="container">
        <div class="story-grid">
            <div class="story-image animate-on-scroll slide-left">
                <div class="story-image-wrapper">
                    <img src="<?= ASSETS_URL ?>/farm/story_image.jpg" alt="Purvanchal Dairyfarm Dairy Farm" loading="lazy"
                         onerror="this.onerror=null; this.src='<?= ASSETS_URL ?>/error_image.png';">
                    <div class="story-experience-badge">
                        <span class="exp-number"><?= $stats['experience'] ?></span>
                        <span class="exp-text">Years of Excellence</span>
                    </div>
                </div>
            </div>
            <div class="story-content animate-on-scroll slide-right">
                <h2 class="section-title">A Legacy of Purity Since 2009</h2>
                <p>Purvanchal Dairyfarm Dairy began as a humble family endeavour in the fertile lands of Village Begpur, Tehsil Nizamabad, District Azamgarh, Uattar Pradesh. What started with a handful of indigenous cows and an unwavering belief in purity has blossomed into one of the region's most trusted dairy brands.</p>
                <p>Our founder, <strong>Shri Himanshu Maurya</strong>, envisioned a dairy farm that would honour the ancient Indian tradition of treating cattle as family while embracing modern hygiene and quality standards. Every drop of milk that leaves our farm carries his dedication to purity and the blessings of our beloved cattle.</p>
                <p>Today, Purvanchal Dairyfarm Dairy serves thousands of families across the region, delivering farm-fresh milk, ghee, paneer, dahi, and more - products made with the same love and care that defined our very first day.</p>
                <div class="story-features">
                    <div class="story-feature">
                        <i class="fas fa-check-circle"></i>
                        <span>100% Pure & Natural</span>
                    </div>
                    <div class="story-feature">
                        <i class="fas fa-check-circle"></i>
                        <span>No Preservatives or Hormones</span>
                    </div>
                    <div class="story-feature">
                        <i class="fas fa-check-circle"></i>
                        <span>Farm to Door Delivery</span>
                    </div>
                    <div class="story-feature">
                        <i class="fas fa-check-circle"></i>
                        <span>FSSAI Certified</span>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- ==================== TIMELINE / MILESTONES ==================== -->
<section class="section section-cream" id="milestones">
    <div class="container">
        <div class="section-header animate-on-scroll fade-up">
            <h2 class="section-title">Milestones That Define Us</h2>
            <p class="section-subtitle">From a small dream to a thriving dairy farm - here is how we grew over the years</p>
        </div>

        <div class="timeline">
            <!-- 2009 -->
            <div class="timeline-item animate-on-scroll fade-up">
                <div class="timeline-marker">
                    <i class="fas fa-seedling"></i>
                </div>
                <div class="timeline-content">
                    <span class="timeline-year">2009</span>
                    <h3 class="timeline-title">The Beginning</h3>
                    <p>Purvanchal Dairyfarm Dairy Farm was founded by Shri Himanshu Maurya with a vision to provide pure, unadulterated dairy products. Starting with just a few indigenous cows in a modest shed, the foundation of trust and quality was laid.</p>
                </div>
            </div>

            <!-- 2012 -->
            <div class="timeline-item animate-on-scroll fade-up">
                <div class="timeline-marker">
                    <i class="fas fa-cow"></i>
                </div>
                <div class="timeline-content">
                    <span class="timeline-year">2012</span>
                    <h3 class="timeline-title">First 50 Cattle</h3>
                    <p>Growing demand and community trust helped us expand our herd to 50 cattle, including prized Sahiwal and Gir breeds known for their rich A2 milk. This milestone marked our transition from a small farm to a serious dairy operation.</p>
                </div>
            </div>

            <!-- 2015 -->
            <div class="timeline-item animate-on-scroll fade-up">
                <div class="timeline-marker">
                    <i class="fas fa-cogs"></i>
                </div>
                <div class="timeline-content">
                    <span class="timeline-year">2015</span>
                    <h3 class="timeline-title">Modern Milking Parlour</h3>
                    <p>We invested in a state-of-the-art milking parlour with automated milking machines and a bulk milk chilling plant, ensuring the highest standards of hygiene and freshness in every litre of milk.</p>
                </div>
            </div>

            <!-- 2018 -->
            <div class="timeline-item animate-on-scroll fade-up">
                <div class="timeline-marker">
                    <i class="fas fa-flask"></i>
                </div>
                <div class="timeline-content">
                    <span class="timeline-year">2018</span>
                    <h3 class="timeline-title">A2 Milk Programme</h3>
                    <p>Recognising the health benefits of A2 protein milk, we launched our dedicated A2 Milk Programme with selectively bred indigenous cattle, making premium A2 milk accessible to health-conscious families.</p>
                </div>
            </div>

            <!-- 2020 -->
            <div class="timeline-item animate-on-scroll fade-up">
                <div class="timeline-marker">
                    <i class="fas fa-chart-line"></i>
                </div>
                <div class="timeline-content">
                    <span class="timeline-year">2020</span>
                    <h3 class="timeline-title">2000 Litres Daily Production</h3>
                    <p>A landmark achievement - our farm reached a daily production capacity of over 2000 litres, serving thousands of households with fresh milk and a complete range of dairy products every single day.</p>
                </div>
            </div>

            <!-- 2023 -->
            <div class="timeline-item animate-on-scroll fade-up">
                <div class="timeline-marker">
                    <i class="fas fa-globe"></i>
                </div>
                <div class="timeline-content">
                    <span class="timeline-year">2023</span>
                    <h3 class="timeline-title">Online Ordering Launched</h3>
                    <p>Embracing technology for customer convenience, we launched our online ordering and subscription platform, enabling families to order fresh dairy products from the comfort of their homes with doorstep delivery.</p>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- ==================== FOUNDER PROFILE ==================== -->
<section class="section" id="founder">
    <div class="container">
        <div class="section-header animate-on-scroll fade-up">
            <h2 class="section-title">The Man Behind Purvanchal Dairyfarm Dairy</h2>
        </div>

        <div class="founder-grid">
            <div class="founder-image animate-on-scroll slide-left">
                <div class="founder-image-wrapper">
                    <img src="<?= ASSETS_URL ?>/team/owner.png"
                         alt="Shri Prem Singh - Founder, PM Dairy Farm"
                         loading="lazy"
                         onerror="this.onerror=null; this.src='<?= ASSETS_URL ?>/error_image.png';">
                    <div class="founder-name-badge">
                        <span class="founder-name-text">Shri Himanshu Maurya</span>
                        <span class="founder-role-text">Founder & Owner</span>
                    </div>
                </div>
            </div>
            <div class="founder-content animate-on-scroll slide-right">
                <blockquote class="founder-quote">
                    <i class="fas fa-quote-left"></i>
                    <p>I grew up watching my father care for our two cows every morning before sunrise. That love, that discipline, that respect for the animal - it shaped who I am. When I started PM Dairy in 2009, I made a promise to myself and to every family that would trust us: <strong>we will never compromise on purity</strong>.</p>
                    <p>Every cow in our farm is like family to us. We feed them fresh organic fodder, give them clean water, and let them roam freely. Happy cattle give the purest milk - this is not just a belief, it is our everyday practice.</p>
                    <p>My dream was simple - to bring the same pure, fresh milk that my mother used to give us as children, to every household in our community. Today, seeing thousands of families trust us with their daily nutrition is the greatest blessing I could ask for.</p>
                    <cite>- Shri Himanshu Maurya, Founder</cite>
                </blockquote>
                <div class="founder-details">
                    <div class="founder-detail-item">
                        <i class="fas fa-map-marker-alt"></i>
                        <span>Village Begpur, Tehsil Nizamabad, District Azamgarh, Uattar Pradesh</span>
                    </div>
                    <div class="founder-detail-item">
                        <i class="fas fa-heart"></i>
                        <span>1+ Years of Dedication to Dairy Farming</span>
                    </div>
                    <div class="founder-detail-item">
                        <i class="fas fa-award"></i>
                        <span>Recognised by Uattar Pradesh Dairy Development Board</span>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- ==================== MISSION / VISION / VALUES ==================== -->
<section class="section section-cream" id="mission-vision">
    <div class="container">
        <div class="section-header animate-on-scroll fade-up">
            <h2 class="section-title">Mission, Vision & Values</h2>
            <p class="section-subtitle">The principles that guide everything we do at Purvanchal Dairyfarm Dairy</p>
        </div>

        <div class="mvv-grid">
            <!-- Mission -->
            <div class="mvv-card animate-on-scroll fade-up">
                <div class="mvv-icon">
                    <i class="fas fa-bullseye"></i>
                </div>
                <h3 class="mvv-title">Our Mission</h3>
                <p>To deliver 100% pure, farm-fresh dairy products to every household, maintaining the highest standards of hygiene, quality, and nutrition while supporting sustainable farming practices and caring for our cattle as family.</p>
                <ul class="mvv-points">
                    <li><i class="fas fa-check"></i> Zero adulteration guarantee</li>
                    <li><i class="fas fa-check"></i> Daily farm-to-door delivery</li>
                    <li><i class="fas fa-check"></i> Fair prices for quality products</li>
                </ul>
            </div>

            <!-- Vision -->
            <div class="mvv-card animate-on-scroll fade-up">
                <div class="mvv-icon">
                    <i class="fas fa-eye"></i>
                </div>
                <h3 class="mvv-title">Our Vision</h3>
                <p>To become Haryana's most trusted and loved dairy brand, known for uncompromising purity, ethical cattle care, and innovative dairy solutions - while inspiring a new generation to embrace clean, sustainable agriculture.</p>
                <ul class="mvv-points">
                    <li><i class="fas fa-check"></i> Regional dairy leadership</li>
                    <li><i class="fas fa-check"></i> Technology-driven farm operations</li>
                    <li><i class="fas fa-check"></i> Community empowerment</li>
                </ul>
            </div>

            <!-- Values -->
            <div class="mvv-card animate-on-scroll fade-up" style="animation-delay: 0.3s">
                <div class="mvv-icon">
                    <i class="fas fa-gem"></i>
                </div>
                <h3 class="mvv-title">Our Values</h3>
                <p>Every decision at PM Dairy is guided by our core values: purity in products, compassion for cattle, honesty in business, and respect for the families who trust us with their daily nutrition.</p>
                <ul class="mvv-points">
                    <li><i class="fas fa-check"></i> Purity & Transparency</li>
                    <li><i class="fas fa-check"></i> Compassion for Animals</li>
                    <li><i class="fas fa-check"></i> Customer First Always</li>
                </ul>
            </div>
        </div>
    </div>
</section>

<!-- ==================== CERTIFICATIONS ==================== -->
<section class="section" id="certifications">
    <div class="container">
        <div class="section-header animate-on-scroll fade-up">
            <h2 class="section-title">Our Certifications</h2>
            <p class="section-subtitle">Trusted certifications that guarantee the quality and safety of our products</p>
        </div>

        <div class="certifications-grid">
            <!-- FSSAI -->
            <div class="cert-card animate-on-scroll fade-up">
                <div class="cert-icon">
                    <img src="<?= ASSETS_URL ?>/images/certifications/fssai.png"
                         alt="FSSAI Certified"
                         loading="lazy"
                         onerror="this.style.display='none'; this.nextElementSibling.style.display='flex';">
                    <div class="cert-icon-fallback" style="display:none;">
                        <i class="fas fa-shield-alt"></i>
                    </div>
                </div>
                <h3 class="cert-name">FSSAI Certified</h3>
                <p class="cert-desc">Food Safety and Standards Authority of India certified. All our products meet the stringent food safety norms mandated by the Government of India.</p>
                <span class="cert-badge"><i class="fas fa-check-circle"></i> Verified</span>
            </div>

            <!-- ISO 22000 -->
            <div class="cert-card animate-on-scroll fade-up" style="animation-delay: 0.15s">
                <div class="cert-icon">
                    <img src="<?= ASSETS_URL ?>/images/certifications/iso22000.png"
                         alt="ISO 22000 Certified"
                         loading="lazy"
                         onerror="this.style.display='none'; this.nextElementSibling.style.display='flex';">
                    <div class="cert-icon-fallback" style="display:none;">
                        <i class="fas fa-certificate"></i>
                    </div>
                </div>
                <h3 class="cert-name">ISO 22000</h3>
                <p class="cert-desc">International standard for food safety management systems, ensuring every stage from farm to delivery follows globally recognised safety protocols.</p>
                <span class="cert-badge"><i class="fas fa-check-circle"></i> Verified</span>
            </div>

            <!-- Organic India -->
            <div class="cert-card animate-on-scroll fade-up" style="animation-delay: 0.3s">
                <div class="cert-icon">
                    <img src="<?= ASSETS_URL ?>/images/certifications/organic-india.png"
                         alt="Organic India Certified"
                         loading="lazy"
                         onerror="this.style.display='none'; this.nextElementSibling.style.display='flex';">
                    <div class="cert-icon-fallback" style="display:none;">
                        <i class="fas fa-leaf"></i>
                    </div>
                </div>
                <h3 class="cert-name">Organic India</h3>
                <p class="cert-desc">Certified organic dairy practices - our cattle are fed organically grown fodder, free from pesticides, GMOs, and artificial growth hormones.</p>
                <span class="cert-badge"><i class="fas fa-check-circle"></i> Verified</span>
            </div>
        </div>
    </div>
</section>

<!-- ==================== CTA ==================== -->
<section class="cta-section" id="about-cta">
    <div class="cta-overlay"></div>
    <div class="container">
        <div class="cta-content animate-on-scroll fade-up">
            <h2 class="cta-title">Want to Experience Our Farm?</h2>
            <p class="cta-text">Come visit PM Dairy Farm and see for yourself how we care for our cattle and produce the purest dairy products. Bring your family for a memorable farm experience.</p>
            <div class="cta-buttons">
                <a href="<?= SITE_URL ?>/pages/farm-visit.php" class="btn btn-gold btn-lg">
                    <i class="fas fa-calendar-check"></i> Book a Farm Visit
                </a>
                <a href="<?= SITE_URL ?>/pages/contact.php" class="btn btn-outline-white btn-lg">
                    <i class="fas fa-phone-alt"></i> Contact Us
                </a>
            </div>
        </div>
    </div>
</section>

<!-- ==================== PAGE-SPECIFIC STYLES ==================== -->
<style>
/* ---- Timeline ---- */
.timeline {
    position: relative;
    max-width: 900px;
    margin: 0 auto;
    padding: 2rem 0;
}
.timeline::before {
    content: '';
    position: absolute;
    left: 50%;
    transform: translateX(-50%);
    top: 0;
    bottom: 0;
    width: 3px;
    background: linear-gradient(to bottom, #52B788, #1A3C2A);
    border-radius: 3px;
}
.timeline-item {
    display: flex;
    align-items: flex-start;
    margin-bottom: 3rem;
    position: relative;
}
.timeline-item:nth-child(odd) {
    flex-direction: row-reverse;
    text-align: right;
}
.timeline-item:nth-child(odd) .timeline-content {
    padding-right: 3rem;
    padding-left: 0;
}
.timeline-item:nth-child(even) .timeline-content {
    padding-left: 3rem;
    padding-right: 0;
}
.timeline-marker {
    position: absolute;
    left: 50%;
    transform: translateX(-50%);
    width: 50px;
    height: 50px;
    background: #1A3C2A;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    color: #FDF8F0;
    font-size: 1.1rem;
    z-index: 2;
    border: 4px solid #FDF8F0;
    box-shadow: 0 0 0 3px #52B788;
}
.timeline-content {
    width: 50%;
    background: #fff;
    padding: 1.5rem;
    border-radius: 12px;
    box-shadow: 0 4px 15px rgba(0,0,0,0.06);
    transition: transform 0.3s ease, box-shadow 0.3s ease;
}
.timeline-content:hover {
    transform: translateY(-3px);
    box-shadow: 0 8px 25px rgba(0,0,0,0.1);
}
.timeline-year {
    display: inline-block;
    background: linear-gradient(135deg, #1A3C2A, #52B788);
    color: #FDF8F0;
    padding: 0.25rem 1rem;
    border-radius: 20px;
    font-size: 0.85rem;
    font-weight: 600;
    margin-bottom: 0.75rem;
    letter-spacing: 0.5px;
}
.timeline-title {
    font-family: 'Playfair Display', serif;
    font-size: 1.25rem;
    color: #1A3C2A;
    margin-bottom: 0.5rem;
}
.timeline-content p {
    color: #555;
    line-height: 1.7;
    font-size: 0.95rem;
}

/* ---- Founder ---- */
.founder-grid {
    display: grid;
    grid-template-columns: 1fr 1.3fr;
    gap: 4rem;
    align-items: center;
}
.founder-image-wrapper {
    position: relative;
    border-radius: 16px;
    overflow: hidden;
    box-shadow: 0 10px 40px rgba(26, 60, 42, 0.15);
}
.founder-image-wrapper img {
    width: 100%;
    height: auto;
    display: block;
    aspect-ratio: 3 / 4;
    object-fit: cover;
}
.founder-name-badge {
    position: absolute;
    bottom: 0;
    left: 0;
    right: 0;
    background: linear-gradient(transparent, rgba(26, 60, 42, 0.9));
    padding: 2rem 1.5rem 1.5rem;
    text-align: center;
}
.founder-name-text {
    display: block;
    color: #FDF8F0;
    font-family: 'Playfair Display', serif;
    font-size: 1.5rem;
    font-weight: 700;
}
.founder-role-text {
    display: block;
    color: #D4A843;
    font-size: 0.9rem;
    font-weight: 500;
    letter-spacing: 1px;
    text-transform: uppercase;
    margin-top: 0.25rem;
}
.founder-quote {
    border: none;
    margin: 0;
    padding: 0;
}
.founder-quote .fa-quote-left {
    font-size: 2rem;
    color: #52B788;
    opacity: 0.3;
    margin-bottom: 0.5rem;
}
.founder-quote p {
    color: #444;
    line-height: 1.8;
    font-size: 1rem;
    margin-bottom: 1rem;
}
.founder-quote cite {
    display: block;
    margin-top: 1rem;
    color: #1A3C2A;
    font-weight: 600;
    font-style: normal;
    font-family: 'Playfair Display', serif;
}
.founder-details {
    margin-top: 2rem;
    border-top: 1px solid #eee;
    padding-top: 1.5rem;
}
.founder-detail-item {
    display: flex;
    align-items: center;
    gap: 0.75rem;
    margin-bottom: 0.75rem;
    color: #555;
    font-size: 0.95rem;
}
.founder-detail-item i {
    color: #52B788;
    width: 20px;
    text-align: center;
}

/* ---- Mission / Vision / Values ---- */
.mvv-grid {
    display: grid;
    grid-template-columns: repeat(3, 1fr);
    gap: 2rem;
}
.mvv-card {
    background: #fff;
    border-radius: 16px;
    padding: 2.5rem 2rem;
    text-align: center;
    box-shadow: 0 4px 20px rgba(0,0,0,0.06);
    transition: transform 0.3s ease, box-shadow 0.3s ease;
    border: 1px solid #eee;
}
.mvv-card:hover {
    transform: translateY(-5px);
    box-shadow: 0 12px 35px rgba(0,0,0,0.1);
}
.mvv-card-highlight {
    background: linear-gradient(135deg, #1A3C2A, #2d5a3f);
    color: #FDF8F0;
    border-color: transparent;
}
.mvv-card-highlight .mvv-title {
    color: #D4A843;
}
.mvv-card-highlight p {
    color: rgba(253, 248, 240, 0.85);
}
.mvv-card-highlight .mvv-points li {
    color: rgba(253, 248, 240, 0.9);
}
.mvv-card-highlight .mvv-points i {
    color: #D4A843;
}
.mvv-icon {
    width: 70px;
    height: 70px;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 1.5rem;
    margin: 0 auto 1.25rem;
    background: rgba(82, 183, 136, 0.1);
    color: #52B788;
}
.mvv-card-highlight .mvv-icon {
    background: rgba(212, 168, 67, 0.2);
    color: #D4A843;
}
.mvv-title {
    font-family: 'Playfair Display', serif;
    font-size: 1.35rem;
    color: #1A3C2A;
    margin-bottom: 1rem;
}
.mvv-card p {
    color: #555;
    line-height: 1.7;
    font-size: 0.95rem;
    margin-bottom: 1.25rem;
}
.mvv-points {
    list-style: none;
    padding: 0;
    margin: 0;
    text-align: left;
}
.mvv-points li {
    display: flex;
    align-items: center;
    gap: 0.6rem;
    padding: 0.4rem 0;
    font-size: 0.9rem;
    color: #444;
}
.mvv-points i {
    color: #52B788;
    font-size: 0.85rem;
}

/* ---- Certifications ---- */
.certifications-grid {
    display: grid;
    grid-template-columns: repeat(3, 1fr);
    gap: 2rem;
}
.cert-card {
    background: #fff;
    border-radius: 16px;
    padding: 2.5rem 2rem;
    text-align: center;
    box-shadow: 0 4px 20px rgba(0,0,0,0.06);
    border: 1px solid #eee;
    transition: transform 0.3s ease, box-shadow 0.3s ease;
}
.cert-card:hover {
    transform: translateY(-5px);
    box-shadow: 0 12px 35px rgba(0,0,0,0.1);
}
.cert-icon {
    margin-bottom: 1.25rem;
}
.cert-icon img {
    width: 80px;
    height: 80px;
    object-fit: contain;
}
.cert-icon-fallback {
    width: 80px;
    height: 80px;
    border-radius: 50%;
    background: linear-gradient(135deg, #1A3C2A, #52B788);
    color: #FDF8F0;
    font-size: 2rem;
    display: flex;
    align-items: center;
    justify-content: center;
    margin: 0 auto;
}
.cert-name {
    font-family: 'Playfair Display', serif;
    font-size: 1.25rem;
    color: #1A3C2A;
    margin-bottom: 0.75rem;
}
.cert-desc {
    color: #666;
    line-height: 1.7;
    font-size: 0.9rem;
    margin-bottom: 1.25rem;
}
.cert-badge {
    display: inline-block;
    background: rgba(82, 183, 136, 0.1);
    color: #52B788;
    padding: 0.35rem 1rem;
    border-radius: 20px;
    font-size: 0.85rem;
    font-weight: 600;
}
.cert-badge i {
    margin-right: 0.3rem;
}

/* ---- CTA Buttons Row ---- */
.cta-buttons {
    display: flex;
    gap: 1rem;
    justify-content: center;
    flex-wrap: wrap;
}

/* ---- Responsive ---- */
@media (max-width: 991px) {
    .timeline::before {
        left: 30px;
    }
    .timeline-marker {
        left: 30px;
        transform: translateX(-50%);
    }
    .timeline-item,
    .timeline-item:nth-child(odd) {
        flex-direction: row;
        text-align: left;
    }
    .timeline-item:nth-child(odd) .timeline-content,
    .timeline-item:nth-child(even) .timeline-content {
        width: calc(100% - 70px);
        margin-left: 70px;
        padding-left: 0;
        padding-right: 0;
    }
    .founder-grid {
        grid-template-columns: 1fr;
        gap: 2rem;
    }
    .founder-image-wrapper img {
        aspect-ratio: 4 / 3;
    }
    .mvv-grid {
        grid-template-columns: 1fr;
    }
    .certifications-grid {
        grid-template-columns: 1fr;
    }
}
@media (max-width: 767px) {
    .timeline::before {
        left: 20px;
    }
    .timeline-marker {
        left: 20px;
        width: 40px;
        height: 40px;
        font-size: 0.9rem;
    }
    .timeline-item:nth-child(odd) .timeline-content,
    .timeline-item:nth-child(even) .timeline-content {
        margin-left: 55px;
        width: calc(100% - 55px);
        padding: 1.25rem;
    }
    .timeline-year {
        font-size: 0.8rem;
    }
    .timeline-title {
        font-size: 1.1rem;
    }
    .founder-grid {
        gap: 1.5rem;
    }
}
</style>

<?php require_once INCLUDES_PATH . 'footer.php'; ?>
