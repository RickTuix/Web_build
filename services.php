<?php
require_once 'db.php';

$pageTitle = "Our Services - Taan Tech";
include 'header.php';
?>

<section class="page-header">
    <div class="container">
        <h1>Our Services</h1>
        <p>Professional tech services by Taan Tech</p>
    </div>
</section>

<div class="container">
    <div class="services-placeholder">
        <div class="services-placeholder-icon">
            <svg xmlns="http://www.w3.org/2000/svg" width="72" height="72" viewBox="0 0 24 24" fill="none" stroke="#1a73e8" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round">
                <path d="M14.7 6.3a1 1 0 0 0 0 1.4l1.6 1.6a1 1 0 0 0 1.4 0l3.77-3.77a6 6 0 0 1-7.94 7.94l-6.91 6.91a2.12 2.12 0 0 1-3-3l6.91-6.91a6 6 0 0 1 7.94-7.94l-3.76 3.76z"></path>
            </svg>
        </div>
        <h2>We're still setting up</h2>
        <p>Check back again at a later time.</p>
        <a href="<?php echo $base_url; ?>/index.php" class="btn btn-primary">Back to Home</a>
    </div>
</div>

<?php include 'footer.php'; ?>