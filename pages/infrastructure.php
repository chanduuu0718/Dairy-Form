<?php
/**
 * Farm Infrastructure Page
 */
require_once __DIR__ . '/../config/config.php';

$pageTitle = 'Farm Infrastructure';
$pageDescription = 'Explore PM Dairy Farm infrastructure - modern cattle sheds, milking parlour, chilling plant, biogas plant and more.';

require_once __DIR__ . '/../includes/header.php';

$facilities = [
    [
        'icon' => 'fas fa-home',
        'title' => 'Modern Cattle Shed',
        'desc' => 'Our cattle sheds are designed with proper ventilation, rubber mat flooring, and automatic water troughs. Each animal has ample space to move, rest, and feed comfortably. The sheds are cleaned twice daily using pressurized water systems.',
        'img' => 'https://images.unsplash.com/photo-1516467508483-a7212febe31a?w=500&q=80'
    ],
    [
        'icon' => 'fas fa-cogs',
        'title' => 'Milking Parlour',
        'desc' => 'Our semi-automatic milking parlour ensures hygienic milking with minimal human touch. The stainless steel equipment is sterilized before every session. We follow strict protocols to maintain the purity of milk from udder to storage.',
        'img' => 'https://images.unsplash.com/photo-1594761051656-50916cef7c76?w=500&q=80'
    ],
    [
        'icon' => 'fas fa-snowflake',
        'title' => 'Chilling Plant',
        'desc' => 'Our bulk milk chilling plant brings milk temperature down to 4°C within minutes of milking. This rapid cooling preserves the nutritional value and prevents bacterial growth, ensuring you get the freshest milk possible.',
        'img' => 'https://images.unsplash.com/photo-1558618666-fcd25c85f82e?w=500&q=80'
    ],
    [
        'icon' => 'fas fa-seedling',
        'title' => 'Fodder Farm',
        'desc' => 'We grow our own green fodder on 15 acres of land. Our cattle are fed a balanced diet of fresh green fodder, dry fodder, and nutritious cattle feed. We never use any growth hormones or artificial supplements.',
        'img' => 'https://images.unsplash.com/photo-1500382017468-9049fed747ef?w=500&q=80'
    ],
    [
        'icon' => 'fas fa-fire',
        'title' => 'Biogas Plant',
        'desc' => 'Cattle waste is converted into clean biogas energy through our biogas plant. This provides cooking fuel and electricity for the farm, making us an eco-friendly and sustainable operation. The leftover slurry is used as organic fertilizer.',
        'img' => 'https://images.unsplash.com/photo-1473341304170-971dccb5ac1e?w=500&q=80'
    ],
    [
        'icon' => 'fas fa-stethoscope',
        'title' => 'Veterinary Room',
        'desc' => 'Our on-site veterinary facility ensures prompt medical attention for all cattle. Regular health check-ups, vaccinations, and preventive care are provided by qualified veterinary doctors. Every animal has a digital health record.',
        'img' => 'https://images.unsplash.com/photo-1548199973-03cce0bbc87b?w=500&q=80'
    ],
    [
        'icon' => 'fas fa-tint',
        'title' => 'Water System',
        'desc' => 'Clean drinking water is available round the clock through automatic water troughs in every shed. We have a dedicated RO water system for the dairy processing area and bore wells with regular water quality testing.',
        'img' => 'https://images.unsplash.com/photo-1504297050568-910d24c426d3?w=500&q=80'
    ]
];
?>

<!-- Page Hero -->
<section class="page-hero page-hero-sm">
    <div class="page-hero-overlay"></div>
    <div class="container">
        <div class="page-hero-content">
            <nav class="breadcrumb">
                <a href="<?= SITE_URL ?>/">Home</a><span>/</span><span class="current">Farm Infrastructure</span>
            </nav>
            <h1>Farm Infrastructure</h1>
            <p>Modern facilities built with care for our cattle and quality for our products</p>
        </div>
    </div>
</section>

<!-- Facilities -->
<section class="section">
    <div class="container">
        <div class="section-header animate-on-scroll fade-up">
            <span class="section-tag">Our Facilities</span>
            <h2 class="section-title">World-Class Infrastructure</h2>
            <p class="section-subtitle">We've invested in modern infrastructure to ensure the highest quality dairy products while keeping our cattle happy and healthy.</p>
        </div>

        <div class="infrastructure-list">
            <?php foreach ($facilities as $i => $facility): ?>
                <div class="infrastructure-card animate-on-scroll <?= $i % 2 === 0 ? 'slide-left' : 'slide-right' ?>">
                    <div class="infra-image">
                        <img src="<?= $facility['img'] ?>" alt="<?= $facility['title'] ?>" loading="lazy">
                    </div>
                    <div class="infra-content">
                        <div class="infra-icon"><i class="<?= $facility['icon'] ?>"></i></div>
                        <h3><?= $facility['title'] ?></h3>
                        <p><?= $facility['desc'] ?></p>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
</section>

<!-- Virtual Tour -->
<section class="section section-cream">
    <div class="container">
        <div class="section-header animate-on-scroll fade-up">
            <span class="section-tag">Virtual Tour</span>
            <h2 class="section-title">Take a Virtual Farm Tour</h2>
            <p class="section-subtitle">Can't visit us in person? Take a virtual tour of our farm from the comfort of your home.</p>
        </div>
        <div class="video-wrapper animate-on-scroll fade-up">
            <div class="video-container">
                <div class="video-placeholder" id="video-placeholder">
                    <i class="fas fa-play-circle"></i>
                    <p>Click to load farm tour video</p>
                </div>
            </div>
        </div>
        <div class="text-center mt-4">
            <a href="<?= SITE_URL ?>/pages/farm-visit.php" class="btn btn-primary btn-lg">
                <i class="fas fa-calendar-check"></i> Book a Real Visit
            </a>
        </div>
    </div>
</section>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
