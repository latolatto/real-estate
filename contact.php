<?php

declare(strict_types=1);

require __DIR__ . '/includes/bootstrap.php';
require __DIR__ . '/includes/layout.php';

render_header('Contact', 'contact');
?>
<section class="py-5">
    <div class="container">
        <div class="row g-4">
            <div class="col-lg-6">
                <div class="card content-card p-4 p-lg-5 h-100">
                    <p class="section-eyebrow mb-2">Contact</p>
                    <h1 class="mb-3">Let’s plan your next move</h1>
                    <p class="text-secondary mb-4">Use the details below on the live site, or replace them anytime from the admin dashboard’s site content section.</p>
                    <div class="mb-3"><strong>Phone:</strong> <?= e(setting('office_phone', '(305) 555-0189')) ?></div>
                    <div class="mb-3"><strong>Email:</strong> <?= e(setting('office_email', 'hello@harborhomes.com')) ?></div>
                    <div class="mb-3"><strong>Address:</strong> <?= e(setting('office_address', '18 Harbor Avenue, Miami, FL')) ?></div>
                    <div><strong>Hours:</strong> <?= e(setting('office_hours', 'Mon - Sat: 9:00 AM - 6:00 PM')) ?></div>
                </div>
            </div>
            <div class="col-lg-6">
                <div class="card content-card p-4 p-lg-5 h-100">
                    <p class="section-eyebrow mb-2">Service Areas</p>
                    <h2 class="mb-3"><?= e(setting('service_regions', 'Miami, Fort Lauderdale, Palm Beach')) ?></h2>
                    <p class="text-secondary mb-0">This starter version includes agency information editing directly in the dashboard, so the team can keep office details, hero messaging, and homepage content current without touching code.</p>
                </div>
            </div>
        </div>
    </div>
</section>
<?php render_footer(); ?>
